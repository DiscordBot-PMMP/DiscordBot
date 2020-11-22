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

	/**
	 * @var AttachableThreadedLogger
	 */
	private $logger;

	/**
	 * @var array
	 */
	private $initialConfig;

	/**
	 * @var bool
	 */
	private $stopping = false;

	public function __construct(AttachableThreadedLogger $logger, array $initialConfig) {
		$this->logger = $logger;
		$this->initialConfig = $initialConfig;
	}

	public function run() {
		error_reporting(-1);

		$this->registerClassLoader();

		if($this->logger instanceof MainLogger){
			$this->logger->registerStatic();
		}

		/** @noinspection PhpIncludeInspection */
		require_once(\JaxkDev\DiscordBot\COMPOSER);

		new Bot($this, (array)$this->initialConfig);
	}

	public function isStopping(): bool{
		return $this->stopping === true;
	}

	public function stop(): void{
		$this->stopping = true;
	}
}