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

namespace JaxkDev\DiscordBot\Plugin\Handlers;

use JaxkDev\DiscordBot\Communication\Protocol;
use JaxkDev\DiscordBot\Main;
use pocketmine\utils\MainLogger;

class BotCommunicationHandler {
	/**
	 * @var Main
	 */
	private $plugin;

	/**
	 * @var float
	 */
	private $lastHeartbeat;

	public function __construct(Main $plugin){
		$this->plugin = $plugin;
	}

	public function handle(array $data): bool{
		assert(is_int($data[0]));
		switch ($data[0]){
			case Protocol::TYPE_HEARTBEAT:
				return $this->handleHeartbeat($data[1]);
			case Protocol::TYPE_BOT_READY:
				//return $this->handleBotReady($data[1]);
			case Protocol::TYPE_STATS_REQUEST:
				//return $this->handleStatsRequest($data[1]);
			case Protocol::TYPE_STATS_RESPONSE:
				//return $this->handleStatsResponse($data[1]);
			default:
				return false;
				// throw new \InvalidKeyException("Invalid ID ({$data[0]}) Received from internal communication.");
		}
	}


	private function handleHeartbeat(array $data): bool{
		assert((count($data) === 1) and is_numeric($data[0]));

		//$this->plugin->getLogger()->debug("Heartbeat received: {$data[0]}");
		$this->lastHeartbeat = (float)$data[0];

		return true;
	}

	public function checkHeartbeat(): void{
		if(($diff = microtime(true) - $this->lastHeartbeat) > Protocol::HEARTBEAT_ALLOWANCE){
			// Bot is dead, shutdown plugin.
			MainLogger::getLogger()->emergency("DiscordBot has not responded for 2 seconds, disabling plugin + bot.");
			$this->plugin->getServer()->getPluginManager()->disablePlugin($this->plugin);
		}
	}

	public function sendHeartbeat(): void{
		$this->plugin->writeOutboundData(
			Protocol::TYPE_HEARTBEAT,
			[microtime(true)]
		);
	}

	public function getLastHeartbeat(): float {
		return $this->lastHeartbeat;
	}
}