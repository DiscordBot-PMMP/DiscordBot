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
use Volatile;

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
	 * @var Volatile
	 */
	private $inboundData;

	/**
	 * @var Volatile
	 */
	private $outboundData;

	/**
	 * @var bool
	 */
	private $stopping = false;

	public function __construct(AttachableThreadedLogger $logger, array $initialConfig, Volatile $inboundData, Volatile $outboundData) {
		$this->logger = $logger;
		$this->initialConfig = $initialConfig;
		$this->inboundData = $inboundData;
		$this->outboundData = $outboundData;
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

	/**
	 * https://github.com/pmmp/pthreads/blob/fork/examples/fetching-data-from-a-thread.php
	 * @return array<int, array>
	 */
	public function readInboundData(){
		return $this->inboundData->shift();
	}

	/**
	 * @param array<int, array> $data
	 */
	public function writeOutboundData(array $data): void{
		$this->outboundData[] = (array)$data;
	}

	public function isStopping(): bool{
		return $this->stopping === true;
	}

	public function stop(): void{
		$this->stopping = true;
	}
}