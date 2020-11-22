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

namespace JaxkDev\DiscordBot;

use Discord\Discord;
use Discord\Exceptions\IntentException;
use Discord\Parts\Channel\Message;
use Discord\Parts\User\Activity;
use Exception;
use Monolog\Logger;
use Monolog\Handler\RotatingFileHandler;
use React\EventLoop\TimerInterface;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;

use pocketmine\utils\MainLogger;

class Bot {
	/**
	 * @var BotThread
	 */
	private $thread;

	/**
	 * @var Discord|null
	 */
	private $client;

	/**
	 * @var bool
	 */
	private $ready = false;

	/**
	 * @var TimerInterface|null
	 */
	private $readyTimer;

	/**
	 * @var array
	 */
	private $config;

	public function __construct(BotThread $thread, array $config) {
		$this->thread = $thread;
		$this->config = $config;

		register_shutdown_function(array($this, 'shutdownHandler'));

		$logger = new Logger('DiscordPHP');
		$handler = new RotatingFileHandler($config['logging']['directory'].DIRECTORY_SEPARATOR."DiscordBot.log", $config['logging']['maxFiles'], Logger::DEBUG);
		$handler->setFilenameFormat('{filename}-{date}', 'Y-m-d');
		$logger->setHandlers(array($handler));

		// TODO Add pipe handler for debugging.

		try {
			$this->client = new Discord([
				'token' => $config['discord']['token'],
				'logger' => $logger
			]);
			$this->config['discord']['token'] = "REDACTED";
		} catch (IntentException $e) {
			MainLogger::getLogger()->logException($e);
			return;
		} catch (InvalidOptionsException $e) {
			MainLogger::getLogger()->logException($e);
			return;
		}

		$this->registerHandlers();
		$this->registerTimers();

		$this->client->run();
	}

	private function registerTimers(): void{
		// Handles shutdown.
		$this->client->getLoop()->addPeriodicTimer(1, function(){
			if($this->thread->isStopping()){
				$this->shutdown();
			}
		});

		// Handles any problems pre-ready.
		$this->readyTimer = $this->client->getLoop()->addTimer(30, function(){
			if($this->client->id !== null){
				MainLogger::getLogger()->warning("Client has taken >30s to get ready, is your discord server large ?");
				$this->client->getLoop()->addTimer(30, function(){
					if(!$this->ready) {
						MainLogger::getLogger()->critical("Client has taken too long to become ready, shutting down.");
						$this->shutdown();
					}
				});
			} else {
				MainLogger::getLogger()->critical("Client failed to login/connect within 30 seconds, See log file for details.");
				$this->shutdown();
			}
		});

		// TODO 'Ticking' Communication between thread + plugin via lists/Queues of data
		// https://github.com/JaxkDev/PyRak/blob/master/src/pyrak/server/udp_server.py#L72
	}

	/** @noinspection PhpUnusedParameterInspection */
	private function registerHandlers(): void{
		// https://github.com/teamreflex/DiscordPHP/issues/433
		// Note ready is emitted after successful connection + all servers/users loaded.
		$this->client->on('ready', function (Discord $discord) {
			$this->client->getLoop()->cancelTimer($this->readyTimer);
			$this->readyTimer = null;
			$this->ready = true;

			$this->logDebugInfo();
			$this->updatePresence($this->config['discord']['presence']['text'], $this->config['discord']['presence']['type']);

			// Listen for messages.
			$discord->on('message', function (Message $message, Discord $discord) {
				if($message->author->user->bot) return; //Ignore Bot's (including self)

				if($message->content[0] === "!"){
					$args = explode(" ", $message->content);
					$cmd = substr(array_shift($args), 1);
					switch($cmd){
						case 'version':
						case 'ver':
							$message->channel->sendMessage("Version information:```\n".
								"> PHP - v".PHP_VERSION."\n".
								"> DiscordPHP - ".Discord::VERSION."\n".
								"> PocketMine - v".\pocketmine\VERSION."\n".
								"> DiscordBot - ".VERSION."```"
							)->otherwise(function($e) use($message) {
								MainLogger::getLogger()->logException($e);
								// At least try and static message.
								$message->channel->sendMessage("**ERROR** Failed to send version information...");
							});
							break;
						default:
							$message->channel->sendMessage("Unknown command.");
					}
				}
				MainLogger::getLogger()->info("[{$message->channel->name}] {$message->author->username}: {$message->content}");
			});
		});
	}

	public function updatePresence(string $text, int $type): bool{
		/** @var Activity $presence */
		$presence = $this->client->factory(Activity::class, [
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
		MainLogger::getLogger()->info("Client ({$this->client->username}#{$this->client->discriminator})({$this->client->id}) ready.");

		MainLogger::getLogger()->debug("Debug Information:\n".
			"> Servers: {$this->client->guilds->count()}\n".
			"> Users: {$this->client->users->count()}\n".
			"> Private Channels: {$this->client->private_channels->count()}"
		);
	}

	public function shutdown(): void{
		if($this->client !== null){
			$this->client->close(true);
			$this->client = null;
			MainLogger::getLogger()->debug("Client closed.");
		}
	}

	public function shutdownHandler(): void{
		if($this->client !== null) {
			$this->client->close();
			$this->client = null;
		}
		MainLogger::getLogger()->debug("BotThread shutdown.");
	}
}