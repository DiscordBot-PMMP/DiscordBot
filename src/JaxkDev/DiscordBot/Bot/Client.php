<?php
/*
 * DiscordBot, PocketMine-MP Plugin.
 *
 * Licensed under the Open Software License version 3.0 (OSL-3.0)
 * Copyright (C) 2020 JaxkDev
 *
 * Twitter :: @JaxkDev
 * Discord :: JaxkDev#2698
 * Email   :: JaxkDev@gmail.com
 */

namespace JaxkDev\DiscordBot\Bot;

use Discord\Discord;
use Discord\Parts\Channel\Channel;
use Discord\Parts\Channel\Message;
use Discord\Parts\Guild\Guild;
use Discord\Parts\User\Activity;
use Discord\Parts\User\Member;
use Error;
use ErrorException;
use Exception;
use JaxkDev\DiscordBot\Bot\Handlers\PluginCommunicationHandler;
use JaxkDev\DiscordBot\Communication\BotThread;
use JaxkDev\DiscordBot\Communication\Protocol;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\RotatingFileHandler;
use pocketmine\utils\MainLogger;
use React\EventLoop\TimerInterface;

class Client {
	/**
	 * @var BotThread
	 */
	private $thread;

	/**
	 * @var Discord
	 */
	private $client;

	/**
	 * @var PluginCommunicationHandler
	 */
	private $pluginCommsHandler;

	/**
	 * @var TimerInterface|null
	 */
	private $readyTimer, $tickTimer;

	/**
	 * @var int
	 */
	private $tickCount = 1;

	/**
	 * @var array
	 */
	private $config;

	public function __construct(BotThread $thread, array $config) {
		$this->thread = $thread;
		$this->config = $config;

		gc_enable();

		error_reporting(E_ALL & ~E_NOTICE);
		set_error_handler(array($this, 'errorHandler'));
		register_shutdown_function(array($this, 'close'));

		// Mono logger can have issues with other timezones, for now use UTC.
		// Note, this does not effect outside thread config.
		// TODO CDT Investigate.
		ini_set("date.timezone", "UTC");
		MainLogger::getLogger()->debug("Log files will be in UTC timezone.");

		$logger = new Logger('DiscordPHP');
		$httpLogger = new Logger('DiscordPHP.HTTP');
		$handler = new RotatingFileHandler($config['logging']['directory'].DIRECTORY_SEPARATOR."DiscordBot.log", $config['logging']['maxFiles'], Logger::DEBUG);
		$handler->setFilenameFormat('{filename}-{date}', 'Y-m-d');
		$logger->setHandlers(array($handler));
		$httpLogger->setHandlers(array($handler));

		if($config['logging']['debug']) {
			$handler = new StreamHandler(($r = fopen('php://stdout', 'w')) === false ? "" : $r);
			$logger->pushHandler($handler);
			$httpLogger->pushHandler($handler);
		}

		// No intents specified yet so IntentException is impossible.
		/** @noinspection PhpUnhandledExceptionInspection */
		$this->client = new Discord([
			'token' => $config['discord']['token'],
			'logger' => $logger,
			'httpLogger' => $httpLogger
		]);
		$this->config['discord']['token'] = "REDACTED";

		$this->registerHandlers();
		$this->registerTimers();

		$this->pluginCommsHandler = new PluginCommunicationHandler($this);

		$this->thread->setStatus(Protocol::THREAD_STATUS_STARTED);

		$this->client->run();
	}

	private function registerTimers(): void{
		// Handles shutdown, rather than a SHUTDOWN const to send through internal communication, set flag to closed.
		// Saves time & will guarantee closure ASAP rather then waiting in line through ^
		$this->client->getLoop()->addPeriodicTimer(1, function(){
			if($this->thread->getStatus() === Protocol::THREAD_STATUS_CLOSING){
				$this->close();
			}
		});

		// Handles any problems pre-ready.
		$this->readyTimer = $this->client->getLoop()->addTimer(30, function(){
			if($this->client->id !== null){
				MainLogger::getLogger()->warning("Client has taken >30s to get ready, is your discord server large ?");
				$this->client->getLoop()->addTimer(30, function(){
					if($this->thread->getStatus() !== Protocol::THREAD_STATUS_READY) {
						MainLogger::getLogger()->critical("Client has taken too long to become ready, shutting down.");
						$this->close();
					}
				});
			} else {
				MainLogger::getLogger()->critical("Client failed to login/connect within 30 seconds, See log file for details.");
				$this->close();
			}
		});

		$this->tickTimer = $this->client->getLoop()->addPeriodicTimer(0.05, function(){
			$this->tick();
		});
	}

