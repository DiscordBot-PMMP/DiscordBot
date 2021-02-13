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

use Discord\Discord;
use Discord\Parts\Channel\Channel as DiscordChannel;
use Discord\Parts\Channel\Message as DiscordMessage;
use Discord\Parts\Guild\Ban as DiscordBan;
use Discord\Parts\Guild\Guild as DiscordGuild;
use Discord\Parts\Guild\Invite as DiscordInvite;
use Discord\Parts\Guild\Role as DiscordRole;
use Discord\Parts\Permissions\RolePermission;
use Discord\Parts\User\Member as DiscordMember;
use Discord\Parts\User\User as DiscordUser;
use JaxkDev\DiscordBot\Bot\Client;
use JaxkDev\DiscordBot\Bot\ModelConverter;
use JaxkDev\DiscordBot\Communication\Models\Activity;
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
use JaxkDev\DiscordBot\Communication\Protocol;
use JaxkDev\DiscordBot\Communication\Packets\Discord\DiscordEventReady;
use pocketmine\utils\MainLogger;

class DiscordEventHandler{

	/** @var Client */
	private $client;

	public function __construct(Client $client){
		$this->client = $client;
	}

	public function registerEvents(): void{
		$discord = $this->client->getDiscordClient();
		$discord->on("MESSAGE_CREATE", [$this, "onMessageCreate"]);
		$discord->on("MESSAGE_DELETE", [$this, "onMessageDelete"]);
		$discord->on("MESSAGE_UPDATE", [$this, "onMessageUpdate"]);  //AKA Edit

		$discord->on("GUILD_MEMBER_ADD", [$this, "onMemberJoin"]);
		$discord->on("GUILD_MEMBER_REMOVE", [$this, "onMemberLeave"]);
		$discord->on("GUILD_MEMBER_UPDATE", [$this, "onMemberUpdate"]);   //Includes Roles,nickname etc

		$discord->on("GUILD_CREATE", [$this, "onGuildJoin"]);
		$discord->on("GUILD_UPDATE", [$this, "onGuildUpdate"]);
		$discord->on("GUILD_DELETE", [$this, "onGuildLeave"]);

		$discord->on("CHANNEL_CREATE", [$this, "onChannelCreate"]);
		$discord->on("CHANNEL_UPDATE", [$this, "onChannelUpdate"]);
		$discord->on("CHANNEL_DELETE", [$this, "onChannelDelete"]);

		$discord->on("GUILD_ROLE_CREATE", [$this, "onRoleCreate"]);
		$discord->on("GUILD_ROLE_UPDATE", [$this, "onRoleUpdate"]);
		$discord->on("GUILD_ROLE_DELETE", [$this, "onRoleDelete"]);

		$discord->on("INVITE_CREATE", [$this, "onInviteCreate"]);
		$discord->on("INVITE_DELETE", [$this, "onInviteDelete"]);

		$discord->on("GUILD_BAN_ADD", [$this, "onBanAdd"]);
		$discord->on("GUILD_BAN_REMOVE", [$this, "onBanRemove"]);

		/*
		 * TODO (others planned for 2.1):
		 * - Reactions (Probably wont store previous reactions, could be very large...)
		 * - Pins (Note event only emits the pins for the channel not if one was added/deleted/unpinned etc.)
		 */
	}

