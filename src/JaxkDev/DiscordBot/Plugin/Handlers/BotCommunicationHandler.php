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

use JaxkDev\DiscordBot\Models\Activity;
use JaxkDev\DiscordBot\Models\Channels\Channel;
use JaxkDev\DiscordBot\Models\Channels\ServerChannel;
use JaxkDev\DiscordBot\Models\Embed\Author;
use JaxkDev\DiscordBot\Models\Embed\Embed;
use JaxkDev\DiscordBot\Models\Embed\Field;
use JaxkDev\DiscordBot\Models\Embed\Footer;
use JaxkDev\DiscordBot\Models\Member;
use JaxkDev\DiscordBot\Models\Messages\Message;
use JaxkDev\DiscordBot\Models\Messages\Webhook;
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
use JaxkDev\DiscordBot\Plugin\ApiRejection;
use JaxkDev\DiscordBot\Plugin\ApiResolver;
use JaxkDev\DiscordBot\Plugin\Events\DiscordChannelDeleted;
use JaxkDev\DiscordBot\Plugin\Events\DiscordChannelUpdated;
use JaxkDev\DiscordBot\Plugin\Events\DiscordMessageDeleted;
use JaxkDev\DiscordBot\Plugin\Events\DiscordMessageSent;
use JaxkDev\DiscordBot\Plugin\Events\DiscordMessageUpdated;
use JaxkDev\DiscordBot\Plugin\Events\DiscordReady;
use JaxkDev\DiscordBot\Plugin\Events\DiscordServerDeleted;
use JaxkDev\DiscordBot\Plugin\Events\DiscordServerJoined;
use JaxkDev\DiscordBot\Plugin\Events\DiscordServerUpdated;
use JaxkDev\DiscordBot\Plugin\Main;
use JaxkDev\DiscordBot\Plugin\Storage;
use pocketmine\utils\MainLogger;

class BotCommunicationHandler{

	/** @var Main */
	private $plugin;

	/** @var float|null */
	private $lastHeartbeat = null;

	public function __construct(Main $plugin){
		$this->plugin = $plugin;
	}

	public function handle(Packet $packet): void{
		// If's instances instead of ID switching due to phpstan/types.
		if($packet instanceof Resolution){
			ApiResolver::handleResolution($packet);
			return;
		}
		if($packet instanceof Heartbeat){
			$this->lastHeartbeat = $packet->getHeartbeat();
			return;
		}

		if($packet instanceof EventMemberJoin) $this->handleMemberJoin($packet);
		elseif($packet instanceof EventMemberLeave) $this->handleMemberLeave($packet);
		elseif($packet instanceof EventMemberUpdate) $this->handleMemberUpdate($packet);
		elseif($packet instanceof EventMessageSent) $this->handleMessageSent($packet);
		elseif($packet instanceof EventMessageUpdate) $this->handleMessageUpdate($packet);
		elseif($packet instanceof EventMessageDelete) $this->handleMessageDelete($packet);
		elseif($packet instanceof EventChannelCreate) $this->handleChannelCreate($packet);
		elseif($packet instanceof EventChannelUpdate) $this->handleChannelUpdate($packet);
		elseif($packet instanceof EventChannelDelete) $this->handleChannelDelete($packet);
		elseif($packet instanceof EventRoleCreate) $this->handleRoleCreate($packet);
		elseif($packet instanceof EventRoleUpdate) $this->handleRoleUpdate($packet);
		elseif($packet instanceof EventRoleDelete) $this->handleRoleDelete($packet);
		elseif($packet instanceof EventInviteCreate) $this->handleInviteCreate($packet);
		elseif($packet instanceof EventInviteDelete) $this->handleInviteDelete($packet);
		elseif($packet instanceof EventBanAdd) $this->handleBanAdd($packet);
		elseif($packet instanceof EventBanRemove) $this->handleBanRemove($packet);
		elseif($packet instanceof EventServerJoin) $this->handleServerJoin($packet);
		elseif($packet instanceof EventServerLeave) $this->handleServerLeave($packet);
		elseif($packet instanceof EventServerUpdate) $this->handleServerUpdate($packet);
		elseif($packet instanceof DataDump) $this->handleDataDump($packet);
		elseif($packet instanceof EventReady) $this->handleReady();
	}

	private function handleReady(): void{
		//Default activity, Feel free to change activity after ReadyEvent.
		$ac = new Activity();
		$ac->setMessage("PocketMine-MP v".\pocketmine\VERSION." | DiscordBot ".\JaxkDev\DiscordBot\VERSION);
		$ac->setType(Activity::TYPE_PLAYING);
		$ac->setStatus(Activity::STATUS_IDLE);
		$this->plugin->getApi()->updateActivity($ac)->otherwise(function(ApiRejection $a){
			MainLogger::getLogger()->logException($a);
		});

		$m = new Message();
		$m->setChannelId("778914498609283102");
		$e = new Embed();
		//$e->setTimestamp(time());
		$e->setTitle("Server Started");
		//$e->setDescription("Server Started");
		//$e->setType(Embed::TYPE_RICH);
		//$e->setUrl("https://test.url");
		$e->setColour(0x10ff10);
		//$f = new Footer();
		//$f->setText("Test Footer");
		//$f->setIconUrl("https://test.footer.icon");
		//$e->setFooter($f);
		//$a = new Author();
		//$a->setName("Test Author");
		//$a->setUrl("https://test.author");
		//$a->setIconUrl("https://test.author.icon");
		//$e->setAuthor($a);
		$f = [];
		for($i = 0; $i < 5; $i++){
			$ff = new Field();
			$ff->setName("Test Field $i");
			$ff->setValue("Test Field Value $i");
			//$f[] = $ff;
		}
		$e->setFields($f);
		$m->setEmbed($e);
		$this->plugin->getApi()->sendMessage($m)->otherwise(function(ApiRejection $d){
			MainLogger::getLogger()->logException($d);
		});

		(new DiscordReady($this->plugin))->call();
	}