	/** @noinspection PhpUnusedParameterInspection */
	private function registerHandlers(): void{
		// https://github.com/teamreflex/DiscordPHP/issues/433
		// Note ready is emitted after successful connection + all servers/users loaded.
		$this->client->on('ready', function (Discord $discord) {
			if($this->readyTimer !== null) {
				$this->client->getLoop()->cancelTimer($this->readyTimer);
				$this->readyTimer = null;
			}
			$this->thread->setStatus(Protocol::THREAD_STATUS_READY);
			MainLogger::getLogger()->info("Client ready.");

			$this->logDebugInfo();

			// Listen for messages.
			$discord->on('message', function (Message $message, Discord $discord) {
				// TODO Move to handler.
				if($message->author instanceof Member ? $message->author->user->bot : $message->author->bot){
					//Ignore Bot's (including self)
					return;
				}

				try{
					//So apparently message content can just disappear...
					//TODO Investigate.
					$prefix = $message->content[0];
				} catch(Error $e){
					return;
				}

				if($prefix === "!"){
					$args = explode(" ", $message->content);
					$cmd = substr(array_shift($args), 1);
					switch($cmd){
						case 'version':
						case 'ver':
							$message->channel->sendMessage("Version information:```\n".
								"> PHP - v".PHP_VERSION."\n".
								"> PocketMine - v".\pocketmine\VERSION."\n".
								"> DiscordBot - ".\JaxkDev\DiscordBot\VERSION."\n".
								"> DiscordPHPSlim - ".Discord::VERSION."```"
							)->otherwise(function($e) use($message) {
								MainLogger::getLogger()->logException($e);
								// At least try a static message, if this fails client probably only has read-only perms
								// In that channel.
								$message->channel->sendMessage("**ERROR** Failed to send version information...");
							});
							break;
					}
				}
			});
		});
	}

	public function tick(): void{
		$data = $this->thread->readInboundData(Protocol::PPT);

		foreach($data as $d) $this->pluginCommsHandler->handle($d);

		if(($this->tickCount % 20) === 0){
			//Run every second.
			$this->pluginCommsHandler->checkHeartbeat();
			$this->pluginCommsHandler->sendHeartbeat();
		}

		$this->tickCount++;
	}

	public function getThread(): BotThread{
		return $this->thread;
	}

	public function sendMessage(string $guild, string $channel, string $content): void{
		if($this->thread->getStatus() !== Protocol::THREAD_STATUS_READY) return;

		/** @noinspection PhpUnhandledExceptionInspection */
		$this->client->guilds->fetch($guild)->done(function(Guild $guild) use($channel, $content) {
			$guild->channels->fetch($channel)->done(function(Channel $channel) use($guild, $content) {
				$channel->sendMessage($content);
				MainLogger::getLogger()->debug("Sent message(".strlen($content).") to ({$guild->id}|{$channel->id})");
			}, function() use($guild, $channel) {
				MainLogger::getLogger()->warning("Failed to fetch channel {$channel} in guild {$guild->id} while attempting to send message.");
			});
		}, function() use($guild) {
			MainLogger::getLogger()->warning("Failed to fetch guild ${guild} while attempting to send message.");
		});
	}

	public function updatePresence(string $text, int $type): bool{
		$presence = new Activity($this->client, [
			'name' => $text,
			'type' => $type
		]);

		try {
			$this->client->updatePresence($presence);
			return true;
		} catch (Exception $e) {
			return false;
		}
	}

	public function logDebugInfo(): void{
		MainLogger::getLogger()->debug("Debug Information:\n".
			"> Username: {$this->client->username}#{$this->client->discriminator}\n".
			"> ID: {$this->client->id}\n".
			"> Servers: {$this->client->guilds->count()}\n".
			"> Users: {$this->client->users->count()}"
		);
	}

	public function errorHandler(int $severity, string $message, string $file, int $line): bool{
		if(substr($message,0,51) === "stream_socket_client(): unable to connect to udp://" and $line === 130){
			// Really nasty hack to check if connection fails,
			// Really need to fork/fix this in DiscordPHP...
			MainLogger::getLogger()->critical("Failed to connect to discord, please check your internet connection.");
		}
		MainLogger::getLogger()->logException(new ErrorException($message, 0, $severity, $file, $line));
		$this->close();
		return true;
	}

	public function close(): void{
		if($this->thread->getStatus() === Protocol::THREAD_STATUS_CLOSED) return;
		if($this->client instanceof Discord){
			try{
				$this->client->close(true);
			} catch (Error $e){
				MainLogger::getLogger()->debug("Failed to close client, probably due it not being started.");
			}
		}
		$this->thread->setStatus(Protocol::THREAD_STATUS_CLOSED);
		MainLogger::getLogger()->debug("Client closed.");
		exit(0);
	}
}