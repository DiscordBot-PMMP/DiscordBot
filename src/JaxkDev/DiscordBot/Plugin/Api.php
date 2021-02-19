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

namespace JaxkDev\DiscordBot\Plugin;

use JaxkDev\DiscordBot\Models\Activity;
use JaxkDev\DiscordBot\Models\Channel;
use JaxkDev\DiscordBot\Models\Message;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\PluginRequestSendMessage;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\PluginRequestUpdateActivity;
use JaxkDev\DiscordBot\Libs\React\Promise\PromiseInterface;

/**
 * For internal and developers use for interacting with the discord bot.
 * @see Main::getApi() To get instance.
 * @see Storage For all discord data.
 * @version 2.0.0
 */
class Api{

	/** @var Main */
	private $plugin;

	public function __construct(Main $plugin){
		$this->plugin = $plugin;
	}

	/**
	 * Creates the Message model ready for sending, or null if not possible to create the message at this time.
	 *
	 * @param Channel|string $channel Channel model or channel ID.
	 * @param string         $content Content, <2000 in length.
	 * @return Message|null
	 * @see Api::sendMessage For sending the message.
	 */
	public function createMessage($channel, string $content): ?Message{
		if(!$channel instanceof Channel){
			$channel = Storage::getChannel($channel);
			if(!$channel instanceof Channel) return null;
		}
		$bot = Storage::getBotUser();
		if($bot === null) return null;

		$msg = new Message();
		$msg->setServerId($channel->getServerId());
		$msg->setChannelId($channel->getId());
		$msg->setAuthorId($bot->getId());
		$msg->setContent($content);
		return $msg;
	}

	/**
	 * Sends the Message to discord.
	 *
	 * @param Message $message
	 * @return PromiseInterface
	 * @see Api::createMessage For creating a message
	 */
	public function sendMessage(Message $message): PromiseInterface{
		$pk = new PluginRequestSendMessage();
		$pk->setMessage($message);
		$this->plugin->writeOutboundData($pk);
		return ApiResolver::create($pk->getUID());
	}

	/**
	 * Creates the Activity model ready for sending/updating.
	 *
	 * @param string      $status
	 * @param int|null    $type
	 * @param string|null $message
	 * @return Activity
	 * @see Api::updateActivity For updating the activity.
	 * @see Activity            For Status & Type constants.
	 */
	public function createActivity(string $status, ?int $type = null, ?string $message = null): Activity{
		$activity = new Activity();
		$activity->setStatus($status);
		$activity->setType($type);
		$activity->setMessage($message);
		return $activity;
	}

	/**
	 * Sends the new activity to replace the current one the bot has.
	 *
	 * @param Activity $activity
	 * @return PromiseInterface
	 * @see Api::createActivity
	 */
	public function updateActivity(Activity $activity): PromiseInterface{
		$pk = new PluginRequestUpdateActivity();
		$pk->setActivity($activity);
		$this->plugin->writeOutboundData($pk);
		return ApiResolver::create($pk->getUID());
	}
}