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

use Discord\Parts\Channel\Channel;
use Discord\Parts\Guild\Guild;
use Discord\Parts\User\Member;
use JaxkDev\DiscordBot\Bot\Client;
use JaxkDev\DiscordBot\Communication\Packets\Heartbeat;
use JaxkDev\DiscordBot\Communication\Packets\Packet;
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

	public function handle(Packet $packet): bool{
		// Utils::assert(is_int($data[0]), "Corrupt internal communication data received.");

		if($packet instanceof Heartbeat) return $this->handleHeartbeat($packet);
		if($packet instanceof UpdateActivity) return $this->handleUpdateActivity($packet);
		if($packet instanceof SendMessage) return $this->handleSendMessage($packet;

		return false;
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
		Utils::assert(in_array($data[0],
			[Protocol::ACTIVITY_TYPE_PLAYING, Protocol::ACTIVITY_TYPE_LISTENING, Protocol::ACTIVITY_TYPE_STREAMING]),
			"Activity type '{$data[0]}' received is not valid.");

		$this->client->updatePresence($data[1], $data[0]);

		return true;
	}

	private function handleHeartbeat(Heartbeat $packet): bool{
		$this->lastHeartbeat = $packet->getHeartbeat();
		return true;
	}


	public function sendHeartbeat(): void{
		$p = new Heartbeat();
		$p->setHeartbeat(microtime(true));
		$this->client->getThread()->writeOutboundData($p);
	}

	public function sendMessageSentEvent(Guild $server, Channel $channel, Member $author, string $content): void{
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