	public function onReady(): void{
		if($this->client->getThread()->getStatus() !== Protocol::THREAD_STATUS_STARTED){
			MainLogger::getLogger()->warning("Closing thread, unexpected state change.");
			$this->client->close();
		}

		//Default activity.
		$ac = new Activity();
		$ac->setMessage("PocketMine-MP v".\pocketmine\VERSION);
		$ac->setType(Activity::TYPE_PLAYING);
		$ac->setStatus(Activity::STATUS_ONLINE);
		$this->client->updatePresence($ac);

		// Register all other events.
		$this->registerEvents();

		// Dump all discord data.
		$pk = new DiscordDataDump();
		$pk->setTimestamp(time());

		MainLogger::getLogger()->debug("Starting the data pack, please be patient.");
		$t = microtime(true);
		$mem = memory_get_usage(true);

		$client = $this->client->getDiscordClient();

		/** @var DiscordGuild $guild */
		foreach($client->guilds as $guild){
			$pk->addServer(ModelConverter::genModelServer($guild));

			/** @var RolePermission $permissions */
			$permissions = $guild->members->offsetGet($client->id)->getPermissions();

			if($permissions->ban_members){
				/** @noinspection PhpUnhandledExceptionInspection */
				$guild->bans->freshen()->done(function() use ($guild){
					MainLogger::getLogger()->debug("Successfully fetched ".sizeof($guild->bans)." bans from server '".
						$guild->name."' (".$guild->id.")");
					if(sizeof($guild->bans) === 0) return;
					$pk = new DiscordDataDump();
					$pk->setTimestamp(time());
					/** @var DiscordBan $ban */
					foreach($guild->bans as $ban){
						$pk->addBan(ModelConverter::genModelBan($ban));
					}
					$this->client->getThread()->writeOutboundData($pk);
				}, function() use ($guild){
					MainLogger::getLogger()->warning("Failed to fetch bans from server '".$guild->name."' (".$guild->id.")");
				});
			}else{
				MainLogger::getLogger()->notice("Cannot fetch bans from server '".$guild->name."' (".$guild->id.
					"), Bot does not have 'ban_members' permission.");
			}

			/** @var DiscordChannel $channel */
			foreach($guild->channels as $channel){
				if($channel->type !== DiscordChannel::TYPE_TEXT) continue;
				$pk->addChannel(ModelConverter::genModelChannel($channel));
			}

			/** @var DiscordRole $role */
			foreach($guild->roles as $role){
				$pk->addRole(ModelConverter::genModelRole($role));
			}

			if($permissions->manage_guild){
				/** @noinspection PhpUnhandledExceptionInspection */
				$guild->invites->freshen()->done(function() use ($guild){
					MainLogger::getLogger()->debug("Successfully fetched ".sizeof($guild->invites).
						" invites from server '".$guild->name."' (".$guild->id.")");
					if(sizeof($guild->invites) === 0) return;
					$pk = new DiscordDataDump();
					$pk->setTimestamp(time());
					/** @var DiscordInvite $invite */
					foreach($guild->invites as $invite){
						$pk->addInvite(ModelConverter::genModelInvite($invite));
					}
					$this->client->getThread()->writeOutboundData($pk);
				}, function() use ($guild){
					MainLogger::getLogger()->warning("Failed to fetch invites from server '".$guild->name."' (".$guild->id.")");
				});
			}else{
				MainLogger::getLogger()->notice("Cannot fetch invites from server '".$guild->name."' (".$guild->id.
					"), Bot does not have 'manage_guild' permission.");
			}

			/** @var DiscordMember $member */
			foreach($guild->members as $member){
				$pk->addMember(ModelConverter::genModelMember($member));
			}
		}

		/** @var DiscordUser $user */
		foreach($client->users as $user){
			$pk->addUser(ModelConverter::genModelUser($user));
		}

		$pk->setBotUser(ModelConverter::genModelUser($client->user));

		$this->client->getThread()->writeOutboundData($pk);

		MainLogger::getLogger()->debug("Data pack Took: ".round(microtime(true)-$t, 5)."s & ".
			round(((memory_get_usage(true)-$mem)/1024)/1024, 4)."mb of memory, Final size: ".$pk->getSize());

		$this->client->getThread()->setStatus(Protocol::THREAD_STATUS_READY);
		MainLogger::getLogger()->info("Client ready.");

		$this->client->getThread()->writeOutboundData(new DiscordEventReady());
		$this->client->getCommunicationHandler()->sendHeartbeat();
		$this->client->logDebugInfo();
	}

	public function onMessageCreate(DiscordMessage $message, Discord $discord): void{
		//var_dump(microtime(true)." - create message #".$message->id);
		if(!$this->checkMessage($message)) return;
		//if($message->author->id === "305060807887159296") $message->react("❤️");
		//Dont ask questions...
		$packet = new DiscordEventMessageSent();
		$packet->setMessage(ModelConverter::genModelMessage($message));
		$this->client->getThread()->writeOutboundData($packet);
	}


