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
use JaxkDev\DiscordBot\Communication\Models\Member;
use JaxkDev\DiscordBot\Communication\Models\Message;
use JaxkDev\DiscordBot\Communication\Models\User;
use JaxkDev\DiscordBot\Communication\Packets\DiscordMemberJoin;
use JaxkDev\DiscordBot\Communication\Packets\DiscordMemberLeave;
use JaxkDev\DiscordBot\Communication\Packets\DiscordMessageSent;
use JaxkDev\DiscordBot\Communication\Packets\Heartbeat;
use JaxkDev\DiscordBot\Communication\Packets\Packet;
use JaxkDev\DiscordBot\Communication\Protocol;
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
		if($packet instanceof Heartbeat) return $this->handleHeartbeat($packet);
		//if($packet instanceof UpdateActivity) return $this->handleUpdateActivity($packet);
		//if($packet instanceof SendMessage) return $this->handleSendMessage($packet;

		return false;
	}

	/*private function handleSendMessage(array $data): bool{
		Utils::assert((count($data) === 3) and strlen($data[0]) === 18 and strlen($data[1]) === 18
			and strlen($data[2]) < 2000, "Invalid message data received.");

		$this->client->sendMessage($data[0], $data[1], $data[2]);

		return true;
	}*/

	/*private function handleUpdateActivity(array $data): bool{
		Utils::assert(in_array($data[0],
			[Protocol::ACTIVITY_TYPE_PLAYING, Protocol::ACTIVITY_TYPE_LISTENING, Protocol::ACTIVITY_TYPE_STREAMING]),
			"Activity type '{$data[0]}' received is not valid.");

		$this->client->updatePresence($data[1], $data[0]);

		return true;
	}*/

	private function handleHeartbeat(Heartbeat $packet): bool{
		$this->lastHeartbeat = $packet->getHeartbeat();
		return true;
	}


	public function sendHeartbeat(): void{
		$packet = new Heartbeat();
		$packet->setHeartbeat(microtime(true));
		$this->client->getThread()->writeOutboundData($packet);
	}

	public function sendMessageSentEvent(Message $message): void{
		$packet = new DiscordMessageSent();
		$packet->setMessage($message);
		$this->client->getThread()->writeOutboundData($packet);
	}

	public function sendMemberJoinEvent(Member $member, User $user): void{
		$packet = new DiscordMemberJoin();
		$packet->setMember($member);
		$packet->setUser($user);
		$this->client->getThread()->writeOutboundData($packet);
	}

	public function sendMemberLeaveEvent(string $member_id): void{
		$packet = new DiscordMemberLeave();
		$packet->setMemberID($member_id);
		$this->client->getThread()->writeOutboundData($packet);
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