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

use JaxkDev\DiscordBot\Communication\Packets\Heartbeat;
use JaxkDev\DiscordBot\Communication\Packets\MemberJoin;
use JaxkDev\DiscordBot\Communication\Packets\Packet;
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

	public function handle(Packet $packet): bool{
		// TODO, Dictionary Based compression of servers to reduce load/memory going across threads.
		// If's instances instead of ID switching due to phpstan/types.
		if($packet instanceof Heartbeat) return $this->handleHeartbeat($packet);
		if($packet instanceof MemberJoin) return $this->handleMemberJoin($packet);
		if($packet instanceof MemberLeave) return $this->handleMemberLeave($packet);
		if($packet instanceof MessageSent) return $this->handleMessageSent($packet);

		// throw new \InvalidKeyException("Invalid ID ({$data[0]}) Received from internal communication.");
		return false;
	}

	private function handleHeartbeat(Heartbeat $packet): bool{
		$this->lastHeartbeat = $packet->getHeartbeat();
		return true;
	}

	/**
	 * @param array $data [string serverID, string serverName, string userId, string userDiscriminator, string userName,
	 * string channelId, string channelName, string content, int timestamp]
	 * @return bool
	 */
	private function handleMessageSent(array $data): bool{
		Utils::assert((count($data) === 9)/* and all values here */);

		$config = $this->plugin->getEventsConfig()['message']['fromDiscord'];

		if(!in_array($data[0].".".$data[5], $config['channels'])) return true;

		$message = str_replace(['{TIME}', '{USER_ID}', '{USERNAME}', '{USER_DISCRIMINATOR}', '{SERVER_ID}',
			'{SERVER_NAME}', '{CHANNEL_ID}', '{CHANNEL_NAME}', '{MESSAGE}'],
			[date('G:i:s', $data[8]), $data[2], $data[4], $data[3], $data[0], $data[1], $data[5], $data[6], $data[7]],
			$config['format']);

		$this->plugin->getServer()->broadcastMessage($message);

		return true;
	}

	/**
	 * @param array $data [string serverID, string serverName, string userId, string userDiscriminator, string userName, int timestamp]
	 * @return bool
	 */
	private function handleMemberJoin(array $data): bool{
		Utils::assert((count($data) === 6)/* and all values here */);

		$config = $this->plugin->getEventsConfig()['member_join']['fromDiscord'];
		if(($config['format'] ?? "") === "") return true;

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
		if(($config['format'] ?? "") === "") return true;

		$message = str_replace(['{TIME}', '{USER_ID}', '{USERNAME}', '{USER_DISCRIMINATOR}', '{SERVER_ID}', '{SERVER_NAME}'],
			[date('G:i:s', $data[5]), $data[2], $data[4], $data[3], $data[0], $data[1]], $config['format']);

		$this->plugin->getServer()->broadcastMessage($message);

		return true;
	}

	public function sendMessage(string $guild, string $channel, string $content): void{
		/*$this->plugin->writeOutboundData(
			Protocol::ID_SEND_MESSAGE,
			[$guild, $channel, $content]
		);*/
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
		$p = new Heartbeat();
		$p->setHeartbeat(microtime(true));
		$this->plugin->writeOutboundData($p);
	}

	public function getLastHeartbeat(): float {
		return $this->lastHeartbeat;
	}
}