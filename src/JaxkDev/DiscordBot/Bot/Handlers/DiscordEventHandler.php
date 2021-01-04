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

use Discord\Discord;
use Discord\Parts\Channel\Channel as DiscordChannel;
use Discord\Parts\Channel\Message as DiscordMessage;
use Discord\Parts\Guild\Guild as DiscordGuild;
use Discord\Parts\Guild\Role as DiscordRole;
use Discord\Parts\User\Member as DiscordMember;
use Discord\Parts\User\User as DiscordUser;
use JaxkDev\DiscordBot\Bot\Client;
use JaxkDev\DiscordBot\Communication\Models\Activity;
use JaxkDev\DiscordBot\Communication\Models\Channel;
use JaxkDev\DiscordBot\Communication\Models\Member;
use JaxkDev\DiscordBot\Communication\Models\Message;
use JaxkDev\DiscordBot\Communication\Models\Role;
use JaxkDev\DiscordBot\Communication\Models\Server;
use JaxkDev\DiscordBot\Communication\Models\User;
use JaxkDev\DiscordBot\Communication\Packets\DiscordEventAllData;
use JaxkDev\DiscordBot\Communication\Protocol;
use pocketmine\utils\MainLogger;

class DiscordEventHandler {

	/** @var Client */
	private $client;

	public function __construct(Client $client){
		$this->client = $client;
	}

	public function registerEvents(): void{
		$discord = $this->client->getDiscordClient();
		$discord->on('MESSAGE_CREATE', array($this, 'onMessage'));
		$discord->on('GUILD_MEMBER_ADD', array($this, 'onMemberJoin'));
		$discord->on('GUILD_MEMBER_REMOVE', array($this, 'onMemberLeave'));
		/**
		 * TODO:
		 * SERVER_JOIN/LEAVE/EDIT
		 * CHANNEL_CREATE/DELETE/EDIT
		 * MEMBER_EDIT (Roles,nickname etc)
		 * ROLE_CREATE/DELETE/EDIT
		 * MESSAGE_DELETE/EDIT
		 */
	}

	public function onReady(): void{
		if($this->client->getThread()->getStatus() !== Protocol::THREAD_STATUS_STARTED){
			MainLogger::getLogger()->warning("Closing thread, unexpected state change.");
			$this->client->close();
		}

		//Default activity.
		$ac = new Activity();
		$ac->setMessage("In PocketMine-MP.")->setType(Activity::TYPE_PLAYING)->setStatus(Activity::STATUS_IDLE);
		$this->client->updatePresence($ac);

		// Register all other events.
		$this->registerEvents();

		// Dump all discord data.
		$pk = new DiscordEventAllData();
		$pk->setTimestamp(time());

		MainLogger::getLogger()->debug("Starting the data pack, this can take several minutes please be patient.\nNote this does not effect the main thread.");
		$t = microtime(true);
		$mem = memory_get_usage(true);

		$client = $this->client->getDiscordClient();

		/** @var DiscordGuild $guild */
		foreach($client->guilds as $guild){
			$server = new Server();
			$server->setId($guild->id)
				->setCreationTimestamp($guild->createdTimestamp())
				->setIconUrl($guild->icon)
				->setLarge($guild->large)
				->setMemberCount($guild->member_count)
				->setName($guild->name)
				->setOwnerId($guild->owner_id)
				->setRegion($guild->region);
			$pk->addServer($server);

			/** @var DiscordChannel $channel */
			foreach($guild->channels as $channel){
				if($channel->type !== DiscordChannel::TYPE_TEXT) continue;
				$ch = new Channel();
				$ch->setName($channel->name)
					->setId($channel->id)
					->setCategory(null) //todo, parent_id gives ID of channel (Type_category)
					->setDescription($channel->topic)
					->setServerId($guild->id);
				$pk->addChannel($ch);
			}

			/** @var DiscordRole $role */
			foreach($guild->roles as $role){
				$r = new Role();
				$r->setServerId($guild->id)
					->setId($role->id)
					->setName($role->name)
					->setColour($role->color)
					->setHoistedPosition($role->position)
					->setMentionable($role->mentionable)
					->setPermissions($role->permissions->bitwise);
				$pk->addRole($r);
			}

			/** @var DiscordMember $member */
			foreach($guild->members as $member){
				$m = new Member();
				$m->setServerId($guild->id)
					->setUserId($member->user->id)
					->setNickname($member->nick)
					->setJoinTimestamp($member->joined_at === null ? 0 : $member->joined_at->getTimestamp())
					->setBoostTimestamp($member->premium_since === null ? null : $member->premium_since->getTimestamp())
					->setId();

				/** @var int[] $roles */
				$roles = [];

				/** @var DiscordRole $role */
				foreach($member->roles as $role){
					$roles[] = $role->id;
				}
				$m->setRolesId($roles);

				$pk->addMember($m);
			}
		}

		/** @var DiscordUser $user */
		foreach($client->users as $user){
			$u = new User();
			$u->setId($user->id)
				->setCreationTimestamp($user->createdTimestamp())
				->setAvatarUrl($user->avatar)
				->setDiscriminator($user->discriminator)
				->setUsername($user->username);
			$pk->addUser($u);
		}

		$bu = $client->user;
		$u = new User();
		$u->setId($bu->id)
			->setUsername($bu->username)
			->setDiscriminator($bu->discriminator)
			->setAvatarUrl($bu->avatar)
			->setCreationTimestamp($bu->createdTimestamp());
		$pk->setBotUser($u);

		$this->client->getThread()->writeOutboundData($pk);

		MainLogger::getLogger()->debug("Data pack Took: ".round(microtime(true)-$t, 5)."s & ".
			round(((memory_get_usage(true)-$mem)/1024)/1024, 4)."mb of memory, Final size: ".$pk->getSize());

		// Force fresh heartbeat asap, as that took quite some time.
		$this->client->getCommunicationHandler()->sendHeartbeat();

		$this->client->getThread()->setStatus(Protocol::THREAD_STATUS_READY);
		MainLogger::getLogger()->info("Client ready.");

		$this->client->logDebugInfo();
	}

