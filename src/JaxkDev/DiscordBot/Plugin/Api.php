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
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestUpdateNickname;
use JaxkDev\DiscordBot\Libs\React\Promise\PromiseInterface;
use JaxkDev\DiscordBot\Models\Activity;
use JaxkDev\DiscordBot\Models\Ban;
use JaxkDev\DiscordBot\Models\Invite;
use JaxkDev\DiscordBot\Models\Member;
use JaxkDev\DiscordBot\Models\Message;

/*
 * TODO:
 * - Send message (Embed/Reply)
 * - Edit message (Embed/Reply)
 * - Give role
 * - Take role
 * - Delete role
 * - Delete channel
 * - Update permissions (channel,role,member)
 * - Update channel
 * - update role
 * - Add Reaction
 * - Remove Reaction (advanced)
 * - Assert all fields are valid before sending packet.
 *
 * V3.x or v2.1+ (depending on BC):
 * - Register listener (messages, reactions etc)
 * - Unregister listener
 *
 * Test:
 * - ban
 * - unban
 * - kick
 *
 * Tested:
 * - Delete message
 * - Delete invite
 * - Update nickname
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

	/**
	 * Sends the new activity to replace the current one the bot has.
	 *
	 * @param Activity $activity
	 * @return PromiseInterface
	 */
	public function updateActivity(Activity $activity): PromiseInterface{
		$pk = new RequestUpdateActivity();
		$pk->setActivity($activity);
		$this->plugin->writeOutboundData($pk);
		return ApiResolver::create($pk->getUID());
	}

	/*public static function createBanModel(Member $member, ?string $reason = null, ?int $daysToDelete = null): ?Ban{
		if($daysToDelete !== null and ($daysToDelete < 0 or $daysToDelete > 7)) return null;
	}*/

	/**
	 * Attempt to ban a member.
	 *
	 * @param Ban $ban
	 * @return PromiseInterface
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

	/*public static function createMessageModel($channel, string $content): ?Message{
		if(strlen($content) > 2000) return null;
	}*/

	/**
	 * Sends the Message to discord.
	 *
	 * @param Message $message
	 * @return PromiseInterface
	 */
	public function sendMessage(Message $message): PromiseInterface{
		$pk = new RequestSendMessage();
		$pk->setMessage($message);
		$this->plugin->writeOutboundData($pk);
		return ApiResolver::create($pk->getUID());
	}

	/**
	 * Edit a sent message.
	 * @see Message::setContent() Set new content (aka edit) then call editMessage.
	 *
	 * @param Message $message
	 * @return PromiseInterface
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

	//public static function createInviteModel(ServerChannel $channel, int $maxAge, int $maxUses, bool $temporary = false): ?Invite{
		//if(($maxAge > 604800 || $maxAge < 0) || ($maxUses > 100 || $maxUses < 0)) return null;
	//}

	/**
	 * Initialise if possible the given invite.
	 *
	 * @param Invite $invite
	 * @return PromiseInterface
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

	/**
	 * Update a members nickname (set to null to remove)
	 * @see Member::setNickname() Set nickname then call updateNickname.
	 *
	 * @param Member $member
	 * @return PromiseInterface
	 */
	public function updateNickname(Member $member): PromiseInterface{
		$pk = new RequestUpdateNickname();
		$pk->setMember($member);
		$this->plugin->writeOutboundData($pk);
		return ApiResolver::create($pk->getUID());
	}
}