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

use AttachableThreadedLogger;
use pocketmine\Thread;
use pocketmine\utils\MainLogger;

class BotThread extends Thread {

	/* @var AttachableThreadedLogger */
	private $logger;

	public function __construct(AttachableThreadedLogger $logger) {
		$this->logger = $logger;

		$this->start(PTHREADS_INHERIT_NONE);
	}

	public function run() {
		error_reporting(-1);

		$this->registerClassLoader();

		if($this->logger instanceof MainLogger){
			$this->logger->registerStatic();
		}

		if(!defined('JaxkDev\DiscordBot\COMPOSER')) {
			if(\Phar::running(true) !== "") {
				define('JaxkDev\DiscordBot\COMPOSER', \Phar::running(true) . "/vendor/autoload.php");
			} else {
				define('JaxkDev\DiscordBot\COMPOSER', dirname(__DIR__, 4) . "/DiscordBot/vendor/autoload.php");
			}
		}
		require_once(\JaxkDev\DiscordBot\COMPOSER);

		new Bot();
		// TODO Integrate DiscordPHP's logger with this one.
	}
}