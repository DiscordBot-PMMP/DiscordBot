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
use Discord\Parts\Channel\Message;
use Discord\Parts\User\Member;
use JaxkDev\DiscordBot\Bot\Client;

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

	public function onMessage(Message $message, Discord $discord){
		if($message->author instanceof Member ? $message->author->user->bot : $message->author->bot) return;

		// Other types of messages not used right now.
		if($message->type !== Message::TYPE_NORMAL) return;

		//Send message event to plugin.
	}

	public function onMemberJoin(Member $member, Discord $discord){
		$this->client->getPluginCommunicationHandler()->sendMemberJoinEvent(
			$member->guild->id,
			$member->guild->name,
			$member->id,
			$member->discriminator,
			$member->username,
			$member->joined_at->getTimestamp()
		);
	}

	public function onMemberLeave(Member $member, Discord $discord){
		$this->client->getPluginCommunicationHandler()->sendMemberLeaveEvent(
			$member->guild->id,
			$member->guild->name,
			$member->id,
			$member->discriminator,
			$member->username,
			time()
		);
	}
}