	public function onMessageUpdate(DiscordMessage $message, Discord $discord): void{
		//var_dump(microtime(true)." - update message #".$message->id);
		if(!$this->checkMessage($message)) return;
		$packet = new DiscordEventMessageUpdate();
		$packet->setMessage(ModelConverter::genModelMessage($message));
		$this->client->getThread()->writeOutboundData($packet);
	}

	/**
	 * @param DiscordMessage|\stdClass $message
	 * @param Discord                  $discord
	 */
	public function onMessageDelete($message, Discord $discord): void{
		//var_dump(microtime(true)." - delete message #".$message->id);
		$packet = new DiscordEventMessageDelete();
		$packet->setMessageId($message->id);
		$this->client->getThread()->writeOutboundData($packet);
	}

	public function onMemberJoin(DiscordMember $member, Discord $discord): void{
		$packet = new DiscordEventMemberJoin();
		$packet->setMember(ModelConverter::genModelMember($member));
		$packet->setUser(ModelConverter::genModelUser($member->user));
		$this->client->getThread()->writeOutboundData($packet);
	}

	public function onMemberUpdate(DiscordMember $member, Discord $discord): void{
		$packet = new DiscordEventMemberUpdate();
		$packet->setMember(ModelConverter::genModelMember($member));
		$this->client->getThread()->writeOutboundData($packet);
	}

	public function onMemberLeave(DiscordMember $member, Discord $discord): void{
		$packet = new DiscordEventMemberLeave();
		$packet->setMemberID($member->guild_id.".".$member->id);
		$this->client->getThread()->writeOutboundData($packet);
	}

	public function onGuildJoin(DiscordGuild $guild, Discord $discord): void{
		$channels = [];
		/** @var DiscordChannel $channel */
		foreach($guild->channels->toArray() as $channel){
			if($channel->type === DiscordChannel::TYPE_TEXT){
				$channels[] = ModelConverter::genModelChannel($channel);
			}
		}
		$roles = [];
		/** @var DiscordRole $role */
		foreach($guild->roles->toArray() as $role){
			$roles[] = ModelConverter::genModelRole($role);
		}
		$members = [];
		/** @var DiscordMember $member */
		foreach($guild->members->toArray() as $member){
			$members[] = ModelConverter::genModelMember($member);
		}

		$packet = new DiscordEventServerJoin();
		$packet->setServer(ModelConverter::genModelServer($guild));
		$packet->setChannels($channels);
		$packet->setMembers($members);
		$packet->setRoles($roles);
		$this->client->getThread()->writeOutboundData($packet);
	}

	public function onGuildUpdate(DiscordGuild $guild, Discord $discord): void{
		$packet = new DiscordEventServerUpdate();
		$packet->setServer(ModelConverter::genModelServer($guild));
		$this->client->getThread()->writeOutboundData($packet);
	}

	public function onGuildLeave(DiscordGuild $guild, Discord $discord): void{
		$packet = new DiscordEventServerLeave();
		$packet->setServerId($guild->id);
		$this->client->getThread()->writeOutboundData($packet);
	}

	public function onChannelCreate(DiscordChannel $channel, Discord $discord): void{
		if(!$this->checkChannel($channel)) return;
		$packet = new DiscordEventChannelCreate();
		$packet->setChannel(ModelConverter::genModelChannel($channel));
		$this->client->getThread()->writeOutboundData($packet);
	}

	public function onChannelUpdate(DiscordChannel $channel, Discord $discord): void{
		if(!$this->checkChannel($channel)) return;
		$packet = new DiscordEventChannelUpdate();
		$packet->setChannel(ModelConverter::genModelChannel($channel));
		$this->client->getThread()->writeOutboundData($packet);
	}

	public function onChannelDelete(DiscordChannel $channel, Discord $discord): void{
		if(!$this->checkChannel($channel)) return;
		$packet = new DiscordEventChannelDelete();
		$packet->setChannelId($channel->id);
		$this->client->getThread()->writeOutboundData($packet);
	}

	public function onRoleCreate(DiscordRole $role, Discord $discord): void{
		$packet = new DiscordEventRoleCreate();
		$packet->setRole(ModelConverter::genModelRole($role));
		$this->client->getThread()->writeOutboundData($packet);
	}

