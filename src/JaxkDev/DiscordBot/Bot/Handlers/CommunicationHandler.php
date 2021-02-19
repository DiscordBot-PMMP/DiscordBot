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

use Discord\Parts\Channel\Channel as DiscordChannel;
use Discord\Parts\Channel\Message as DiscordMessage;
use Discord\Parts\Guild\Guild as DiscordGuild;
use Discord\Parts\User\Activity as DiscordActivity;
use JaxkDev\DiscordBot\Bot\Client;
use JaxkDev\DiscordBot\Bot\ModelConverter;
use JaxkDev\DiscordBot\Models\Activity;
use JaxkDev\DiscordBot\Communication\Packets\Resolution;
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
		$ack = new Resolution();
		$ack->setPid($packet->getUID());
		if($this->client->getThread()->getStatus() !== Protocol::THREAD_STATUS_READY){
			$ack->setRejectReason("Thread not ready for API Requests.");
			$ack->setSuccessful(false);
			$this->client->getThread()->writeOutboundData($ack);
			return true;
		}
		$message = $packet->getMessage();

		/** @noinspection PhpUnhandledExceptionInspection */ //Impossible.
		$this->client->getDiscordClient()->guilds->fetch($message->getServerId())->done(function(DiscordGuild $guild) use($ack, $message){
			$guild->channels->fetch($message->getChannelId())->done(function(DiscordChannel $channel) use($ack, $message){
				$channel->sendMessage($message->getContent())->then(function(DiscordMessage $msg) use($ack){
					$ack->setSuccessData([ModelConverter::genModelMessage($msg)]);
					$this->client->getThread()->writeOutboundData($ack);
					MainLogger::getLogger()->debug("Sent message(".strlen($msg->content).") to ({$msg->channel_id})");
				}, function(\Throwable $e) use($ack){
					$ack->setSuccessful(false);
					$ack->setRejectReason("Failed to send message (generic, Error: {$e->getMessage()})");
					$this->client->getThread()->writeOutboundData($ack);
				});
			}, function(\Throwable $e) use($ack, $message){
				$ack->setSuccessful(false);
				$ack->setRejectReason("Failed to fetch channel {$message->getChannelId()} in server {$message->getServerId()}. (Error: {$e->getMessage()})");
				$this->client->getThread()->writeOutboundData($ack);
			});
		}, function(\Throwable $e) use($ack, $message){
			$ack->setSuccessful(false);
			$ack->setRejectReason("Failed to fetch server {$message->getServerId()}. (Error: {$e->getMessage()})");
			$this->client->getThread()->writeOutboundData($ack);
		});
		return true;
	}

	private function handleUpdateActivity(PluginRequestUpdateActivity $packet): bool{
		$activity = $packet->getActivity();
		$presence = new DiscordActivity($this->client->getDiscordClient(), [
			'name' => $activity->getMessage(),
			'type' => $activity->getType()
		]);

		$ack = new Resolution();
		$ack->setPid($packet->getUID());
		try{
			$this->client->getDiscordClient()->updatePresence($presence, $activity->getStatus() === Activity::STATUS_IDLE, $activity->getStatus());
			$this->client->getThread()->writeOutboundData($ack);
		}catch (\Throwable $e){
			$ack->setRejectReason($e->getMessage());
			$ack->setSuccessful(false);
			$this->client->getThread()->writeOutboundData($ack);
		}
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