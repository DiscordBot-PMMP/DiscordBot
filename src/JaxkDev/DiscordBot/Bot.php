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
use Discord\Parts\Guild\Emoji;
use Discord\Parts\User\Activity;

class Bot {
	/* @var Discord */
	private $client;

	public function __construct() {
		try {
			$this->client = new Discord([
				'token' => 'KEY HERE REMINDER TO SELF, MY KEY IS IN SERVER_KEY.TXT',
			]);
		} catch (IntentException $e) {
			var_dump($e);
		}
		$this->registerHandlers();
		$this->client->run();
	}

	private function registerHandlers(){
		$this->client->on('ready', function ($discord) {
			echo "Bot is ready!", PHP_EOL;
			$discord->updatePresence($discord->factory(Activity::class, [
				'name' => 'on a PMMP Server',
				'type' => Activity::TYPE_PLAYING
			]));

			// Listen for messages.
			$discord->on('message', function ($message, $discord) {
				echo "{$message->author->username}: {$message->content}",PHP_EOL;
			});
		});
	}
}