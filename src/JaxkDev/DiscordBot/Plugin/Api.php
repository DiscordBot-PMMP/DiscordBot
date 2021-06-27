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

use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestAddReaction;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestBroadcastTyping;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestDeleteChannel;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestDeleteMessage;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestDeleteRole;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestEditMessage;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestAddRole;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestInitialiseBan;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestInitialiseInvite;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestKickMember;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestLeaveServer;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestRemoveAllReactions;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestRemoveReaction;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestRemoveRole;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestRevokeInvite;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestSendMessage;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestRevokeBan;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestUpdateActivity;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestUpdateNickname;
use JaxkDev\DiscordBot\Libs\React\Promise\PromiseInterface;
use JaxkDev\DiscordBot\Models\Activity;
use JaxkDev\DiscordBot\Models\Ban;
use JaxkDev\DiscordBot\Models\Invite;
use JaxkDev\DiscordBot\Models\Messages\Message;
use JaxkDev\DiscordBot\Models\Messages\Reply;
use JaxkDev\DiscordBot\Models\Messages\Webhook;
use function JaxkDev\DiscordBot\Libs\React\Promise\reject as rejectPromise;

/*
 * TODO:
 * - Update Permissions (channel,role,member)
 * - Update Channel
 * - Update Role
 * - Create Role
 * - Create Channel
 *
 * V3.x or v2.1+ (depending on BC):
 * - Register listener (messages, reactions etc)
 * - Unregister listener
 *
 * Tested:
 * - Ban
 * - Unban
 * - Kick
 * - Delete Role
 * - Remove Role
 * - Add Role
 * - Remove Reactions(bulk/user)
 * - Remove Reaction(individual)
 * - Add Reaction
 * - Send Message(+Embed/+Reply)
 * - Edit Message(+Embed/+Reply)
 * - Delete Channel
 * - Delete Message
 * - Delete Invite
 * - Update Nickname
 * - Broadcast Typing
 */

/**
 * For internal and developers use for interacting with the discord bot.
 *
 * Model creation methods are static, note you can initialise your own models but
 * these functions ensure the required fields are present & valid for creation and use..
 *
 *
 * @see Main::getApi() To get instance.
 * @see Storage For all discord data.
 */
class Api{

	/** @var Main */
	private $plugin;

	public function __construct(Main $plugin){
		$this->plugin = $plugin;
	}

	//Technically we could make our own servers but that would mean bot with owner permissions and that could get dodgy.

	/**
	 * Leave a discord server.
	 *
	 * @param string $server_id
	 * @return PromiseInterface
	 */
	public function leaveServer(string $server_id): PromiseInterface{
		if(!Utils::validDiscordSnowflake($server_id)){
			return rejectPromise(new ApiRejection("Invalid server ID '$server_id'."));
		}
		$pk = new RequestLeaveServer();
		$pk->setServerId($server_id);
		$this->plugin->writeOutboundData($pk);
		return ApiResolver::create($pk->getUID());
	}

	/**
	 * Delete a role.
	 *
	 * @param string $server_id
	 * @param string $role_id
	 * @return PromiseInterface
	 */
	public function deleteRole(string $server_id, string $role_id): PromiseInterface{
		if(!Utils::validDiscordSnowflake($server_id)){
			return rejectPromise(new ApiRejection("Invalid server ID '$server_id'."));
		}
		if(!Utils::validDiscordSnowflake($role_id)){
			return rejectPromise(new ApiRejection("Invalid role ID '$role_id'."));
		}
		$pk = new RequestDeleteRole();
		$pk->setServerId($server_id);
		$pk->setRoleId($role_id);
		$this->plugin->writeOutboundData($pk);
		return ApiResolver::create($pk->getUID());
	}

