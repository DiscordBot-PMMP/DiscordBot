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

use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestDeleteMessage;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestEditMessage;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestInitialiseBan;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestInitialiseInvite;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestKickMember;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestRevokeInvite;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestSendMessage;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestRevokeBan;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestUpdateActivity;
use JaxkDev\DiscordBot\Libs\React\Promise\PromiseInterface;
use JaxkDev\DiscordBot\Models\Activity;
use JaxkDev\DiscordBot\Models\Ban;
use JaxkDev\DiscordBot\Models\Channels\Channel;
use JaxkDev\DiscordBot\Models\Channels\DmChannel;
use JaxkDev\DiscordBot\Models\Channels\ServerChannel;
use JaxkDev\DiscordBot\Models\Channels\TextChannel;
use JaxkDev\DiscordBot\Models\Invite;
use JaxkDev\DiscordBot\Models\Member;
use JaxkDev\DiscordBot\Models\Message;
use JaxkDev\DiscordBot\Models\User;

/*
 * TODO:
 * - Create role
 * - Give role
 * - Take role
 * - Delete role
 * - Delete channel
 * - Create channel
 * - Update permissions (channel,role,member)
 * - Update channel
 * - Update nickname
 *
 * - Assert all fields are valid before sending packet.
 *
 * Test:
 * - create ban
 * - ban
 * - unban
 * - kick
 *
 * Tested:
 * - Send message
 * - Edit message
 * - Delete message
 * - Create invite
 * - Delete invite
 */

