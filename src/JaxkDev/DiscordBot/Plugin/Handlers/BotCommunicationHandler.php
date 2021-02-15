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

use JaxkDev\DiscordBot\Communication\Models\Activity;
use JaxkDev\DiscordBot\Communication\Models\Channel;
use JaxkDev\DiscordBot\Communication\Models\Member;
use JaxkDev\DiscordBot\Communication\Models\Server;
use JaxkDev\DiscordBot\Communication\Models\User;
use JaxkDev\DiscordBot\Communication\Packets\Resolution;
use JaxkDev\DiscordBot\Communication\Packets\Discord\DiscordDataDump;
use JaxkDev\DiscordBot\Communication\Packets\Discord\DiscordEventBanAdd;
use JaxkDev\DiscordBot\Communication\Packets\Discord\DiscordEventBanRemove;
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
use JaxkDev\DiscordBot\Communication\Packets\Discord\DiscordEventReady;
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
		if($packet instanceof DiscordEventInviteCreate) return $this->handleInviteCreate($packet);
		if($packet instanceof DiscordEventInviteDelete) return $this->handleInviteDelete($packet);
		if($packet instanceof DiscordEventBanAdd) return $this->handleBanAdd($packet);
		if($packet instanceof DiscordEventBanRemove) return $this->handleBanRemove($packet);
		if($packet instanceof DiscordEventServerJoin) return $this->handleServerJoin($packet);
		if($packet instanceof DiscordEventServerLeave) return $this->handleServerLeave($packet);
		if($packet instanceof DiscordEventServerUpdate) return $this->handleServerUpdate($packet);
		if($packet instanceof DiscordDataDump) return $this->handleDataDump($packet);
		if($packet instanceof DiscordEventReady) return $this->handleReady();
		return false;
	}

	private function handleReady(): bool{
		(new DiscordReady($this->plugin))->call();
		//TODO Implement into Main's tick to verify bot is ready for heartbeats etc.

		//TEST ACK resolving...
		$act = $this->plugin->getApi()->createActivity(Activity::STATUS_DND, Activity::TYPE_PLAYING, "TestAck");
		//Should resolve.
		$this->plugin->getApi()->updateActivity($act)->done(function($v){
			var_dump("Resolved norm");
			var_dump($v);
		}, function($v){
			var_dump("Rejected norm");
			var_dump($v);
		});
		$c = new Channel();
		$c->setId("2313213");
		$c->setServerId("23424324");
		$c->setName("channel test doesnt exist.");
		$msg = $this->plugin->getApi()->createMessage($c, "Content !");
		if($msg !== null){
			//Should reject, with server not found.
			$this->plugin->getApi()->sendMessage($msg)->done(function($v){
				var_dump("Resolved msg");
				var_dump($v);
			}, function($v){
				var_dump("Rejected msg");
				var_dump($v);
			});
		}
		return true;
	}

	private function handleMessageSent(DiscordEventMessageSent $packet): bool{
		$config = $this->plugin->getEventsConfig()["message"]["fromDiscord"];
		$message = $packet->getMessage();

		if(!in_array($message->getChannelId(), $config["channels"])) return true;

		//If any of these asserts fire theres a mismatch between Storage and discord.

		/** @var Server $server */
		$server = Storage::getServer($message->getServerId());
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
				$user->getDiscriminator(), $server->getId(), $server->getName(), $channel->getId(), $channel->getName(),
				$message->getContent()
			],
			$config["format"]);

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
		$e = new DiscordChannelUpdated($this->plugin, $packet->getChannel());
		$e->call();
		if($e->isCancelled()) return true;
		Storage::addChannel($packet->getChannel());
		$this->plugin->getServer()->broadcastMessage("Channel '".$packet->getChannel()->getName()."' created.");
		return true;
	}

	private function handleChannelUpdate(DiscordEventChannelUpdate $packet): bool{
		$e = new DiscordChannelUpdated($this->plugin, $packet->getChannel());
		$e->call();
		if($e->isCancelled()) return true;
		Storage::updateChannel($packet->getChannel());
		$this->plugin->getServer()->broadcastMessage("Channel '".$packet->getChannel()->getName()."' updated.");
		return true;
	}

	private function handleChannelDelete(DiscordEventChannelDelete $packet): bool{
		$channel = Storage::getChannel($packet->getChannelId());
		if($channel === null) return false;
		$e = new DiscordChannelDeleted($this->plugin, $channel);
		$e->call();
		if($e->isCancelled()) return true;
		$this->plugin->getServer()->broadcastMessage("Channel '".$channel->getName()."' deleted.");
		Storage::removeChannel($packet->getChannelId());
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
		$role = Storage::getRole($packet->getRoleId());
		if($role === null) return false;
		$this->plugin->getServer()->broadcastMessage("Role '".$role->getName()."' deleted.");
		Storage::removeRole($packet->getRoleId());
		return true;
	}

	private function handleInviteCreate(DiscordEventInviteCreate $packet): bool{
		Storage::addInvite($packet->getInvite());
		$this->plugin->getServer()->broadcastMessage("Invite '".$packet->getInvite()->getCode()."' created.");
		return true;
	}

	private function handleInviteDelete(DiscordEventInviteDelete $packet): bool{
		Storage::removeInvite($packet->getInviteCode());
		$this->plugin->getServer()->broadcastMessage("Invite '".$packet->getInviteCode()."' deleted/expired.");
		return true;
	}

	private function handleBanAdd(DiscordEventBanAdd $packet): bool{
		Storage::addBan($packet->getBan());
		$this->plugin->getServer()->broadcastMessage("Ban '".$packet->getBan()->getId()."' has been added.");
		return true;
	}

	private function handleBanRemove(DiscordEventBanRemove $packet): bool{
		Storage::removeBan($packet->getId());
		$this->plugin->getServer()->broadcastMessage("Ban '".$packet->getId()."' has been removed.");
		return true;
	}

	private function handleMemberJoin(DiscordEventMemberJoin $packet): bool{
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

	private function handleMemberUpdate(DiscordEventMemberUpdate $packet): bool{
		Storage::updateMember($packet->getMember());
		$this->plugin->getServer()->broadcastMessage("Member updated.");
		return true;
	}

	private function handleMemberLeave(DiscordEventMemberLeave $packet): bool{
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

	private function handleServerJoin(DiscordEventServerJoin $packet): bool{
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

	private function handleServerUpdate(DiscordEventServerUpdate $packet): bool{
		$e = new DiscordServerUpdated($this->plugin, $packet->getServer());
		$e->call();
		if($e->isCancelled()) return true;
		Storage::updateServer($packet->getServer());
		$this->plugin->getServer()->broadcastMessage("Updated discord server: ".$packet->getServer()->getName());
		return true;
	}

	private function handleServerLeave(DiscordEventServerLeave $packet): bool{
		$server = Storage::getServer($packet->getServerId());
		if($server === null) return false;
		$e = new DiscordServerDeleted($this->plugin, $server);
		$e->call();
		if($e->isCancelled()) return true;
		$this->plugin->getServer()->broadcastMessage("Deleted/Removed/Left discord server: ".$server->getName());
		Storage::removeServer($packet->getServerId());
		return true;
	}

	private function handleDataDump(DiscordDataDump $packet): bool{
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
			$this->plugin->getLogger()->emergency("DiscordBot has not responded for ".
				Protocol::HEARTBEAT_ALLOWANCE." seconds, disabling plugin + bot.");
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