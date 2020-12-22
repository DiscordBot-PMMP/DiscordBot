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
use JaxkDev\DiscordBot\Communication\Models\Activity;
use JaxkDev\DiscordBot\Communication\Models\Member;
use JaxkDev\DiscordBot\Communication\Models\Message;

class DiscordEventHandler {
	/**
	 * @var Client
	 */
	private $client;

	public function __construct(Client $client){
		$this->client = $client;
	}

	public function registerEvents(): void{
		$discord = $this->client->getDiscordClient();
		$discord->on('MESSAGE_CREATE', array($this, 'onMessage'));
		$discord->on('GUILD_MEMBER_ADD', array($this, 'onMemberJoin'));
		$discord->on('GUILD_MEMBER_REMOVE', array($this, 'onMemberLeave'));
	}

	public function onMessage(DiscordMessage $message, Discord $discord): void{
		// Eg webhooks ?
		if(!$message->author instanceof DiscordMember) return;
		if($message->author->user->bot) return;

		// Other types of messages not used right now.
		if($message->type !== DiscordMessage::TYPE_NORMAL) return;
		if($message->channel->type !== DiscordChannel::TYPE_TEXT) return;
		if(($message->content ?? "") === "") return; //Images/Files, can be empty strings or just null in other cases.

		/* Clean mentions, TODO Should we clean content before sending ?
		// Channels:
		$message->content = preg_replace_callback("/<#[0-9]+>/", function($d){
			$id = substr($d[0], 2, 18); //Fixed format afaik.
			$channel = $this->client->getDiscordClient()->getChannel($id);
			if($channel === null) return $d[0];
			return "#".$channel->name;
		}, $message->content) ?? "";

		// Users:
		$message->content = preg_replace_callback("/<@!?[0-9]+>/", function($d){
			$id = substr($d[0], ($d[0][2] === "!" ? 3 : 2), 18);
			$user = $this->client->getDiscordClient()->users->get("id", $id);
			if($user === null) return $d[0];
			return "@".$user->username."#".$user->discriminator;
		}, $message->content) ?? "";

		// Roles:
		$message->content = preg_replace_callback("/<@&[0-9]+>/", function($d) use($message){
			$id = substr($d[0], ($d[0][2] === "&" ? 3 : 2), 18);
			$role = $message->author->guild->roles->get("id", $id);
			if($role === null) return $d[0];
			return "@".$role->name;
		}, $message->content) ?? "";*/

		if($message->channel->guild_id === null) throw new \AssertionError("GuildID Cannot be null.");

		$m = new Message();
		$m->setId($message->id)
			->setTimestamp($message->timestamp->getTimestamp())
			->setAuthorId($message->author->user->id)
			->setChannelId($message->channel_id)
			->setGuildId($message->channel->guild_id)
			->setEveryoneMentioned($message->mention_everyone)
			->setContent($message->content)
			->setChannelsMentioned(array_keys($message->mention_channels->toArray()))
			->setRolesMentioned(array_keys($message->mention_roles->toArray()))
			->setUsersMentioned(array_keys($message->mentions->toArray()));

		$this->client->getPluginCommunicationHandler()->sendMessageSentEvent($m);
	}

	public function onMemberJoin(DiscordMember $member, Discord $discord): void{
		$activity = new Activity();
		$activity->setType($member->game->type)
			->setMessage($member->game->name)
			->setStatus($member->game->state);

		$m = new Member();
		$m->setId($member->id)
			->setUsername($member->username)
			->setActivity($activity)
			->setStatus($member->status)
			->setAvatarUrl($member->user->avatar)
			->setBoostTimestamp(null)
			->setDiscriminator($member->user->discriminator)
			->setGuildId($member->guild_id)
			->setJoinTimestamp($member->joined_at->getTimestamp())
			->setNickname($member->nick)
			->setRolesId(array_keys($member->roles->toArray()));

		$this->client->getPluginCommunicationHandler()->sendMemberJoinEvent($m);
	}

	public function onMemberLeave(DiscordMember $member, Discord $discord): void{
		$activity = new Activity();
		$activity->setType($member->game->type)
			->setMessage($member->game->name)
			->setStatus($member->game->state);

		$m = new Member();
		$m->setId($member->id)
			->setUsername($member->username)
			->setActivity($activity)
			->setStatus($member->status)
			->setAvatarUrl($member->user->avatar)
			->setBoostTimestamp(null)
			->setDiscriminator($member->user->discriminator)
			->setGuildId($member->guild_id)
			->setJoinTimestamp($member->joined_at->getTimestamp())
			->setNickname($member->nick)
			->setRolesId(array_keys($member->roles->toArray()));

		$this->client->getPluginCommunicationHandler()->sendMemberLeaveEvent($m);
	}
}