	/**
	 * Remove a role from a member.
	 *
	 * @param string $member_id
	 * @param string $role_id
	 * @return PromiseInterface
	 */
	public function removeRole(string $member_id, string $role_id): PromiseInterface{
		[$sid, $uid] = explode(".", $member_id);
		if(!Utils::validDiscordSnowflake($sid) or !Utils::validDiscordSnowflake($uid)){
			return rejectPromise(new ApiRejection("Invalid member ID '$member_id'."));
		}
		if(!Utils::validDiscordSnowflake($role_id)){
			return rejectPromise(new ApiRejection("Invalid role ID '$role_id'."));
		}
		$pk = new RequestRemoveRole();
		$pk->setServerId($sid);
		$pk->setUserId($uid);
		$pk->setRoleId($role_id);
		$this->plugin->writeOutboundData($pk);
		return ApiResolver::create($pk->getUID());
	}

	/**
	 * Give the member a role.
	 *
	 * @param string $member_id
	 * @param string $role_id
	 * @return PromiseInterface
	 */
	public function addRole(string $member_id, string $role_id): PromiseInterface{
		[$sid, $uid] = explode(".", $member_id);
		if(!Utils::validDiscordSnowflake($sid) or !Utils::validDiscordSnowflake($uid)){
			return rejectPromise(new ApiRejection("Invalid member ID '$member_id'."));
		}
		if(!Utils::validDiscordSnowflake($role_id)){
			return rejectPromise(new ApiRejection("Invalid role ID '$role_id'."));
		}
		$pk = new RequestAddRole();
		$pk->setServerId($sid);
		$pk->setUserId($uid);
		$pk->setRoleId($role_id);
		$this->plugin->writeOutboundData($pk);
		return ApiResolver::create($pk->getUID());
	}

	/**
	 * Remove a single reaction.
	 *
	 * @param string $channel_id
	 * @param string $message_id
	 * @param string $user_id
	 * @param string $emoji Raw emoji eg '👍'
	 * @return PromiseInterface
	 */
	public function removeReaction(string $channel_id, string $message_id, string $user_id, string $emoji): PromiseInterface{
		if(!Utils::validDiscordSnowflake($channel_id)){
			return rejectPromise(new ApiRejection("Invalid channel ID '$channel_id'."));
		}
		if(!Utils::validDiscordSnowflake($message_id)){
			return rejectPromise(new ApiRejection("Invalid message ID '$message_id'."));
		}
		if(!Utils::validDiscordSnowflake($user_id)){
			return rejectPromise(new ApiRejection("Invalid user ID '$user_id'."));
		}
		$pk = new RequestRemoveReaction();
		$pk->setChannelId($channel_id);
		$pk->setUserId($user_id);
		$pk->setMessageId($message_id);
		$pk->setEmoji($emoji);
		$this->plugin->writeOutboundData($pk);
		return ApiResolver::create($pk->getUID());
	}

	/**
	 * Remove all reactions on a message.
	 *
	 * TODO HIGH PRIORITY, Investigate when emoji present it fails (Bad request) on PHP7 but works fine with PHP8
	 *
	 * @param string      $channel_id
	 * @param string      $message_id
	 * @param string|null $emoji If no emoji specified ALL reactions by EVERYONE will be deleted,
	 *                           if specified everyone's reaction with that emoji will be removed.
	 * @return PromiseInterface
	 */
	public function removeAllReactions(string $channel_id, string $message_id, ?string $emoji = null): PromiseInterface{
		if($emoji !== null and PHP_VERSION_ID < 80000){
			return rejectPromise(new ApiRejection("removeAllReactions with emoji does not currently work on PHP7 :("));
		}
		if(!Utils::validDiscordSnowflake($channel_id)){
			return rejectPromise(new ApiRejection("Invalid channel ID '$channel_id'."));
		}
		if(!Utils::validDiscordSnowflake($message_id)){
			return rejectPromise(new ApiRejection("Invalid message ID '$message_id'."));
		}
		$pk = new RequestRemoveAllReactions();
		$pk->setChannelId($channel_id);
		$pk->setMessageId($message_id);
		$pk->setEmoji($emoji);
		$this->plugin->writeOutboundData($pk);
		return ApiResolver::create($pk->getUID());
	}

