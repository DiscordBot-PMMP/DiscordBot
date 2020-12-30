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
use Discord\Parts\User\Member as DiscordMember;
use JaxkDev\DiscordBot\Bot\Client;
use JaxkDev\DiscordBot\Communication\Models\Member;
use JaxkDev\DiscordBot\Communication\Models\Message;
use JaxkDev\DiscordBot\Communication\Models\User;

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
		$m->setId((int)$message->id)
			->setTimestamp($message->timestamp->getTimestamp())
			->setAuthorId(($message->channel->guild_id.".".$message->author->id))
			->setChannelId((int)$message->channel_id)
			->setGuildId((int)$message->channel->guild_id)
			->setEveryoneMentioned($message->mention_everyone)
			->setContent($message->content)
			->setChannelsMentioned(array_keys($message->mention_channels->toArray()))
			->setRolesMentioned(array_keys($message->mention_roles->toArray()))
			->setUsersMentioned(array_keys($message->mentions->toArray()));

		$this->client->getPluginCommunicationHandler()->sendMessageSentEvent($m);
	}

	public function onMemberJoin(DiscordMember $member, Discord $discord): void{
		//TODO, Do we send user data here as well ?
		$u = new User();
		$u->setId((int)$member->id)
			->setUsername($member->username)
			->setDiscriminator((int)$member->user->discriminator)
			->setCreationTimestamp((int)($member->user->createdTimestamp()??0))
			->setAvatarUrl($member->user->avatar);

		$m = new Member();
		$m->setUserId((int)$member->id)
			->setBoostTimestamp(null)
			->setGuildId((int)$member->guild_id)
			->setJoinTimestamp($member->joined_at->getTimestamp())
			->setNickname($member->nick)
			->setRolesId(array_keys($member->roles->toArray()))
			->setId();

		$this->client->getPluginCommunicationHandler()->sendMemberJoinEvent($m, $u);
	}

	public function onMemberLeave(DiscordMember $member, Discord $discord): void{
		$this->client->getPluginCommunicationHandler()->sendMemberLeaveEvent($member->guild_id.".".$member->id);
	}
}