	public function onRoleUpdate(DiscordRole $role, Discord $discord): void{
		$packet = new DiscordEventRoleUpdate();
		$packet->setRole(ModelConverter::genModelRole($role));
		$this->client->getThread()->writeOutboundData($packet);
	}

	public function onRoleDelete(DiscordRole $role, Discord $discord): void{
		$packet = new DiscordEventRoleDelete();
		$packet->setRoleId($role->id);
		$this->client->getThread()->writeOutboundData($packet);
	}

	public function onInviteCreate(DiscordInvite $invite, Discord $discord): void{
		$packet = new DiscordEventInviteCreate();
		$packet->setInvite(ModelConverter::genModelInvite($invite));
		$this->client->getThread()->writeOutboundData($packet);
	}

	/**
	 * @param \stdClass $invite {channel_id: str, guild_id: str, code: str}
	 * @param Discord   $discord
	 */
	public function onInviteDelete(\stdClass $invite, Discord $discord): void{
		$packet = new DiscordEventInviteDelete();
		$packet->setInviteCode($invite->code);
		$this->client->getThread()->writeOutboundData($packet);
	}

	public function onBanAdd(DiscordBan $ban, Discord $discord): void{
		//No reason unless you freshen bans which is only possible with ban_members permission.
		$g = $ban->guild;
		/** @var DiscordMember|null $m */
		$m = $g->members->offsetGet($discord->user->id);
		if($m !== null and $m->getPermissions()->ban_members){
			//Get ban reason.
			/** @noinspection PhpUnhandledExceptionInspection */ //Impossible.
			$g->bans->freshen()->done(function() use ($ban, $g){
				//Got latest bans so we can fetch reason unless it was unbanned in like 0.01s
				/** @var DiscordBan|null $b */
				$b = $g->bans->offsetGet($ban->user_id);
				if($b !== null){
					MainLogger::getLogger()->debug("Successfully fetched bans, attached reason to new ban event.");
					$packet = new DiscordEventBanAdd();
					$packet->setBan(ModelConverter::genModelBan($b));
					$this->client->getThread()->writeOutboundData($packet);
				}else{
					MainLogger::getLogger()->debug("No ban after freshen ??? (IMPORTANT LOGIC ERROR)");
					$packet = new DiscordEventBanAdd();
					$packet->setBan(ModelConverter::genModelBan($ban));
					$this->client->getThread()->writeOutboundData($packet);
				}
			}, function() use ($ban){
				//Failed so just send ban with no reason.
				MainLogger::getLogger()->debug("Failed to fetch bans even with ban_members permission, using old ban object.");
				$packet = new DiscordEventBanAdd();
				$packet->setBan(ModelConverter::genModelBan($ban));
				$this->client->getThread()->writeOutboundData($packet);
			});
		}else{
			MainLogger::getLogger()->debug("Bot does not have ban_members permission so no reason was attached to this ban.");
			$packet = new DiscordEventBanAdd();
			$packet->setBan(ModelConverter::genModelBan($ban));
			$this->client->getThread()->writeOutboundData($packet);
		}
	}

	public function onBanRemove(DiscordBan $ban, Discord $discord): void{
		$packet = new DiscordEventBanRemove();
		$packet->setId($ban->guild_id.".".$ban->user_id);
		$this->client->getThread()->writeOutboundData($packet);
	}

	/**
	 * Checks if we handle this type of message in this type of channel.
	 * @param DiscordMessage $message
	 * @return bool
	 */
	private function checkMessage(DiscordMessage $message): bool{
		// Can be user if bot doesnt have correct intents enabled on discord developer dashboard.
		if($message->author === null) return false; //Investigating specific case.
		if($message->author instanceof DiscordMember ? $message->author->user->bot : (isset($message->author) ? $message->author->bot : true)) return false;

		// Other types of messages not used right now.
		if($message->type !== DiscordMessage::TYPE_NORMAL) return false;
		if($message->channel->type !== DiscordChannel::TYPE_TEXT) return false;
		if(($message->content ?? "") === "") return false; //Images/Files, can be empty strings or just null in other cases.
		if($message->channel->guild_id === null) return false;

		return true;
	}

	private function checkChannel(DiscordChannel $channel): bool{
		return (($channel->type ?? -1) === DiscordChannel::TYPE_TEXT);
	}
}