	public function onMessage(DiscordMessage $message, Discord $discord): void{
		// Eg webhooks ?
		if(!$message->author instanceof DiscordMember) return;
		if($message->author->user->bot) return;

		// Other types of messages not used right now.
		if($message->type !== DiscordMessage::TYPE_NORMAL) return;
		if($message->channel->type !== DiscordChannel::TYPE_TEXT) return;
		if(($message->content ?? "") === "") return; //Images/Files, can be empty strings or just null in other cases.

		if($message->channel->guild_id === null) throw new \AssertionError("GuildID Cannot be null.");

		$m = new Message();
		$m->setId($message->id)
			->setTimestamp($message->timestamp->getTimestamp())
			->setAuthorId(($message->channel->guild_id.".".$message->author->id))
			->setChannelId($message->channel_id)
			->setServerId($message->channel->guild_id)
			->setEveryoneMentioned($message->mention_everyone)
			->setContent($message->content)
			->setChannelsMentioned(array_keys($message->mention_channels->toArray()))
			->setRolesMentioned(array_keys($message->mention_roles->toArray()))
			->setUsersMentioned(array_keys($message->mentions->toArray()));

		$this->client->getCommunicationHandler()->sendMessageSentEvent($m);
	}

	public function onMemberJoin(DiscordMember $member, Discord $discord): void{
		$u = new User();
		$u->setId($member->id)
			->setUsername($member->username)
			->setDiscriminator($member->user->discriminator)
			->setCreationTimestamp(($member->user->createdTimestamp()??0))
			->setAvatarUrl($member->user->avatar);

		$m = new Member();
		$m->setUserId($member->id)
			->setBoostTimestamp(null)
			->setServerId($member->guild_id)
			->setJoinTimestamp($member->joined_at === null ? 0 : $member->joined_at->getTimestamp())
			->setNickname($member->nick)
			->setRolesId(array_keys($member->roles->toArray()))
			->setId();

		$this->client->getCommunicationHandler()->sendMemberJoinEvent($m, $u);
	}

	public function onMemberLeave(DiscordMember $member, Discord $discord): void{
		$this->client->getCommunicationHandler()->sendMemberLeaveEvent($member->guild_id.".".$member->id);
	}
}