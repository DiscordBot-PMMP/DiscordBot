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

namespace JaxkDev\DiscordBot\Plugin\Handlers;

use JaxkDev\DiscordBot\Communication\Models\Channel;
use JaxkDev\DiscordBot\Communication\Models\Member;
use JaxkDev\DiscordBot\Communication\Models\Server;
use JaxkDev\DiscordBot\Communication\Models\User;
use JaxkDev\DiscordBot\Communication\Packets\DiscordEventAllData;
use JaxkDev\DiscordBot\Communication\Packets\DiscordEventChannelCreate;
use JaxkDev\DiscordBot\Communication\Packets\DiscordEventChannelDelete;
use JaxkDev\DiscordBot\Communication\Packets\DiscordEventChannelUpdate;
use JaxkDev\DiscordBot\Communication\Packets\DiscordEventMemberJoin;
use JaxkDev\DiscordBot\Communication\Packets\DiscordEventMemberLeave;
use JaxkDev\DiscordBot\Communication\Packets\DiscordEventMemberUpdate;
use JaxkDev\DiscordBot\Communication\Packets\DiscordEventMessageDelete;
use JaxkDev\DiscordBot\Communication\Packets\DiscordEventMessageSent;
use JaxkDev\DiscordBot\Communication\Packets\DiscordEventMessageUpdate;
use JaxkDev\DiscordBot\Communication\Packets\DiscordEventRoleCreate;
use JaxkDev\DiscordBot\Communication\Packets\DiscordEventRoleDelete;
use JaxkDev\DiscordBot\Communication\Packets\DiscordEventRoleUpdate;
use JaxkDev\DiscordBot\Communication\Packets\DiscordEventServerJoin;
use JaxkDev\DiscordBot\Communication\Packets\DiscordEventServerLeave;
use JaxkDev\DiscordBot\Communication\Packets\DiscordEventServerUpdate;
use JaxkDev\DiscordBot\Communication\Packets\Heartbeat;
use JaxkDev\DiscordBot\Communication\Packets\Packet;
use JaxkDev\DiscordBot\Communication\Protocol;
use JaxkDev\DiscordBot\Plugin\Main;
use JaxkDev\DiscordBot\Plugin\Storage;
use JaxkDev\DiscordBot\Utils;
use pocketmine\utils\MainLogger;

class BotCommunicationHandler{

	/** @var Main */
	private $plugin;

	/** @var float|null */
	private $lastHeartbeat = null;

	public function __construct(Main $plugin){
		$this->plugin = $plugin;
	}

	public function handle(Packet $packet): bool{
		// If's instances instead of ID switching due to phpstan/types.
		if($packet instanceof Heartbeat) return $this->handleHeartbeat($packet);
		if($packet instanceof DiscordEventMemberJoin) return $this->handleMemberJoin($packet);
		if($packet instanceof DiscordEventMemberLeave) return $this->handleMemberLeave($packet);
		if($packet instanceof DiscordEventMemberUpdate) return $this->handleMemberUpdate($packet);
		if($packet instanceof DiscordEventMessageSent) return $this->handleMessageSent($packet);
		if($packet instanceof DiscordEventMessageUpdate) return $this->handleMessageUpdate($packet);
		if($packet instanceof DiscordEventMessageDelete) return $this->handleMessageDelete($packet);
		if($packet instanceof DiscordEventChannelCreate) return $this->handleChannelCreate($packet);
		if($packet instanceof DiscordEventChannelUpdate) return $this->handleChannelUpdate($packet);
		if($packet instanceof DiscordEventChannelDelete) return $this->handleChannelDelete($packet);
		if($packet instanceof DiscordEventRoleCreate) return $this->handleRoleCreate($packet);
		if($packet instanceof DiscordEventRoleUpdate) return $this->handleRoleUpdate($packet);
		if($packet instanceof DiscordEventRoleDelete) return $this->handleRoleDelete($packet);
		if($packet instanceof DiscordEventServerJoin) return $this->handleServerJoin($packet);
		if($packet instanceof DiscordEventServerLeave) return $this->handleServerLeave($packet);
		if($packet instanceof DiscordEventServerUpdate) return $this->handleServerUpdate($packet);
		if($packet instanceof DiscordEventAllData) return $this->handleAllDiscordData($packet);
		return false;
	}