/**
 * For internal and developers use for interacting with the discord bot.
 * Model creation methods are static.
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
	 * Creates the Activity model ready for sending/updating.
	 *
	 * @param string      $status	Constant, see JaxkDev\DiscordBot\Models\Activity class.
	 * @param int|null    $type		Constant, see JaxkDev\DiscordBot\Models\Activity class.
	 * @param string|null $message
	 * @return Activity
	 * @see Api::updateActivity For updating the activity.
	 * @see Activity            For Status & Type constants.
	 */
	public static function createActivityModel(string $status, ?int $type = null, ?string $message = null): Activity{
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
	 * @see Api::createActivityModel
	 */
	public function updateActivity(Activity $activity): PromiseInterface{
		$pk = new RequestUpdateActivity();
		$pk->setActivity($activity);
		$this->plugin->writeOutboundData($pk);
		return ApiResolver::create($pk->getUID());
	}

	/**
	 * Create a ban model ready for use or null if days is out of range.
	 *
	 * @param Member      $member
	 * @param string|null $reason		Reason for banning them, *will not be sent to member, just for audit log*
	 * @param int|null    $daysToDelete How many days worth of messages to delete, maximum 7 days.
	 * @return Ban|null
	 * @see Api::initialiseBan() To actually initialise the ban.
	 */
	public static function createBanModel(Member $member, ?string $reason = null, ?int $daysToDelete = null): ?Ban{
		if($daysToDelete !== null and ($daysToDelete < 0 or $daysToDelete > 7)) return null;
		$ban = new Ban();
		$ban->setServerId($member->getServerId());
		$ban->setUserId($member->getUserId());
		$ban->setReason($reason);
		$ban->setDaysToDelete($daysToDelete);
		return $ban;
	}

	/**
	 * Attempt to ban a member.
	 *
	 * @param Ban $ban
	 * @return PromiseInterface
	 * @see Api::createBanModel() For getting Ban model.
	 */
	public function initialiseBan(Ban $ban): PromiseInterface{
		$pk = new RequestInitialiseBan();
		$pk->setBan($ban);
		$this->plugin->writeOutboundData($pk);
		return ApiResolver::create($pk->getUID());
	}

	/**
	 * Attempt to revoke a ban.
	 *
	 * @param Ban $ban
	 * @return PromiseInterface
	 */
	public function revokeBan(Ban $ban): PromiseInterface{
		$pk = new RequestRevokeBan();
		$pk->setBan($ban);
		$this->plugin->writeOutboundData($pk);
		return ApiResolver::create($pk->getUID());
	}

	/**
	 * Attempt to kick a member.
	 *
	 * @param Member $member
	 * @return PromiseInterface
	 * @see Storage::getMember() For getting Member model.
	 */
	public function kickMember(Member $member): PromiseInterface{
		$pk = new RequestKickMember();
		$pk->setMember($member);
		$this->plugin->writeOutboundData($pk);
		return ApiResolver::create($pk->getUID());
	}

	/**
	 * Creates the Message model ready for sending, or null if user couldn't be found in storage.
	 *
	 * @param TextChannel|DmChannel|User	$channel TextChannel model or User Model for DMs
	 * @param string						$content Content, <2000 in length.
	 * @return Message|null
	 * @see Api::sendMessage For sending the message.
	 */
	public static function createMessageModel($channel, string $content): ?Message{
		if(strlen($content) > 2000) return null;
		if($channel instanceof User){
			$id = $channel->getId();
			$channel = new DmChannel();
			$channel->setId($id);
		}
		if(!$channel instanceof Channel) return null;

		$msg = new Message();
		if($channel instanceof ServerChannel) $msg->setServerId($channel->getServerId());
		$msg->setChannelId($channel->getId());
		$msg->setContent($content);
		return $msg;
	}

	/**
	 * Sends the Message to discord.
	 *
	 * @param Message $message
	 * @return PromiseInterface
	 * @see Api::createMessageModel For creating a message
	 */
	public function sendMessage(Message $message): PromiseInterface{
		$pk = new RequestSendMessage();
		$pk->setMessage($message);
		$this->plugin->writeOutboundData($pk);
		return ApiResolver::create($pk->getUID());
	}

	/**
	 * Edit a sent message.
	 *
	 * @param Message $message
	 * @return PromiseInterface
	 * @see Api::createMessageModel For creating a message
	 * @see Api::sendMessage For sending a message.
	 * @see Api::deleteMessage For deleting a sent message.
	 */
	public function editMessage(Message $message): PromiseInterface{
		$pk = new RequestEditMessage();
		$pk->setMessage($message);
		$this->plugin->writeOutboundData($pk);
		return ApiResolver::create($pk->getUID());
	}

	/**
	 * Delete a sent message.
	 *
	 * @param Message $message
	 * @return PromiseInterface
	 */
	public function deleteMessage(Message $message): PromiseInterface{
		$pk = new RequestDeleteMessage();
		$pk->setMessage($message);
		$this->plugin->writeOutboundData($pk);
		return ApiResolver::create($pk->getUID());
	}

	/**
	 * Create a model invite, must be initialised before use !
	 *
	 * @param ServerChannel $channel
	 * @param int     $maxAge		max age in seconds from initialise time until expiration, 0 for forever.
	 * @param int     $maxUses		max amount of uses before expiration, 0 for unlimited.
	 * @param bool    $temporary
	 * @return Invite|null
	 * @see Api::initialiseInvite() To actually create the invite through discord.
	 */
	public static function createInviteModel(ServerChannel $channel, int $maxAge, int $maxUses, bool $temporary = false): ?Invite{
		if(($maxAge > 604800 || $maxAge < 0) || ($maxUses > 100 || $maxUses < 0)) return null;
		$invite = new Invite();
		$invite->setServerId($channel->getServerId());
		$invite->setChannelId($channel->getId());
		$invite->setMaxAge($maxAge);
		$invite->setMaxUses($maxUses);
		$invite->setTemporary($temporary);
		return $invite;
	}

	/**
	 * Initialise if possible the given invite.
	 *
	 * @param Invite $invite
	 * @return PromiseInterface
	 * @see Api::createInviteModel() For creating a invite.
	 * @see Api::revokeInvite() For revoking an initialised invite.
	 */
	public function initialiseInvite(Invite $invite): PromiseInterface{
		$pk = new RequestInitialiseInvite();
		$pk->setInvite($invite);
		$this->plugin->writeOutboundData($pk);
		return ApiResolver::create($pk->getUID());
	}

	/**
	 * Revoke an initialised invite.
	 *
	 * @param Invite $invite
	 * @return PromiseInterface
	 */
	public function revokeInvite(Invite $invite): PromiseInterface{
		$pk = new RequestRevokeInvite();
		$pk->setInvite($invite);
		$this->plugin->writeOutboundData($pk);
		return ApiResolver::create($pk->getUID());
	}
}