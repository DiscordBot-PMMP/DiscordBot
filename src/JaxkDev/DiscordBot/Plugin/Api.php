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
use JaxkDev\DiscordBot\Models\Channels\Channel;
use JaxkDev\DiscordBot\Models\Channels\DmChannel;
use JaxkDev\DiscordBot\Models\Channels\ServerChannel;
use JaxkDev\DiscordBot\Models\Message;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestSendMessage;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestUpdateActivity;
use JaxkDev\DiscordBot\Libs\React\Promise\PromiseInterface;
use JaxkDev\DiscordBot\Models\User;

/*
 * TODO:
 * - Kick member
 * - Ban member
 * - Give role
 * - Take role
 * - Edit message
 * - Delete message
 * - Delete channel
 * - Create channel
 * - Update permissions (channel,role,member)
 * - Update channel
 * - Create invite
 * - Delete invite
 */

/**
 * For internal and developers use for interacting with the discord bot.
 * @see Main::getApi() To get instance.
 * @see Storage For all discord data.
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
		if(strlen($content) > 2000) return null;

		if(!$channel instanceof Channel){
			$c = Storage::getChannel($channel);
			if(!$c instanceof ServerChannel){
				$u = Storage::getUser($channel); //check user for dm channel.
				//Now you could in theory try to send a message to any user but its almost certain to be
				//rejected if its not in storage.
				if(!$u instanceof User) return null;
				$channel = new DmChannel();
				$channel->setId($u->getId());
			}else{
				$channel = $c;
			}
		}

		$bot = Storage::getBotUser();
		if($bot === null) return null;

		$msg = new Message();
		if($channel instanceof ServerChannel) $msg->setServerId($channel->getServerId());
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
		$pk = new RequestSendMessage();
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
		$pk = new RequestUpdateActivity();
		$pk->setActivity($activity);
		$this->plugin->writeOutboundData($pk);
		return ApiResolver::create($pk->getUID());
	}
}