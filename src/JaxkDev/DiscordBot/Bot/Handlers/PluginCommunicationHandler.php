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

namespace JaxkDev\DiscordBot\Bot\Handlers;

use JaxkDev\DiscordBot\Bot\Client;
use JaxkDev\DiscordBot\Communication\Protocol;
use JaxkDev\DiscordBot\Utils;
use pocketmine\utils\MainLogger;

class PluginCommunicationHandler {

	/**
	 * @var Client
	 */
	private $client;

	/**
	 * @var float
	 */
	private $lastHeartbeat;

	public function __construct(Client $client){
		$this->client = $client;
	}

	public function handle(array $data): bool{
		Utils::assert(is_int($data[0]), "Corrupt internal communication data received.");

		switch ($data[0]){
			case Protocol::ID_HEARTBEAT:
				return $this->handleHeartbeat($data[1]);
			case Protocol::ID_UPDATE_ACTIVITY:
				return $this->handleUpdateActivity($data[1]);
			case Protocol::ID_SEND_MESSAGE:
				return $this->handleSendMessage($data[1]);
			default:
				return false;
		}
	}

	/**
	 * @param array $data [string(18) SERVER_ID, string(18) CHANNEL_ID, string(2000) TEXT]
	 * @return bool
	 */
	private function handleSendMessage(array $data): bool{
		Utils::assert((count($data) === 3) and strlen($data[0]) === 18 and strlen($data[1]) === 18
			and strlen($data[2]) < 2000, "Invalid message data received.");

		$this->client->sendMessage($data[0], $data[1], $data[2]);

		return true;
	}

	/**
	 * @param array $data [int ACTIVITY_TYPE, string TEXT]
	 * @return bool
	 */
	private function handleUpdateActivity(array $data): bool{
		Utils::assert((count($data) === 2) and is_int($data[0]) and is_string($data[1]), "Invalid UpdateActivity data received.");
		Utils::assert(in_array($data[0],
			[Protocol::ACTIVITY_TYPE_PLAYING, Protocol::ACTIVITY_TYPE_LISTENING, Protocol::ACTIVITY_TYPE_STREAMING]),
			"Activity type '{$data[0]}' received is not valid.");

		$this->client->updatePresence($data[1], $data[0]);

		return true;
	}

	/**
	 * @param array $data [float TIMESTAMP]
	 * @return bool
	 */
	private function handleHeartbeat(array $data): bool{
		Utils::assert((count($data) === 1) and is_numeric($data[0]), "Invalid Heartbeat data received.");

		$this->lastHeartbeat = (float)$data[0];

		return true;
	}


	public function sendHeartbeat(): void{
		$this->client->getThread()->writeOutboundData(
			Protocol::ID_HEARTBEAT,
			[microtime(true)]
		);
	}

	public function sendMessageSentEvent(string $serverId, string $serverName, string $userId, string $userDiscriminator,
									 string $userName, string $channelId, string $channelName, string $content,
									 float $timestamp){
		$this->client->getThread()->writeOutboundData(
			Protocol::ID_EVENT_MESSAGE_SENT,
			[
				$serverId,
				$serverName,
				$userId,
				$userDiscriminator,
				$userName,
				$channelId,
				$channelName,
				$content,
				$timestamp
			]
		);
	}

	/**
	 * @param string $serverId			Server's ID (18-length)
	 * @param string $serverName		Server's Name
	 * @param string $userId			User's ID (18-length)
	 * @param string $userDiscriminator	User's Discriminator (4-length)
	 * @param string $userName			Username
	 * @param float $timestamp			Timestamp of join
	 * @return void
	 */
	public function sendMemberJoinEvent(string $serverId, string $serverName, string $userId, string $userDiscriminator,
										string $userName, float $timestamp): void{
		$this->client->getThread()->writeOutboundData(
			Protocol::ID_EVENT_MEMBER_JOIN,
			[
				$serverId,
				$serverName,
				$userId,
				$userDiscriminator,
				$userName,
				$timestamp
			]
		);
	}

	/**
	 * @param string $serverId			Server's ID (18-length)
	 * @param string $serverName		Server's Name
	 * @param string $userId			User's ID (18-length)
	 * @param string $userDiscriminator	User's Discriminator (4-length)
	 * @param string $userName			Username
	 * @param float $timestamp			Timestamp of join
	 * @return void
	 */
	public function sendMemberLeaveEvent(string $serverId, string $serverName, string $userId, string $userDiscriminator,
										string $userName, float $timestamp): void{
		$this->client->getThread()->writeOutboundData(
			Protocol::ID_EVENT_MEMBER_LEAVE,
			[
				$serverId,
				$serverName,
				$userId,
				$userDiscriminator,
				$userName,
				$timestamp
			]
		);
	}

	public function checkHeartbeat(): void{
		if(($diff = microtime(true) - ($this->lastHeartbeat ?? microtime(true))) > Protocol::HEARTBEAT_ALLOWANCE){
			// Plugin is dead, shutdown self.
			MainLogger::getLogger()->emergency("Plugin has not responded for 2 seconds, shutting self down.");
			$this->client->close();
		}
	}

	public function getLastHeartbeat(): float {
		return $this->lastHeartbeat;
	}
}