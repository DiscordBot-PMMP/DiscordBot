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
use JaxkDev\DiscordBot\Utils;
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
	private $inboundData, $outboundData;

	/**
	 * @var int
	 */
	private $status = Protocol::THREAD_STATUS_STARTING;

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

	/*
	 * https://github.com/pmmp/pthreads/blob/fork/examples/fetching-data-from-a-thread.php
	 */
	public function readInboundData(int $count = 1): array{
		return $this->inboundData->chunk($count); /* @phpstan-ignore-line */
	}

	public function writeOutboundData(int $id, array $data): void{
		$this->outboundData[] = (array)[$id, $data];
	}

	public function setStatus(int $status): void{
		Utils::assert($status >= 0 and $status < 10);
		$this->status = $status;
	}

	public function getStatus(): int{
		return $this->status;
	}
}