	private function handleHeartbeat(Heartbeat $packet): bool{
		$this->lastHeartbeat = $packet->getHeartbeat();
		return true;
	}

	private function handleMessageSent(DiscordEventMessageSent $packet): bool{
		$config = $this->plugin->getEventsConfig()['message']['fromDiscord'];
		$message = $packet->getMessage();

		if(!in_array($message->getChannelId(), $config['channels'])) return true;

		//If any of these asserts fire theres a mismatch between Storage and discord.

		/** @var Server $server */
		$server = Storage::getServer($message->getServerId());
		Utils::assert($server instanceof Server);

		/** @var Channel $channel */
		$channel = Storage::getChannel($message->getChannelId());
		Utils::assert($channel instanceof Channel);

		/** @var Member $author */
		$author = Storage::getMember($message->getAuthorId()??"");
		Utils::assert($author instanceof Member);

		/** @var User $user */
		$user = Storage::getUser($author->getUserId());
		Utils::assert($user instanceof User);

		$formatted = str_replace(['{TIME}', '{USER_ID}', '{USERNAME}', '{USER_DISCRIMINATOR}', '{SERVER_ID}',
			'{SERVER_NAME}', '{CHANNEL_ID}', '{CHANNEL_NAME}', '{MESSAGE}'], [
				date('G:i:s', (int)$message->getTimestamp()??0), $author->getUserId(), $user->getUsername(),
				$user->getDiscriminator(), $server->getId(), $server->getName(), $channel->getId(), $channel->getName(),
				$message->getContent()
			],
			$config['format']);

		$this->plugin->getServer()->broadcastMessage($formatted);
		return true;
	}

	private function handleMessageUpdate(DiscordEventMessageUpdate $packet): bool{
		return false;
	}

	private function handleMessageDelete(DiscordEventMessageDelete $packet): bool{
		return false;
	}

	private function handleChannelCreate(DiscordEventChannelCreate $packet): bool{
		Storage::addChannel($packet->getChannel());
		$this->plugin->getServer()->broadcastMessage("Channel '".$packet->getChannel()->getName()."' created.");
		return true;
	}

	private function handleChannelUpdate(DiscordEventChannelUpdate $packet): bool{
		Storage::updateChannel($packet->getChannel());
		$this->plugin->getServer()->broadcastMessage("Channel '".$packet->getChannel()->getName()."' updated.");
		return true;
	}

	private function handleChannelDelete(DiscordEventChannelDelete $packet): bool{
		Storage::removeChannel($packet->getChannel()->getId());
		$this->plugin->getServer()->broadcastMessage("Channel '".$packet->getChannel()->getName()."' deleted.");
		return true;
	}

	private function handleRoleCreate(DiscordEventRoleCreate $packet): bool{
		Storage::addRole($packet->getRole());
		$this->plugin->getServer()->broadcastMessage("Role '".$packet->getRole()->getName()."' created.");
		return true;
	}

	private function handleRoleUpdate(DiscordEventRoleUpdate $packet): bool{
		Storage::updateRole($packet->getRole());
		$this->plugin->getServer()->broadcastMessage("Role '".$packet->getRole()->getName()."' updated.");
		return true;
	}

	private function handleRoleDelete(DiscordEventRoleDelete $packet): bool{
		Storage::removeRole($packet->getRole()->getId());
		$this->plugin->getServer()->broadcastMessage("Role '".$packet->getRole()->getName()."' deleted.");
		return true;
	}

	private function handleMemberJoin(DiscordEventMemberJoin $packet): bool{
		$config = $this->plugin->getEventsConfig()['member_join']['fromDiscord'];
		if(($config['format'] ?? "") === "") return true;

		/** @var Server $server */
		$server = Storage::getServer($packet->getMember()->getServerId());
		Utils::assert($server instanceof Server);

		if(!in_array($server->getId(), $config['servers'])) return true;

		$member = $packet->getMember();
		$user = $packet->getUser();

		Storage::addMember($member);
		Storage::addUser($user);

		$formatted = str_replace(
			['{TIME}', '{USER_ID}', '{USERNAME}', '{USER_DISCRIMINATOR}', '{SERVER_ID}', '{SERVER_NAME}'],
			[date('G:i:s', $member->getJoinTimestamp()), $member->getId(), $user->getUsername(),
				$user->getDiscriminator(), $server->getId(), $server->getName()], $config['format']);

		$this->plugin->getServer()->broadcastMessage($formatted);
		return true;
	}

