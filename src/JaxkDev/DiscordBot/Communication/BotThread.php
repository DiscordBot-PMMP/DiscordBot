<?php
/*
 * DiscordBot, PocketMine-MP Plugin.
 *
 * Licensed under the Open Software License version 3.0 (OSL-3.0)
 * Copyright (C) 2020-2021 JaxkDev
 *
 * Twitter :: @JaxkDev
 * Discord :: JaxkDev#2698
 * Email   :: JaxkDev@gmail.com
 */

namespace JaxkDev\DiscordBot\Communication;

use AttachableThreadedLogger;
use JaxkDev\DiscordBot\Bot\Client;
use JaxkDev\DiscordBot\Communication\Packets\Packet;
use JaxkDev\DiscordBot\Utils;
use pocketmine\Thread;
use pocketmine\utils\MainLogger;
use Volatile;

class BotThread extends Thread{

	/** @var AttachableThreadedLogger */
	private $logger;

	/**  @var array */
	private $initialConfig;

	/** @var Volatile */
	private $inboundData;
	/** @var Volatile */
	private $outboundData;

	/** @var int */
	private $status = Protocol::THREAD_STATUS_STARTING;

	public function __construct(AttachableThreadedLogger $logger, array $initialConfig, Volatile $inboundData, Volatile $outboundData){
		$this->logger = $logger;
		$this->initialConfig = $initialConfig;
		$this->inboundData = $inboundData;
		$this->outboundData = $outboundData;
	}

	public function run(){
		if($this->logger instanceof MainLogger){
			$this->logger->registerStatic();
		}

		$this->registerClassLoader();

		/** @noinspection PhpIncludeInspection */
		require_once(\JaxkDev\DiscordBot\COMPOSER);

		new Client($this, (array)$this->initialConfig);
	}

	public function readInboundData(int $count = 1): array{
		return array_map(function($data){
			/** @var Packet $packet */
			$packet = unserialize($data);
			Utils::assert($packet instanceof Packet);
			return $packet;
		}, $this->inboundData->chunk($count, false));
	}

	public function writeOutboundData(Packet $packet): void{
		$this->outboundData[] = serialize($packet);
	}

	//TODO Investigate best solution for status communication (refer to new ready packet)
	public function setStatus(int $status): void{
		Utils::assert($status >= 0 and $status < 10);
		$this->status = $status;
	}

	public function getStatus(): int{
		return $this->status;
	}
}