	/**
	 * Add a reaction to a message.
	 *
	 * Note, If you have already reacted with the emoji provided it will still respond with a successful promise resolution.
	 *
	 * @param string $channel_id
	 * @param string $message_id
	 * @param string $emoji			MUST BE THE ACTUAL EMOJI CHARACTER, (Custom/Private emoji's not yet supported) eg '👍'
	 * @return PromiseInterface
	 */
	public function addReaction(string $channel_id, string $message_id, string $emoji): PromiseInterface{
		if(!Utils::validDiscordSnowflake($channel_id)){
			return rejectPromise(new ApiRejection("Invalid channel ID '$channel_id'."));
		}
		if(!Utils::validDiscordSnowflake($message_id)){
			return rejectPromise(new ApiRejection("Invalid message ID '$message_id'."));
		}
		$pk = new RequestAddReaction();
		$pk->setChannelId($channel_id);
		$pk->setMessageId($message_id);
		$pk->setEmoji($emoji);
		$this->plugin->writeOutboundData($pk);
		return ApiResolver::create($pk->getUID());
	}

	/**
	 * "Generally bots should not implement this. However, if a bot is responding to a command and expects the computation
	 * to take a few seconds, this endpoint may be called to let the user know that the bot is processing their message."
	 * The 'typing' effect will last for 10s
	 *
	 * DO NOT ABUSE THIS.
	 *
	 * @param string $channel_id
	 * @return PromiseInterface
	 */
	public function broadcastTyping(string $channel_id): PromiseInterface{
		if(!Utils::validDiscordSnowflake($channel_id)){
			return rejectPromise(new ApiRejection("Invalid channel ID '$channel_id'."));
		}
		$pk = new RequestBroadcastTyping();
		$pk->setChannelId($channel_id);
		$this->plugin->writeOutboundData($pk);
		return ApiResolver::create($pk->getUID());
	}

	/**
	 * Sends the new activity to replace the current one the bot has.
	 *
	 * @param Activity $activity
	 * @return PromiseInterface
	 */
	public function updateActivity(Activity $activity): PromiseInterface{
		//Dont know the activity message requirements.
		if(!in_array($activity->getStatus(), [Activity::STATUS_IDLE, Activity::STATUS_DND, Activity::STATUS_INVISIBLE, Activity::STATUS_OFFLINE, Activity::STATUS_ONLINE])){
			return rejectPromise(new ApiRejection("Invalid activity, status '{$activity->getStatus()}' is invalid."));
		}
		if(($t = $activity->getType()) !== null and !in_array($t, [Activity::TYPE_PLAYING, Activity::TYPE_STREAMING, Activity::TYPE_LISTENING, Activity::TYPE_WATCHING, Activity::TYPE_CUSTOM, Activity::TYPE_COMPETING])){
			return rejectPromise(new ApiRejection("Invalid activity, type '$t' is invalid."));
		}
		$pk = new RequestUpdateActivity();
		$pk->setActivity($activity);
		$this->plugin->writeOutboundData($pk);
		return ApiResolver::create($pk->getUID());
	}

	/**
	 * Attempt to ban a member.
	 *
	 * @param Ban $ban
	 * @return PromiseInterface
	 */
	public function initialiseBan(Ban $ban): PromiseInterface{
		if(!Utils::validDiscordSnowflake($ban->getServerId())){
			return rejectPromise(new ApiRejection("Invalid ban, server ID '{$ban->getServerId()}' is invalid."));
		}
		if(!Utils::validDiscordSnowflake($ban->getUserId())){
			return rejectPromise(new ApiRejection("Invalid ban, user ID '{$ban->getUserId()}' is invalid."));
		}
		if(($d = $ban->getDaysToDelete()) !== null and ($d < 0 or $d > 7)){
			return rejectPromise(new ApiRejection("Invalid ban, days to delete '$d' must be 0-7."));
		}
		$pk = new RequestInitialiseBan();
		$pk->setBan($ban);
		$this->plugin->writeOutboundData($pk);
		return ApiResolver::create($pk->getUID());
	}

