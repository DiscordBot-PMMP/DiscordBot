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
use Discord\Parts\User\Activity;
use Monolog\Logger;
use Monolog\Handler\RotatingFileHandler;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use pocketmine\utils\MainLogger;

class Bot {
	/**
	 * @var BotThread
	 */
	private $thread;

	/**
	 * @var Discord
	 */
	private $client;

	/** @var bool */
	private $ready = false;

	/** @var array */
	private $config;

	public function __construct(BotThread $thread, array $config) {
		$this->thread = $thread;
		$this->config = $config;

		register_shutdown_function(array($this, 'shutdownHandler'));

		$logger = new Logger('logger');
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
		$this->client->getLoop()->addTimer(10, function(){
			if(!$this->ready){
				MainLogger::getLogger()->critical("Client failed to login/connect within 10 seconds, See log for details.");
				$this->shutdown();
			}
		});

		// TODO 'Ticking' Communication between thread + plugin via lists/Queues of data
	}

	private function registerHandlers(): void{
		$this->client->on('ready', function ($discord) {
			$this->ready = true;
			MainLogger::getLogger()->info("Client (".$this->client->username."#".$this->client->discriminator.")(".
				$this->client->id.") ready, Currently in ".$this->client->guilds->count()." servers/guilds.");
			$discord->updatePresence($discord->factory(Activity::class, [
				'name' => $this->config['discord']['presence']['text'],
				'type' => $this->config['discord']['presence']['type']
			]));

			// Listen for messages.
			$discord->on('message', function ($message, $discord) {
				MainLogger::getLogger()->info("{$message->author->username}: {$message->content}");
			});
		});
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
		}
		$this->thread->stop();  //Flag as stopping/stopped if not already.
		MainLogger::getLogger()->debug("BotThread shutdown.");
	}
}