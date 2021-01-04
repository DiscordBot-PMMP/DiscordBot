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

namespace JaxkDev\DiscordBot\Plugin;

use JaxkDev\DiscordBot\Communication\Models\Activity;
use JaxkDev\DiscordBot\Communication\Models\Channel;
use JaxkDev\DiscordBot\Communication\Models\Member;
use JaxkDev\DiscordBot\Communication\Models\Message;
use JaxkDev\DiscordBot\Communication\Models\Role;
use JaxkDev\DiscordBot\Communication\Models\Server;
use JaxkDev\DiscordBot\Communication\Models\User;
use JaxkDev\DiscordBot\Communication\Packets\PluginRequestSendMessage;
use JaxkDev\DiscordBot\Communication\Packets\PluginRequestUpdateActivity;

/**
 * For internal and developers use for interacting with the discord bot.
 * @see Main::getAPI()
 * @version 2.0.0
 */
class API{

	/** @var Main */
	private $plugin;

	public function __construct(Main $plugin){
		$this->plugin = $plugin;
	}

	/**
	 * Creates the Message model ready for sending, or null if not possible to create the message at this time.
	 * @see API::sendMessage For sending the message.
	 * @param Channel|string $channel Channel model or channel ID.
	 * @param string         $content Content, <2000 in length.
	 * @return Message|null
	 */
	public function createMessage($channel, string $content): ?Message{
		if(!$channel instanceof Channel){
			$channel = self::getChannel($channel);
			if(!$channel instanceof Channel) return null;
		}
		$bot = Storage::getBotUser();
		if($bot === null) return null;

		$msg = new Message();
		$msg->setServerId($channel->getServerId())
			->setChannelId($channel->getId())
			->setAuthorId($bot->getId())
			->setContent($content);
		return $msg;
	}

	/**
	 * Sends the Message to discord.
	 * @see API::createMessage For creating a message
	 * @param Message $message
	 */
	public function sendMessage(Message $message): void{
		$pk = new PluginRequestSendMessage();
		$pk->setMessage($message);
		$this->plugin->writeOutboundData($pk);
	}

	/**
	 * Creates the Activity model ready for sending/updating.
	 * @see API::updateActivity For updating the activity.
	 * @see Activity            For Status & Type constants.
	 * @param string      $status
	 * @param int|null    $type
	 * @param string|null $message
	 * @return Activity
	 */
	public function createActivity(string $status, ?int $type = null, ?string $message = null): Activity{
		$activity = new Activity();
		$activity->setStatus($status)
			->setType($type)
			->setMessage($message);
		return $activity;
	}

	/**
	 * Sends the new activity to replace the current one the bot has.
	 * @see API::createActivity
	 * @param Activity $activity
	 */
	public function updateActivity(Activity $activity): void{
		$pk = new PluginRequestUpdateActivity();
		$pk->setActivity($activity);
		$this->plugin->writeOutboundData($pk);
	}







	/*
	 * Wrappers for Storage
	 */

	/**
	 * @param string $id
	 * @return Server|null
	 */
	public static function getServer(string $id): ?Server{
		return Storage::getServer($id);
	}

	/**
	 * @param string $name
	 * @return Server|null
	 */
	public static function getServerByName(string $name): ?Server{
		return Storage::getServerByName($name);
	}

	/**
	 * @param string $id
	 * @return Channel|null
	 */
	public static function getChannel(string $id): ?Channel{
		return Storage::getChannel($id);
	}

	/**
	 * @param string $serverId
	 * @return Channel[]
	 */
	public static function getChannelsByServer(string $serverId): array{
		return Storage::getChannelsByServer($serverId);
	}

	/**
	 * @param string $id Member's ID is unique 'serverID.userID'
	 * @return Member|null
	 */
	public static function getMember(string $id): ?Member{
		return Storage::getMember($id);
	}

	/**
	 * @param string $serverId
	 * @return array
	 */
	public static function getMembersByServer(string $serverId): array{
		return Storage::getMembersByServer($serverId);
	}

	/**
	 * @param string $id
	 * @return User|null
	 */
	public static function getUser(string $id): ?User{
		return Storage::getUser($id);
	}

	/**
	 * @param string $id
	 * @return Role|null
	 */
	public static function getRole(string $id): ?Role{
		return Storage::getRole($id);
	}

	/**
	 * @return User|null
	 */
	public static function getBotUser(): ?User{
		return Storage::getBotUser();
	}

	/**
	 * @return int
	 */
	public static function getStorageTimestamp(): int{
		return Storage::getTimestamp();
	}
}