	/**
	 * Attempt to revoke a ban.
	 *
	 * @param string $server_id
	 * @param string $user_id
	 * @return PromiseInterface
	 */
	public function revokeBan(string $server_id, string $user_id): PromiseInterface{
		if(!Utils::validDiscordSnowflake($server_id)){
			return rejectPromise(new ApiRejection("Invalid server ID '$server_id'."));
		}
		if(!Utils::validDiscordSnowflake($user_id)){
			return rejectPromise(new ApiRejection("Invalid user ID '$user_id'."));
		}
		$pk = new RequestRevokeBan();
		$pk->setServerId($server_id);
		$pk->setUserId($user_id);
		$this->plugin->writeOutboundData($pk);
		return ApiResolver::create($pk->getUID());
	}

	/**
	 * Attempt to kick a member.
	 *
	 * @param string $member_id
	 * @return PromiseInterface
	 */
	public function kickMember(string $member_id): PromiseInterface{
		[$sid, $uid] = explode(".", $member_id);
		if(!Utils::validDiscordSnowflake($sid) or !Utils::validDiscordSnowflake($uid)){
			return rejectPromise(new ApiRejection("Invalid member ID '$member_id'."));
		}
		$pk = new RequestKickMember();
		$pk->setServerId($sid);
		$pk->setUserId($uid);
		$this->plugin->writeOutboundData($pk);
		return ApiResolver::create($pk->getUID());
	}

	/**
	 * Sends the Message to discord.
	 *
	 * @param Message $message
	 * @return PromiseInterface
	 */
	public function sendMessage(Message $message): PromiseInterface{
		if($message instanceof Webhook){
			return rejectPromise(new ApiRejection("Webhook messages cannot be sent, only received."));
		}
		if(($s = strlen($message->getContent())) > 2000){
			return rejectPromise(new ApiRejection("Invalid content size '$s', max 2000."));
		}
		if(!Utils::validDiscordSnowflake($message->getChannelId())){
			return rejectPromise(new ApiRejection("Invalid message, channel_id '{$message->getChannelId()}' is invalid"));
		}
		foreach($message->getChannelsMentioned() as $cid){
			if(!Utils::validDiscordSnowflake($cid)){
				return rejectPromise(new ApiRejection("Invalid message, channels mentioned ID '$cid' is invalid."));
			}
		}
		foreach($message->getRolesMentioned() as $rid){
			if(!Utils::validDiscordSnowflake($rid)){
				return rejectPromise(new ApiRejection("Invalid message, roles mentioned ID '$rid' is invalid."));
			}
		}
		foreach($message->getUsersMentioned() as $uid){
			if(!Utils::validDiscordSnowflake($uid)){
				return rejectPromise(new ApiRejection("Invalid message, users mentioned ID '$uid' is invalid."));
			}
		}
		if($message instanceof Reply){
			if($message->getReferencedMessageId() === null or !Utils::validDiscordSnowflake($message->getReferencedMessageId())){
				return rejectPromise(new ApiRejection("Invalid message, referenced message ID '{$message->getReferencedMessageId()}' is invalid."));
			}
		}
		//TODO Embed checks.
		$pk = new RequestSendMessage();
		$pk->setMessage($message);
		$this->plugin->writeOutboundData($pk);
		return ApiResolver::create($pk->getUID());
	}

	/**
	 * Edit a sent message.
	 *
	 * Note you can't convert a 'REPLY' message to a normal 'MESSAGE'.
	 *
	 * @param Message $message
	 * @return PromiseInterface
	 * @see Api::sendMessage For sending a message.
	 * @see Api::deleteMessage For deleting a sent message.
	 */
	public function editMessage(Message $message): PromiseInterface{
		//TODO Checks.
		$pk = new RequestEditMessage();
		$pk->setMessage($message);
		$this->plugin->writeOutboundData($pk);
		return ApiResolver::create($pk->getUID());
	}