	private function handleMemberLeave(DiscordEventMemberLeave $packet): bool{
		$config = $this->plugin->getEventsConfig()['member_leave']['fromDiscord'];
		if(($config['format'] ?? "") === "") return true;

		/** @var Member $member */
		$member = Storage::getMember($packet->getMemberID());
		Utils::assert($member instanceof Member);

		/** @var Server $server */
		$server = Storage::getServer($member->getServerId());
		Utils::assert($server instanceof Server);

		//Have to fetch member first because onLeave we dont have their data direct from discord, so use cache :)
		if(!in_array($server->getId(), $config['servers'])) return true;

		/** @var User $user */
		$user = Storage::getUser($member->getUserId());
		Utils::assert($user instanceof User);

		Storage::removeMember($member->getId());

		$formatted = str_replace(
			['{TIME}', '{USER_ID}', '{USERNAME}', '{USER_DISCRIMINATOR}', '{SERVER_ID}', '{SERVER_NAME}'],
			[date('G:i:s', $member->getJoinTimestamp()), $user->getId(), $user->getUsername(),
				$user->getDiscriminator(), $server->getId(), $server->getName()], $config['format']);

		$this->plugin->getServer()->broadcastMessage($formatted);
		return true;
	}

	private function handleMemberUpdate(DiscordEventMemberUpdate $packet): bool{
		Storage::updateMember($packet->getMember());
		$this->plugin->getServer()->broadcastMessage("Member updated.");
		return true;
	}

	private function handleServerJoin(DiscordEventServerJoin $packet): bool{
		Storage::addServer($packet->getServer());
		foreach($packet->getMembers() as $member) Storage::addMember($member);
		foreach($packet->getRoles() as $role) Storage::addRole($role);
		foreach($packet->getChannels() as $channel) Storage::addChannel($channel);
		$this->plugin->getServer()->broadcastMessage("Joined discord server: ".$packet->getServer()->getName());
		return true;
	}

	private function handleServerLeave(DiscordEventServerLeave $packet): bool{
		Storage::removeServer($packet->getServer()->getId());
		$this->plugin->getServer()->broadcastMessage("Removed/Left discord server: ".$packet->getServer()->getName());
		return true;
	}

	private function handleServerUpdate(DiscordEventServerUpdate $packet): bool{
		Storage::updateServer($packet->getServer());
		$this->plugin->getServer()->broadcastMessage("Updated discord server: ".$packet->getServer()->getName());
		return true;
	}

	private function handleAllDiscordData(DiscordEventAllData $packet): bool{
		//Todo verify packet before resetting data.
		Storage::reset();
		foreach($packet->getServers() as $server){
			Storage::addServer($server);
		}
		foreach($packet->getChannels() as $channel){
			Storage::addChannel($channel);
		}
		foreach($packet->getRoles() as $role){
			Storage::addRole($role);
		}
		foreach($packet->getMembers() as $member){
			Storage::addMember($member);
		}
		foreach($packet->getUsers() as $user){
			Storage::addUser($user);
		}
		if($packet->getBotUser() !== null) Storage::setBotUser($packet->getBotUser());
		Storage::setTimestamp($packet->getTimestamp());

		return true;
	}

	/**
	 * Checks last KNOWN Heartbeat timestamp with current time, does not check pre-start condition.
	 */
	public function checkHeartbeat(): void{
		if($this->lastHeartbeat === null) return;
		if(($diff = microtime(true) - $this->lastHeartbeat) > Protocol::HEARTBEAT_ALLOWANCE){
			MainLogger::getLogger()->emergency("DiscordBot has not responded for 2 seconds, disabling plugin + bot.");
			$this->plugin->stopAll();
		}
	}

	public function sendHeartbeat(): void{
		$p = new Heartbeat();
		$p->setHeartbeat(microtime(true));
		$this->plugin->writeOutboundData($p);
	}

	public function getLastHeartbeat(): ?float{
		return $this->lastHeartbeat;
	}
}