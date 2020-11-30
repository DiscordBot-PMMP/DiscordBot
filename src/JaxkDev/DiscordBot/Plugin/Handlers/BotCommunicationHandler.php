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
			case Protocol::ID_EVENT_MEMBER_JOIN:
				return $this->handleMemberJoin($data[1]);
			case Protocol::ID_EVENT_MEMBER_LEAVE:
				return $this->handleMemberLeave($data[1]);
			/*case Protocol::ID_EVENT_MESSAGE_SENT:
				return $this->handleMessageSent($data[1]);*/
			default:
				return false;
				// throw new \InvalidKeyException("Invalid ID ({$data[0]}) Received from internal communication.");
		}
	}

	/**
	 * @param array $data [float TIMESTAMP]
	 * @return bool
	 */
	private function handleHeartbeat(array $data): bool{
		Utils::assert((count($data) === 1) and is_numeric($data[0]), "Invalid heartbeat data.");

		$this->lastHeartbeat = (float)$data[0];

		return true;
	}

	/**
	 * @param array $data [string serverID, string serverName, string userId, string userDiscriminator, string userName, int timestamp]
	 * @return bool
	 */
	private function handleMemberJoin(array $data): bool{
		Utils::assert((count($data) === 6)/* and all values here */);

		$config = $this->plugin->getEventsConfig()['member_join']['fromDiscord'];
		$message = str_replace(['{TIME}', '{USER_ID}', '{USERNAME}', '{USER_DISCRIMINATOR}', '{SERVER_ID}', '{SERVER_NAME}'],
			[date('G:i:s', $data[5]), $data[2], $data[4], $data[3], $data[0], $data[1]], $config['format']);

		$this->plugin->getServer()->broadcastMessage($message);

		return true;
	}

	/**
	 * @param array $data [string serverID, string serverName, string userId, string userDiscriminator, string userName, int timestamp]
	 * @return bool
	 */
	private function handleMemberLeave(array $data): bool{
		Utils::assert((count($data) === 6)/* and all values here */);

		$config = $this->plugin->getEventsConfig()['member_leave']['fromDiscord'];
		$message = str_replace(['{TIME}', '{USER_ID}', '{USERNAME}', '{USER_DISCRIMINATOR}', '{SERVER_ID}', '{SERVER_NAME}'],
			[date('G:i:s', $data[5]), $data[2], $data[4], $data[3], $data[0], $data[1]], $config['format']);

		$this->plugin->getServer()->broadcastMessage($message);

		return true;
	}

	public function sendMessage(string $guild, string $channel, string $content): void{
		$this->plugin->writeOutboundData(
			Protocol::ID_SEND_MESSAGE,
			[$guild, $channel, $content]
		);
	}

	/**
	 * Checks last KNOWN Heartbeat timestamp with current time, does not check pre-start condition.
	 */
	public function checkHeartbeat(): void{
		if(($diff = microtime(true) - ($this->lastHeartbeat ?? microtime(true))) > Protocol::HEARTBEAT_ALLOWANCE){
			// Bot is dead, shutdown plugin.
			MainLogger::getLogger()->emergency("DiscordBot has not responded for 2 seconds, disabling plugin + bot.");
			$this->plugin->stopAll();
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