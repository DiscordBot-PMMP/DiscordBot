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

namespace JaxkDev\DiscordBot\Communication;

use AttachableThreadedLogger;
use JaxkDev\DiscordBot\Bot\Client;
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
		$this->registerClassLoader();

		if($this->logger instanceof MainLogger){
			$this->logger->registerStatic();
		}

		/** @noinspection PhpIncludeInspection */
		require_once(\JaxkDev\DiscordBot\COMPOSER);

		new Client($this, (array)$this->initialConfig);
	}

	public function isStopping(): bool{
		return $this->stopping === true;
	}

	public function stop(): void{
		$this->stopping = true;
	}
}