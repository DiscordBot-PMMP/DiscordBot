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
use JaxkDev\DiscordBot\Utils;
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
		Utils::assert(is_int($data[0]), "Corrupt internal communication data received.");
		switch ($data[0]){
			case Protocol::ID_HEARTBEAT:
				return $this->handleHeartbeat($data[1]);
			default:
				return false;
				// throw new \InvalidKeyException("Invalid ID ({$data[0]}) Received from internal communication.");
		}
	}


	private function handleHeartbeat(array $data): bool{
		Utils::assert((count($data) === 1) and is_numeric($data[0]), "Invalid heartbeat data.");

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
			Protocol::ID_HEARTBEAT,
			[microtime(true)]
		);
	}

	public function getLastHeartbeat(): float {
		return $this->lastHeartbeat;
	}
}