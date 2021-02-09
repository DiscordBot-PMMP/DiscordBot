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
use JaxkDev\DiscordBot\Communication\Models\Channel;
use JaxkDev\DiscordBot\Communication\Models\Invite;
use JaxkDev\DiscordBot\Communication\Models\Member;
use JaxkDev\DiscordBot\Communication\Models\Message;
use JaxkDev\DiscordBot\Communication\Models\Role;
use JaxkDev\DiscordBot\Communication\Models\Server;
use JaxkDev\DiscordBot\Communication\Models\User;
use JaxkDev\DiscordBot\Communication\Packets\Discord\DiscordEventChannelCreate;
use JaxkDev\DiscordBot\Communication\Packets\Discord\DiscordEventChannelDelete;
use JaxkDev\DiscordBot\Communication\Packets\Discord\DiscordEventChannelUpdate;
use JaxkDev\DiscordBot\Communication\Packets\Discord\DiscordEventInviteCreate;
use JaxkDev\DiscordBot\Communication\Packets\Discord\DiscordEventInviteDelete;
use JaxkDev\DiscordBot\Communication\Packets\Discord\DiscordEventMemberJoin;
use JaxkDev\DiscordBot\Communication\Packets\Discord\DiscordEventMemberLeave;
use JaxkDev\DiscordBot\Communication\Packets\Discord\DiscordEventMemberUpdate;
use JaxkDev\DiscordBot\Communication\Packets\Discord\DiscordEventMessageDelete;
use JaxkDev\DiscordBot\Communication\Packets\Discord\DiscordEventMessageSent;
use JaxkDev\DiscordBot\Communication\Packets\Discord\DiscordEventMessageUpdate;
use JaxkDev\DiscordBot\Communication\Packets\Discord\DiscordEventRoleCreate;
use JaxkDev\DiscordBot\Communication\Packets\Discord\DiscordEventRoleDelete;
use JaxkDev\DiscordBot\Communication\Packets\Discord\DiscordEventRoleUpdate;
use JaxkDev\DiscordBot\Communication\Packets\Discord\DiscordEventServerJoin;
use JaxkDev\DiscordBot\Communication\Packets\Discord\DiscordEventServerLeave;
use JaxkDev\DiscordBot\Communication\Packets\Discord\DiscordEventServerUpdate;
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


	//--- Outbound:
	// TODO, Dont think this should be here, happy for it to be directly from discord event handler.


	public function sendHeartbeat(): void{
		$packet = new Heartbeat();
		$packet->setHeartbeat(microtime(true));
		$this->client->getThread()->writeOutboundData($packet);
	}

	public function sendChannelCreateEvent(Channel $channel): void{
		$packet = new DiscordEventChannelCreate();
		$packet->setChannel($channel);
		$this->client->getThread()->writeOutboundData($packet);
	}

	public function sendChannelUpdateEvent(Channel $channel): void{
		$packet = new DiscordEventChannelUpdate();
		$packet->setChannel($channel);
		$this->client->getThread()->writeOutboundData($packet);
	}

	public function sendChannelDeleteEvent(Channel $channel): void{
		$packet = new DiscordEventChannelDelete();
		$packet->setChannel($channel);
		$this->client->getThread()->writeOutboundData($packet);
	}

	public function sendMessageSentEvent(Message $message): void{
		$packet = new DiscordEventMessageSent();
		$packet->setMessage($message);
		$this->client->getThread()->writeOutboundData($packet);
	}

	public function sendMessageDeleteEvent(string $message): void{
		$packet = new DiscordEventMessageDelete();
		$packet->setMessageId($message);
		$this->client->getThread()->writeOutboundData($packet);
	}

	public function sendMessageUpdateEvent(Message $message): void{
		$packet = new DiscordEventMessageUpdate();
		$packet->setMessage($message);
		$this->client->getThread()->writeOutboundData($packet);
	}

	public function sendMemberJoinEvent(Member $member, User $user): void{
		$packet = new DiscordEventMemberJoin();
		$packet->setMember($member);
		$packet->setUser($user);
		$this->client->getThread()->writeOutboundData($packet);
	}

	public function sendMemberLeaveEvent(string $member_id): void{
		$packet = new DiscordEventMemberLeave();
		$packet->setMemberID($member_id);
		$this->client->getThread()->writeOutboundData($packet);
	}

	public function sendMemberUpdateEvent(Member $member): void{
		$packet = new DiscordEventMemberUpdate();
		$packet->setMember($member);
		$this->client->getThread()->writeOutboundData($packet);
	}

	public function sendRoleCreateEvent(Role $role): void{
		$packet = new DiscordEventRoleCreate();
		$packet->setRole($role);
		$this->client->getThread()->writeOutboundData($packet);
	}

	public function sendRoleUpdateEvent(Role $role): void{
		$packet = new DiscordEventRoleUpdate();
		$packet->setRole($role);
		$this->client->getThread()->writeOutboundData($packet);
	}

	public function sendRoleDeleteEvent(Role $role): void{
		$packet = new DiscordEventRoleDelete();
		$packet->setRole($role);
		$this->client->getThread()->writeOutboundData($packet);
	}

	public function sendInviteCreateEvent(Invite $invite): void{
		$packet = new DiscordEventInviteCreate();
		$packet->setInvite($invite);
		$this->client->getThread()->writeOutboundData($packet);
	}

	public function sendInviteDeleteEvent(string $invite_code): void{
		$packet = new DiscordEventInviteDelete();
		$packet->setInviteCode($invite_code);
		$this->client->getThread()->writeOutboundData($packet);
	}

	/**
	 * @param Server 	$server
	 * @param Channel[] $channels
	 * @param Role[]  	$roles
	 * @param Member[]	$members
	 */
	public function sendServerJoinEvent(Server $server, array $channels, array $roles, array $members): void{
		$packet = new DiscordEventServerJoin();
		$packet->setServer($server);
		$packet->setChannels($channels);
		$packet->setMembers($members);
		$packet->setRoles($roles);
		$this->client->getThread()->writeOutboundData($packet);
	}

	public function sendServerLeaveEvent(Server $server): void{
		$packet = new DiscordEventServerLeave();
		$packet->setServer($server);
		$this->client->getThread()->writeOutboundData($packet);
	}

	public function sendServerUpdateEvent(Server $server): void{
		$packet = new DiscordEventServerUpdate();
		$packet->setServer($server);
		$this->client->getThread()->writeOutboundData($packet);
	}

	//--- Utils:


	public function checkHeartbeat(): void{
		if($this->lastHeartbeat === null) return;
		if(($diff = (microtime(true) - $this->lastHeartbeat)) > Protocol::HEARTBEAT_ALLOWANCE){
			MainLogger::getLogger()->emergency("Plugin has not responded for ".Protocol::HEARTBEAT_ALLOWANCE." seconds, shutting self down.");
			$this->client->close();
		}
	}

	public function getLastHeartbeat(): ?float{
		return $this->lastHeartbeat;
	}
}