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

use JaxkDev\DiscordBot\Models\Channels\Channel;
use JaxkDev\DiscordBot\Models\Channels\ServerChannel;
use JaxkDev\DiscordBot\Models\Member;
use JaxkDev\DiscordBot\Models\Server;
use JaxkDev\DiscordBot\Models\User;
use JaxkDev\DiscordBot\Communication\Packets\Resolution;
use JaxkDev\DiscordBot\Communication\Packets\Discord\DataDump;
use JaxkDev\DiscordBot\Communication\Packets\Discord\EventBanAdd;
use JaxkDev\DiscordBot\Communication\Packets\Discord\EventBanRemove;
use JaxkDev\DiscordBot\Communication\Packets\Discord\EventChannelCreate;
use JaxkDev\DiscordBot\Communication\Packets\Discord\EventChannelDelete;
use JaxkDev\DiscordBot\Communication\Packets\Discord\EventChannelUpdate;
use JaxkDev\DiscordBot\Communication\Packets\Discord\EventInviteCreate;
use JaxkDev\DiscordBot\Communication\Packets\Discord\EventInviteDelete;
use JaxkDev\DiscordBot\Communication\Packets\Discord\EventMemberJoin;
use JaxkDev\DiscordBot\Communication\Packets\Discord\EventMemberLeave;
use JaxkDev\DiscordBot\Communication\Packets\Discord\EventMemberUpdate;
use JaxkDev\DiscordBot\Communication\Packets\Discord\EventMessageDelete;
use JaxkDev\DiscordBot\Communication\Packets\Discord\EventMessageSent;
use JaxkDev\DiscordBot\Communication\Packets\Discord\EventMessageUpdate;
use JaxkDev\DiscordBot\Communication\Packets\Discord\EventRoleCreate;
use JaxkDev\DiscordBot\Communication\Packets\Discord\EventRoleDelete;
use JaxkDev\DiscordBot\Communication\Packets\Discord\EventRoleUpdate;
use JaxkDev\DiscordBot\Communication\Packets\Discord\EventServerJoin;
use JaxkDev\DiscordBot\Communication\Packets\Discord\EventServerLeave;
use JaxkDev\DiscordBot\Communication\Packets\Discord\EventServerUpdate;
use JaxkDev\DiscordBot\Communication\Packets\Discord\EventReady;
use JaxkDev\DiscordBot\Communication\Packets\Heartbeat;
use JaxkDev\DiscordBot\Communication\Packets\Packet;
use JaxkDev\DiscordBot\Communication\Protocol;
use JaxkDev\DiscordBot\Plugin\ApiResolver;
use JaxkDev\DiscordBot\Plugin\Events\DiscordChannelDeleted;
use JaxkDev\DiscordBot\Plugin\Events\DiscordChannelUpdated;
use JaxkDev\DiscordBot\Plugin\Events\DiscordReady;
use JaxkDev\DiscordBot\Plugin\Events\DiscordServerDeleted;
use JaxkDev\DiscordBot\Plugin\Events\DiscordServerJoined;
use JaxkDev\DiscordBot\Plugin\Events\DiscordServerUpdated;
use JaxkDev\DiscordBot\Plugin\Main;
use JaxkDev\DiscordBot\Plugin\Storage;

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
		if($packet instanceof Resolution){
			ApiResolver::handleResolution($packet);
			return true;
		}
		if($packet instanceof Heartbeat){
			$this->lastHeartbeat = $packet->getHeartbeat();
			return true;
		}

		if($packet instanceof EventMemberJoin) return $this->handleMemberJoin($packet);
		if($packet instanceof EventMemberLeave) return $this->handleMemberLeave($packet);
		if($packet instanceof EventMemberUpdate) return $this->handleMemberUpdate($packet);
		if($packet instanceof EventMessageSent) return $this->handleMessageSent($packet);
		if($packet instanceof EventMessageUpdate) return $this->handleMessageUpdate($packet);
		if($packet instanceof EventMessageDelete) return $this->handleMessageDelete($packet);
		if($packet instanceof EventChannelCreate) return $this->handleChannelCreate($packet);
		if($packet instanceof EventChannelUpdate) return $this->handleChannelUpdate($packet);
		if($packet instanceof EventChannelDelete) return $this->handleChannelDelete($packet);
		if($packet instanceof EventRoleCreate) return $this->handleRoleCreate($packet);
		if($packet instanceof EventRoleUpdate) return $this->handleRoleUpdate($packet);
		if($packet instanceof EventRoleDelete) return $this->handleRoleDelete($packet);
		if($packet instanceof EventInviteCreate) return $this->handleInviteCreate($packet);
		if($packet instanceof EventInviteDelete) return $this->handleInviteDelete($packet);
		if($packet instanceof EventBanAdd) return $this->handleBanAdd($packet);
		if($packet instanceof EventBanRemove) return $this->handleBanRemove($packet);
		if($packet instanceof EventServerJoin) return $this->handleServerJoin($packet);
		if($packet instanceof EventServerLeave) return $this->handleServerLeave($packet);
		if($packet instanceof EventServerUpdate) return $this->handleServerUpdate($packet);
		if($packet instanceof DataDump) return $this->handleDataDump($packet);
		if($packet instanceof EventReady) return $this->handleReady();
		return false;
	}

	private function handleReady(): bool{
		(new DiscordReady($this->plugin))->call();
		return true;
	}

	private function handleMessageSent(EventMessageSent $packet): bool{
		$config = $this->plugin->getEventsConfig()["message"]["fromDiscord"];
		$message = $packet->getMessage();

		if(!in_array($message->getChannelId(), $config["channels"])) return true;

		//If any of these asserts fire theres a mismatch between Storage and discord.

		/** @var Server $server */
		$server = Storage::getServer($message->getServerId()??"");
		if(!$server instanceof Server){
			throw new \AssertionError("Server '{$message->getServerId()}' not found in storage.");
		}

		/** @var Channel $channel */
		$channel = Storage::getChannel($message->getChannelId());
		if(!$channel instanceof Channel){
			throw new \AssertionError("Channel '{$message->getChannelId()}' not found in storage.");
		}

		/** @var Member $author */
		$author = Storage::getMember($message->getAuthorId()??"");
		if(!$author instanceof Member){
			throw new \AssertionError("Member '{$message->getAuthorId()}' not found in storage.");
		}

		/** @var User $user */
		$user = Storage::getUser($author->getUserId());
		if(!$user instanceof User){
			throw new \AssertionError("User '{$author->getUserId()}' not found in storage.");
		}

		$formatted = str_replace(["{TIME}", "{USER_ID}", "{USERNAME}", "{USER_DISCRIMINATOR}", "{SERVER_ID}",
			"{SERVER_NAME}", "{CHANNEL_ID}", "{CHANNEL_NAME}", "{MESSAGE}"], [
				date("G:i:s", (int)$message->getTimestamp()??0), $author->getUserId(), $user->getUsername(),
				$user->getDiscriminator(), $server->getId(), $server->getName(), $channel->getId(),
				(($channel instanceof ServerChannel) ? $channel->getName() : $channel->getId()), $message->getContent()
			],
			$config["format"]);

		$this->plugin->getServer()->broadcastMessage($formatted);
		return true;
	}

	private function handleMessageUpdate(EventMessageUpdate $packet): bool{
		return false;
	}

	private function handleMessageDelete(EventMessageDelete $packet): bool{
		return false;
	}

	private function handleChannelCreate(EventChannelCreate $packet): bool{
		$c = $packet->getChannel();
		$e = new DiscordChannelUpdated($this->plugin, $c);
		$e->call();
		if($e->isCancelled()) return true;
		Storage::addChannel($c);
		$this->plugin->getServer()->broadcastMessage("Channel '".$c->getName()."' created.");
		return true;
	}

	private function handleChannelUpdate(EventChannelUpdate $packet): bool{
		$c = $packet->getChannel();
		$e = new DiscordChannelUpdated($this->plugin, $c);
		$e->call();
		if($e->isCancelled()) return true;
		Storage::updateChannel($c);
		$this->plugin->getServer()->broadcastMessage("Channel '".$c->getName()."' updated.");
		return true;
	}

	private function handleChannelDelete(EventChannelDelete $packet): bool{
		$c = Storage::getChannel($packet->getChannelId());
		if($c === null) return false;
		$e = new DiscordChannelDeleted($this->plugin, $c);
		$e->call();
		if($e->isCancelled()) return true;
		Storage::removeChannel($c->getId());
		$this->plugin->getServer()->broadcastMessage("Channel '".$c->getName()."' deleted.");
		return true;
	}

	private function handleRoleCreate(EventRoleCreate $packet): bool{
		Storage::addRole($packet->getRole());
		$this->plugin->getServer()->broadcastMessage("Role '".$packet->getRole()->getName()."' created.");
		return true;
	}

	private function handleRoleUpdate(EventRoleUpdate $packet): bool{
		Storage::updateRole($packet->getRole());
		$this->plugin->getServer()->broadcastMessage("Role '".$packet->getRole()->getName()."' updated.");
		return true;
	}

	private function handleRoleDelete(EventRoleDelete $packet): bool{
		$role = Storage::getRole($packet->getRoleId());
		if($role === null) return false;
		$this->plugin->getServer()->broadcastMessage("Role '".$role->getName()."' deleted.");
		Storage::removeRole($packet->getRoleId());
		return true;
	}

	private function handleInviteCreate(EventInviteCreate $packet): bool{
		Storage::addInvite($packet->getInvite());
		$this->plugin->getServer()->broadcastMessage("Invite '".$packet->getInvite()->getCode()."' created.");
		return true;
	}

	private function handleInviteDelete(EventInviteDelete $packet): bool{
		Storage::removeInvite($packet->getInviteCode());
		$this->plugin->getServer()->broadcastMessage("Invite '".$packet->getInviteCode()."' deleted/expired.");
		return true;
	}

	private function handleBanAdd(EventBanAdd $packet): bool{
		Storage::addBan($packet->getBan());
		$this->plugin->getServer()->broadcastMessage("Ban '".$packet->getBan()->getId()."' has been added.");
		return true;
	}

	private function handleBanRemove(EventBanRemove $packet): bool{
		Storage::removeBan($packet->getId());
		$this->plugin->getServer()->broadcastMessage("Ban '".$packet->getId()."' has been removed.");
		return true;
	}

	private function handleMemberJoin(EventMemberJoin $packet): bool{
		$config = $this->plugin->getEventsConfig()["member_join"]["fromDiscord"];
		if(($config["format"] ?? "") === "") return true;

		/** @var Server $server */
		$server = Storage::getServer($packet->getMember()->getServerId());
		if(!$server instanceof Server){
			throw new \AssertionError("Server '{$packet->getMember()->getServerId()}' not found for member '{$packet->getMember()->getId()}'");
		}

		if(!in_array($server->getId(), $config["servers"])) return true;

		$member = $packet->getMember();
		$user = $packet->getUser();

		Storage::addMember($member);
		Storage::addUser($user);

		$formatted = str_replace(
			["{TIME}", "{USER_ID}", "{USERNAME}", "{USER_DISCRIMINATOR}", "{SERVER_ID}", "{SERVER_NAME}"],
			[date("G:i:s", $member->getJoinTimestamp()), $member->getId(), $user->getUsername(),
				$user->getDiscriminator(), $server->getId(), $server->getName()], $config["format"]);

		$this->plugin->getServer()->broadcastMessage($formatted);
		return true;
	}

	private function handleMemberUpdate(EventMemberUpdate $packet): bool{
		Storage::updateMember($packet->getMember());
		$this->plugin->getServer()->broadcastMessage("Member updated.");
		return true;
	}

	private function handleMemberLeave(EventMemberLeave $packet): bool{
		$config = $this->plugin->getEventsConfig()["member_leave"]["fromDiscord"];
		if(($config["format"] ?? "") === "") return true;

		/** @var Member $member */
		$member = Storage::getMember($packet->getMemberID());
		if(!$member instanceof Member){
			throw new \AssertionError("Member '{$packet->getMemberID()}' not found in storage.");
		}

		/** @var Server $server */
		$server = Storage::getServer($member->getServerId());
		if(!$server instanceof Server){
			throw new \AssertionError("Server '{$member->getServerId()}' not found for member '{$member->getId()}'");
		}

		//Have to fetch member first because onLeave we dont have their data direct from discord, so use cache :)
		if(!in_array($server->getId(), $config["servers"])) return true;

		/** @var User $user */
		$user = Storage::getUser($member->getUserId());
		if(!$user instanceof User){
			throw new \AssertionError("User '{$member->getUserId()}' not found in storage.");
		}

		Storage::removeMember($member->getId());

		$formatted = str_replace(
			["{TIME}", "{USER_ID}", "{USERNAME}", "{USER_DISCRIMINATOR}", "{SERVER_ID}", "{SERVER_NAME}"],
			[date("G:i:s", $member->getJoinTimestamp()), $user->getId(), $user->getUsername(),
				$user->getDiscriminator(), $server->getId(), $server->getName()], $config["format"]);

		$this->plugin->getServer()->broadcastMessage($formatted);
		return true;
	}

	private function handleServerJoin(EventServerJoin $packet): bool{
		$e = new DiscordServerJoined($this->plugin, $packet->getServer(), $packet->getRoles(),
			$packet->getChannels(), $packet->getMembers());
		$e->call();
		if($e->isCancelled()) return true;

		Storage::addServer($packet->getServer());
		foreach($packet->getMembers() as $member){
			Storage::addMember($member);
		}
		foreach($packet->getRoles() as $role){
			Storage::addRole($role);
		}
		foreach($packet->getChannels() as $channel){
			Storage::addChannel($channel);
		}
		$this->plugin->getServer()->broadcastMessage("Joined discord server: ".$packet->getServer()->getName());
		return true;
	}

	private function handleServerUpdate(EventServerUpdate $packet): bool{
		$e = new DiscordServerUpdated($this->plugin, $packet->getServer());
		$e->call();
		if($e->isCancelled()) return true;
		Storage::updateServer($packet->getServer());
		$this->plugin->getServer()->broadcastMessage("Updated discord server: ".$packet->getServer()->getName());
		return true;
	}

	private function handleServerLeave(EventServerLeave $packet): bool{
		$server = Storage::getServer($packet->getServerId());
		if($server === null) return false;
		$e = new DiscordServerDeleted($this->plugin, $server);
		$e->call();
		if($e->isCancelled()) return true;
		$this->plugin->getServer()->broadcastMessage("Deleted/Removed/Left discord server: ".$server->getName());
		Storage::removeServer($packet->getServerId());
		return true;
	}

	private function handleDataDump(DataDump $packet): bool{
		foreach($packet->getServers() as $server){
			Storage::addServer($server);
		}
		foreach($packet->getChannels() as $channel){
			Storage::addChannel($channel);
		}
		foreach($packet->getRoles() as $role){
			Storage::addRole($role);
		}
		foreach($packet->getBans() as $ban){
			Storage::addBan($ban);
		}
		foreach($packet->getInvites() as $invite){
			Storage::addInvite($invite);
		}
		foreach($packet->getMembers() as $member){
			Storage::addMember($member);
		}
		foreach($packet->getUsers() as $user){
			Storage::addUser($user);
		}
		if($packet->getBotUser() !== null){
			Storage::setBotUser($packet->getBotUser());
		}
		Storage::setTimestamp($packet->getTimestamp());
		$this->plugin->getLogger()->debug("Handled data dump (".$packet->getTimestamp().") (".$packet->getSize().")");
		return true;
	}

	/**
	 * Checks last KNOWN Heartbeat timestamp with current time, does not check pre-start condition.
	 */
	public function checkHeartbeat(): void{
		if($this->lastHeartbeat === null) return;
		if(($diff = microtime(true) - $this->lastHeartbeat) > Protocol::HEARTBEAT_ALLOWANCE){
			$this->plugin->getLogger()->emergency("DiscordBot has not responded for {$diff} seconds, disabling plugin.");
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