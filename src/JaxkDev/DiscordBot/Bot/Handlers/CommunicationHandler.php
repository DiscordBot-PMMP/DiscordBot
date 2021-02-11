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

namespace JaxkDev\DiscordBot\Bot\Handlers;

use JaxkDev\DiscordBot\Bot\Client;
use JaxkDev\DiscordBot\Communication\Packets\Heartbeat;
use JaxkDev\DiscordBot\Communication\Packets\Packet;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\PluginRequestSendMessage;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\PluginRequestUpdateActivity;
use JaxkDev\DiscordBot\Communication\Protocol;
use pocketmine\utils\MainLogger;

class CommunicationHandler{

	/** @var Client */
	private $client;

	/** @var float|null */
	private $lastHeartbeat = null;

	public function __construct(Client $client){
		$this->client = $client;
	}

	//--- Handlers:

	public function handle(Packet $packet): bool{
		if($packet instanceof Heartbeat) return $this->handleHeartbeat($packet);
		if($packet instanceof PluginRequestUpdateActivity) return $this->handleUpdateActivity($packet);
		if($packet instanceof PluginRequestSendMessage) return $this->handleSendMessage($packet);
		return false;
	}

	private function handleSendMessage(PluginRequestSendMessage $packet): bool{
		$this->client->sendMessage($packet->getMessage());
		return true;
	}

	private function handleUpdateActivity(PluginRequestUpdateActivity $packet): bool{
		$this->client->updatePresence($packet->getActivity());
		return true;
	}

	private function handleHeartbeat(Heartbeat $packet): bool{
		$this->lastHeartbeat = $packet->getHeartbeat();
		return true;
	}

	//---------------------------------------------------

	public function sendHeartbeat(): void{
		$packet = new Heartbeat();
		$packet->setHeartbeat(microtime(true));
		$this->client->getThread()->writeOutboundData($packet);
	}

	public function checkHeartbeat(): void{
		if($this->lastHeartbeat === null) return;
		if(($diff = (microtime(true) - $this->lastHeartbeat)) > Protocol::HEARTBEAT_ALLOWANCE){
			MainLogger::getLogger()->emergency("Plugin has not responded for ".
				Protocol::HEARTBEAT_ALLOWANCE." seconds, shutting self down.");
			$this->client->close();
		}
	}

	public function getLastHeartbeat(): ?float{
		return $this->lastHeartbeat;
	}
}