	/**
	 * Delete a sent message.
	 *
	 * @param string $message_id
	 * @param string $channel_id
	 * @return PromiseInterface
	 */
	public function deleteMessage(string $message_id, string $channel_id): PromiseInterface{
		if(!Utils::validDiscordSnowflake($channel_id)){
			return rejectPromise(new ApiRejection("Invalid channel ID '$channel_id'."));
		}
		if(!Utils::validDiscordSnowflake($message_id)){
			return rejectPromise(new ApiRejection("Invalid message ID '$message_id'."));
		}
		$pk = new RequestDeleteMessage();
		$pk->setMessageId($message_id);
		$pk->setChannelId($channel_id);
		$this->plugin->writeOutboundData($pk);
		return ApiResolver::create($pk->getUID());
	}

	/**
	 * Delete a channel in a server, you cannot delete private channels (DM's)
	 *
	 * @param string $server_id
	 * @param string $channel_id
	 * @return PromiseInterface
	 */
	public function deleteChannel(string $server_id, string $channel_id): PromiseInterface{
		if(!Utils::validDiscordSnowflake($server_id)){
			return rejectPromise(new ApiRejection("Invalid server ID '$server_id'."));
		}
		if(!Utils::validDiscordSnowflake($channel_id)){
			return rejectPromise(new ApiRejection("Invalid channel ID '$channel_id'."));
		}
		$pk = new RequestDeleteChannel();
		$pk->setServerId($server_id);
		$pk->setChannelId($channel_id);
		$this->plugin->writeOutboundData($pk);
		return ApiResolver::create($pk->getUID());
	}

	/**
	 * Initialise if possible the given invite.
	 *
	 * @param Invite $invite
	 * @return PromiseInterface
	 * @see Api::revokeInvite() For revoking an initialised invite.
	 */
	public function initialiseInvite(Invite $invite): PromiseInterface{
		if(!Utils::validDiscordSnowflake($invite->getServerId())){
			return rejectPromise(new ApiRejection("Invalid server ID '{$invite->getServerId()}' in Invite."));
		}
		if(!Utils::validDiscordSnowflake($invite->getChannelId())){
			return rejectPromise(new ApiRejection("Invalid channel ID '{$invite->getChannelId()}' in Invite."));
		}
		if(($u = $invite->getMaxUses()) > 100 or $u < 0){
			return rejectPromise(new ApiRejection("Invalid max uses '$u' in Invite, max uses must be 0-100."));
		}
		if(($a = $invite->getMaxAge()) > 604800 or $a < 0){
			return rejectPromise(new ApiRejection("Invalid max age '$a' in Invite, max age must be 0-604800"));
		}
		$pk = new RequestInitialiseInvite();
		$pk->setInvite($invite);
		$this->plugin->writeOutboundData($pk);
		return ApiResolver::create($pk->getUID());
	}

	/**
	 * Revoke an initialised invite.
	 *
	 * @param string $server_id
	 * @param string $invite_code
	 * @return PromiseInterface
	 */
	public function revokeInvite(string $server_id, string $invite_code): PromiseInterface{
		if(!Utils::validDiscordSnowflake($server_id)){
			return rejectPromise(new ApiRejection("Invalid server ID '$server_id'."));
		}
		$pk = new RequestRevokeInvite();
		$pk->setServerId($server_id);
		$pk->setInviteCode($invite_code);
		$this->plugin->writeOutboundData($pk);
		return ApiResolver::create($pk->getUID());
	}

	/**
	 * Update a members nickname (set to null to remove)
	 *
	 * @param string $member_id
	 * @param null|string $nickname Null to remove nickname.
	 * @return PromiseInterface
	 */
	public function updateNickname(string $member_id, ?string $nickname = null): PromiseInterface{
		[$sid, $uid] = explode(".", $member_id);
		if(!Utils::validDiscordSnowflake($sid) or !Utils::validDiscordSnowflake($uid)){
			return rejectPromise(new ApiRejection("Invalid member ID '$member_id'."));
		}
		$pk = new RequestUpdateNickname();
		$pk->setServerId($sid);
		$pk->setUserId($uid);
		$pk->setNickname($nickname);
		$this->plugin->writeOutboundData($pk);
		return ApiResolver::create($pk->getUID());
	}
}