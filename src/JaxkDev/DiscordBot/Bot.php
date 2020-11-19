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

	public function __construct(BotThread $thread) {
		$this->thread = $thread;

		try {
			$this->client = new Discord([
				'token' => 'KEY HERE REMINDER TO SELF, MY KEY IS IN SERVER_KEY.TXT',
			]);
		} catch (IntentException $e) {
			var_dump($e);
		}

		$this->registerHandlers();
		$this->registerTimers();

		$this->client->run();
	}

	private function registerTimers(): void{
		// Handles shutdown.
		$this->client->getLoop()->addPeriodicTimer(1, function(){
			if($this->thread->isStopping()){
				$this->client->close(true);
				MainLogger::getLogger()->info("Client closed.");
			}
		});
	}

	private function registerHandlers(): void{
		$this->client->on('ready', function ($discord) {
			MainLogger::getLogger()->info("Client ready.");
			$discord->updatePresence($discord->factory(Activity::class, [
				'name' => 'on a PMMP Server',
				'type' => Activity::TYPE_PLAYING
			]));

			// Listen for messages.
			$discord->on('message', function ($message, $discord) {
				MainLogger::getLogger()->info("{$message->author->username}: {$message->content}");
			});
		});
	}
}