	private function handleMessageSent(EventMessageSent $packet): void{
		$message = $packet->getMessage();

		$e = new DiscordMessageSent($this->plugin, $message);
		$e->call();
		if($e->isCancelled()) return;
		if($message instanceof Webhook or trim($message->getContent()) === "") return;

		$config = $this->plugin->getEventsConfig()["message"]["fromDiscord"];
		if(!in_array($message->getChannelId(), $config["channels"])) return;

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
	}

	private function handleMessageUpdate(EventMessageUpdate $packet): void{
		(new DiscordMessageUpdated($this->plugin, $packet->getMessage()))->call();
	}

	private function handleMessageDelete(EventMessageDelete $packet): void{
		(new DiscordMessageDeleted($this->plugin, $packet->getMessageId()))->call();
		return;
	}

	private function handleChannelCreate(EventChannelCreate $packet): void{
		$c = $packet->getChannel();
		$e = new DiscordChannelUpdated($this->plugin, $c);
		$e->call();
		Storage::addChannel($c);
		if(!$e->isCancelled()) return;
	}

	private function handleChannelUpdate(EventChannelUpdate $packet): void{
		$c = $packet->getChannel();
		$e = new DiscordChannelUpdated($this->plugin, $c);
		$e->call();
		Storage::updateChannel($c);
		if($e->isCancelled()) return;
	}

	private function handleChannelDelete(EventChannelDelete $packet): void{
		$c = Storage::getChannel($packet->getChannelId());
		if($c === null) return;
		$e = new DiscordChannelDeleted($this->plugin, $c);
		$e->call();
		Storage::removeChannel($c->getId());
		if($e->isCancelled()) return;
	}

	private function handleRoleCreate(EventRoleCreate $packet): void{
		//TODO Event
		Storage::addRole($packet->getRole());
	}

	private function handleRoleUpdate(EventRoleUpdate $packet): void{
		//TODO Event
		Storage::updateRole($packet->getRole());
	}

	private function handleRoleDelete(EventRoleDelete $packet): void{
		//TODO Event
		$role = Storage::getRole($packet->getRoleId());
		if($role === null) return;
		Storage::removeRole($packet->getRoleId());
	}

	private function handleInviteCreate(EventInviteCreate $packet): void{
		//TODO Event
		Storage::addInvite($packet->getInvite());
	}

	private function handleInviteDelete(EventInviteDelete $packet): void{
		//TODO Event
		Storage::removeInvite($packet->getInviteCode());
	}

	private function handleBanAdd(EventBanAdd $packet): void{
		//TODO Event
		Storage::addBan($packet->getBan());
	}

	private function handleBanRemove(EventBanRemove $packet): void{
		//TODO Event
		Storage::removeBan($packet->getId());
	}

	private function handleMemberJoin(EventMemberJoin $packet): void{
		//TODO Event
		$config = $this->plugin->getEventsConfig()["member_join"]["fromDiscord"];
		if(($config["format"] ?? "") === "") return;

		/** @var Server $server */
		$server = Storage::getServer($packet->getMember()->getServerId());
		if(!$server instanceof Server){
			throw new \AssertionError("Server '{$packet->getMember()->getServerId()}' not found for member '{$packet->getMember()->getId()}'");
		}

		if(!in_array($server->getId(), $config["servers"])) return;

		$member = $packet->getMember();
		$user = $packet->getUser();

		Storage::addMember($member);
		Storage::addUser($user);

		$formatted = str_replace(
			["{TIME}", "{USER_ID}", "{USERNAME}", "{USER_DISCRIMINATOR}", "{SERVER_ID}", "{SERVER_NAME}"],
			[date("G:i:s", $member->getJoinTimestamp()), $member->getId(), $user->getUsername(),
				$user->getDiscriminator(), $server->getId(), $server->getName()], $config["format"]);

		$this->plugin->getServer()->broadcastMessage($formatted);
	}

	private function handleMemberUpdate(EventMemberUpdate $packet): void{
		//TODO Event
		Storage::updateMember($packet->getMember());
	}

	private function handleMemberLeave(EventMemberLeave $packet): void{
		//TODO Event
		$config = $this->plugin->getEventsConfig()["member_leave"]["fromDiscord"];
		if(($config["format"] ?? "") === "") return;

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
		if(!in_array($server->getId(), $config["servers"])) return;

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
	}

	private function handleServerJoin(EventServerJoin $packet): void{
		$e = new DiscordServerJoined($this->plugin, $packet->getServer(), $packet->getRoles(),
			$packet->getChannels(), $packet->getMembers());
		$e->call();
		if($e->isCancelled()) return;

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
	}

	private function handleServerUpdate(EventServerUpdate $packet): void{
		$e = new DiscordServerUpdated($this->plugin, $packet->getServer());
		$e->call();
		if($e->isCancelled()) return;
		Storage::updateServer($packet->getServer());
	}

	private function handleServerLeave(EventServerLeave $packet): void{
		$server = Storage::getServer($packet->getServerId());
		if($server === null) return;
		$e = new DiscordServerDeleted($this->plugin, $server);
		$e->call();
		if($e->isCancelled()) return;
		Storage::removeServer($packet->getServerId());
	}

	private function handleDataDump(DataDump $packet): void{
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