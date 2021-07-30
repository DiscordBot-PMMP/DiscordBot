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
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestCreateChannel;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestCreateRole;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestCreateWebhook;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestDeleteChannel;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestDeleteMessage;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestDeleteRole;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestDeleteWebhook;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestEditMessage;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestAddRole;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestFetchMessage;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestFetchPinnedMessages;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestFetchWebhooks;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestInitialiseBan;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestInitialiseInvite;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestKickMember;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestLeaveServer;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestPinMessage;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestRemoveAllReactions;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestRemoveReaction;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestRemoveRole;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestRevokeInvite;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestSendFile;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestSendMessage;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestRevokeBan;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestUnpinMessage;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestUpdatePresence;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestUpdateChannel;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestUpdateNickname;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestUpdateRole;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestUpdateWebhook;
use JaxkDev\DiscordBot\Libs\React\Promise\PromiseInterface;
use JaxkDev\DiscordBot\Models\Activity;
use JaxkDev\DiscordBot\Models\Ban;
use JaxkDev\DiscordBot\Models\Channels\ServerChannel;
use JaxkDev\DiscordBot\Models\Invite;
use JaxkDev\DiscordBot\Models\Member;
use JaxkDev\DiscordBot\Models\Messages\Message;
use JaxkDev\DiscordBot\Models\Messages\Webhook as WebhookMessage;
use JaxkDev\DiscordBot\Models\Webhook;
use JaxkDev\DiscordBot\Models\Role;
use function JaxkDev\DiscordBot\Libs\React\Promise\reject as rejectPromise;

/**
 * For internal and developers use for interacting with the discord bot.
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
     * Creates a normal webhook inside a channel.
     *
     * @param Webhook $webhook
     * @return PromiseInterface Resolves with a Webhook model.
     */
    public function createWebhook(Webhook $webhook): PromiseInterface{
        if($webhook->getType() !== Webhook::TYPE_NORMAL){
            return rejectPromise(new ApiRejection("Only normal webhooks can be created right now."));
        }
        if(!Utils::validDiscordSnowflake($webhook->getChannelId())){
            return rejectPromise(new ApiRejection("Webhook channel ID is invalid."));
        }
        if($webhook->getId() !== null or $webhook->getToken() !== null){
            return rejectPromise(new ApiRejection("Webhook already has an ID/token, it cannot be created twice."));
        }
        $pk = new RequestCreateWebhook($webhook);
        $this->plugin->writeOutboundData($pk);
        return ApiResolver::create($pk->getUID());
    }

    /**
     * Update a webhooks name or avatar.
     *
     * @param Webhook $webhook
     * @return PromiseInterface Resolves with a Webhook model.
     */
    public function updateWebhook(Webhook $webhook): PromiseInterface{
        if($webhook->getType() !== Webhook::TYPE_NORMAL){
            return rejectPromise(new ApiRejection("Only normal webhooks can be edited right now."));
        }
        if($webhook->getId() === null or $webhook->getToken() === null){
            return rejectPromise(new ApiRejection("Webhook does not have an ID/token, it cannot be edited before being created."));
        }
        if(!Utils::validDiscordSnowflake($webhook->getId())){
            return rejectPromise(new ApiRejection("Invalid webhook ID '{$webhook->getId()}'."));
        }
        $pk = new RequestUpdateWebhook($webhook);
        $this->plugin->writeOutboundData($pk);
        return ApiResolver::create($pk->getUID());
    }

    /**
     * Delete a webhook
     *
     * @param string $channel_id
     * @param string $webhook_id
     * @return PromiseInterface Resolves with no data.
     */
    public function deleteWebhook(string $channel_id, string $webhook_id): PromiseInterface{
        if(!Utils::validDiscordSnowflake($webhook_id)){
            return rejectPromise(new ApiRejection("Invalid webhook ID '$webhook_id'."));
        }
        if(!Utils::validDiscordSnowflake($channel_id)){
            return rejectPromise(new ApiRejection("Invalid channel ID '$channel_id'."));
        }
        $pk = new RequestDeleteWebhook($channel_id, $webhook_id);
        $this->plugin->writeOutboundData($pk);
        return ApiResolver::create($pk->getUID());
    }

    //createServer will not be added due to security issues.

    /**
     * Leave a discord server.
     *
     * @param string $server_id
     * @return PromiseInterface Resolves with no data.
     */
    public function leaveServer(string $server_id): PromiseInterface{
        if(!Utils::validDiscordSnowflake($server_id)){
            return rejectPromise(new ApiRejection("Invalid server ID '$server_id'."));
        }
        $pk = new RequestLeaveServer($server_id);
        $this->plugin->writeOutboundData($pk);
        return ApiResolver::create($pk->getUID());
    }

    /**
     * Fetch all webhooks that are linked to a channel.
     *
     * @param string $channel_id
     * @return PromiseInterface Resolves with an array of Webhook models.
     */
    public function fetchWebhooks(string $channel_id): PromiseInterface{
        if(!Utils::validDiscordSnowflake($channel_id)){
            return rejectPromise(new ApiRejection("Invalid channel ID '$channel_id'."));
        }
        $pk = new RequestFetchWebhooks($channel_id);
        $this->plugin->writeOutboundData($pk);
        return ApiResolver::create($pk->getUID());
    }

    /**
     * Fetch all the pinned messages in a channel.
     *
     * Note you could fetch individual messages by id using fetchMessage from channel::pins but this is easier.
     *
     * @param string $channel_id
     * @return PromiseInterface Resolves with an array of Message models.
     */
    public function fetchPinnedMessages(string $channel_id): PromiseInterface{
        if(!Utils::validDiscordSnowflake($channel_id)){
            return rejectPromise(new ApiRejection("Invalid channel ID '$channel_id'."));
        }
        $pk = new RequestFetchPinnedMessages($channel_id);
        $this->plugin->writeOutboundData($pk);
        return ApiResolver::create($pk->getUID());
    }

    /**
     * Fetch a message by ID.
     *
     * @param string $channel_id
     * @param string $message_id
     * @return PromiseInterface Resolves with a Message model.
     */
    public function fetchMessage(string $channel_id, string $message_id): PromiseInterface{
        if(!Utils::validDiscordSnowflake($channel_id)){
            return rejectPromise(new ApiRejection("Invalid channel ID '$channel_id'."));
        }
        if(!Utils::validDiscordSnowflake($message_id)){
            return rejectPromise(new ApiRejection("Invalid message ID '$message_id'."));
        }
        $pk = new RequestFetchMessage($channel_id, $message_id);
        $this->plugin->writeOutboundData($pk);
        return ApiResolver::create($pk->getUID());
    }

    /**
     * Pin a message to the channel.
     *
     * @param string $channel_id
     * @param string $message_id
     * @return PromiseInterface Resolves with no data.
     */
    public function pinMessage(string $channel_id, string $message_id): PromiseInterface{
        if(!Utils::validDiscordSnowflake($channel_id)){
            return rejectPromise(new ApiRejection("Invalid channel ID '$channel_id'."));
        }
        if(!Utils::validDiscordSnowflake($message_id)){
            return rejectPromise(new ApiRejection("Invalid message ID '$message_id'."));
        }
        $pk = new RequestPinMessage($channel_id, $message_id);
        $this->plugin->writeOutboundData($pk);
        return ApiResolver::create($pk->getUID());
    }

    /**
     * Un-pin a message to the channel.
     *
     * @param string $channel_id
     * @param string $message_id
     * @return PromiseInterface Resolves with no data.
     */
    public function unpinMessage(string $channel_id, string $message_id): PromiseInterface{
        if(!Utils::validDiscordSnowflake($channel_id)){
            return rejectPromise(new ApiRejection("Invalid channel ID '$channel_id'."));
        }
        if(!Utils::validDiscordSnowflake($message_id)){
            return rejectPromise(new ApiRejection("Invalid message ID '$message_id'."));
        }
        $pk = new RequestUnpinMessage($channel_id, $message_id);
        $this->plugin->writeOutboundData($pk);
        return ApiResolver::create($pk->getUID());
    }

    /**
     * Create a role.
     *
     * @param Role $role
     * @return PromiseInterface Resolves with Role model.
     */
    public function createRole(Role $role): PromiseInterface{
        $pk = new RequestCreateRole($role);
        $this->plugin->writeOutboundData($pk);
        return ApiResolver::create($pk->getUID());
    }

    /**
     * Update a already created role, ID must be present.
     *
     * Note you cannot change the hoisted position of the 'everyone' role, or move any role higher than the bots highest role.
     *
     * If hoisted position changed, all roles that move to account for the change will emit an updated event.
     *
     * @param Role $role
     * @return PromiseInterface Resolves with a Role model.
     */
    public function updateRole(Role $role): PromiseInterface{
        if($role->getId() === null){
            return rejectPromise(new ApiRejection("Role ID must be present when updating."));
        }
        $pk = new RequestUpdateRole($role);
        $this->plugin->writeOutboundData($pk);
        return ApiResolver::create($pk->getUID());
    }

    /**
     * Delete a role.
     *
     * @param string $server_id
     * @param string $role_id
     * @return PromiseInterface Resolves with no data.
     */
    public function deleteRole(string $server_id, string $role_id): PromiseInterface{
        if(!Utils::validDiscordSnowflake($server_id)){
            return rejectPromise(new ApiRejection("Invalid server ID '$server_id'."));
        }
        if(!Utils::validDiscordSnowflake($role_id)){
            return rejectPromise(new ApiRejection("Invalid role ID '$role_id'."));
        }
        $pk = new RequestDeleteRole($server_id, $role_id);
        $this->plugin->writeOutboundData($pk);
        return ApiResolver::create($pk->getUID());
    }

    /**
     * Remove a role from a member.
     *
     * @param string $member_id
     * @param string $role_id
     * @return PromiseInterface Resolves with no data.
     */
    public function removeRole(string $member_id, string $role_id): PromiseInterface{
        [$sid, $uid] = explode(".", $member_id);
        if(!Utils::validDiscordSnowflake($sid) or !Utils::validDiscordSnowflake($uid)){
            return rejectPromise(new ApiRejection("Invalid member ID '$member_id'."));
        }
        if(!Utils::validDiscordSnowflake($role_id)){
            return rejectPromise(new ApiRejection("Invalid role ID '$role_id'."));
        }
        $pk = new RequestRemoveRole($sid, $uid, $role_id);
        $this->plugin->writeOutboundData($pk);
        return ApiResolver::create($pk->getUID());
    }

    /**
     * Give the member a role.
     *
     * @param string $member_id
     * @param string $role_id
     * @return PromiseInterface Resolves with no data.
     */
    public function addRole(string $member_id, string $role_id): PromiseInterface{
        [$sid, $uid] = explode(".", $member_id);
        if(!Utils::validDiscordSnowflake($sid) or !Utils::validDiscordSnowflake($uid)){
            return rejectPromise(new ApiRejection("Invalid member ID '$member_id'."));
        }
        if(!Utils::validDiscordSnowflake($role_id)){
            return rejectPromise(new ApiRejection("Invalid role ID '$role_id'."));
        }
        $pk = new RequestAddRole($sid, $uid, $role_id);
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
     * @return PromiseInterface Resolves with no data.
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
        $pk = new RequestRemoveReaction($channel_id, $message_id, $user_id, $emoji);
        $this->plugin->writeOutboundData($pk);
        return ApiResolver::create($pk->getUID());
    }

    /**
     * Remove all reactions on a message.
     *
     * @param string      $channel_id
     * @param string      $message_id
     * @param string|null $emoji If no emoji specified ALL reactions by EVERYONE will be deleted,
     *                           if specified everyone's reaction with that emoji will be removed.
     * @return PromiseInterface Resolves with no data.
     */
    public function removeAllReactions(string $channel_id, string $message_id, ?string $emoji = null): PromiseInterface{
        if(!Utils::validDiscordSnowflake($channel_id)){
            return rejectPromise(new ApiRejection("Invalid channel ID '$channel_id'."));
        }
        if(!Utils::validDiscordSnowflake($message_id)){
            return rejectPromise(new ApiRejection("Invalid message ID '$message_id'."));
        }
        $pk = new RequestRemoveAllReactions($channel_id, $message_id, $emoji);
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
     * @param string $emoji            MUST BE THE ACTUAL EMOJI CHARACTER, (Custom/Private emoji's not yet supported) eg '👍'
     * @return PromiseInterface Resolves with no data.
     */
    public function addReaction(string $channel_id, string $message_id, string $emoji): PromiseInterface{
        if(!Utils::validDiscordSnowflake($channel_id)){
            return rejectPromise(new ApiRejection("Invalid channel ID '$channel_id'."));
        }
        if(!Utils::validDiscordSnowflake($message_id)){
            return rejectPromise(new ApiRejection("Invalid message ID '$message_id'."));
        }
        $pk = new RequestAddReaction($channel_id, $message_id, $emoji);
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
     * @return PromiseInterface Resolves with no data.
     */
    public function broadcastTyping(string $channel_id): PromiseInterface{
        if(!Utils::validDiscordSnowflake($channel_id)){
            return rejectPromise(new ApiRejection("Invalid channel ID '$channel_id'."));
        }
        $pk = new RequestBroadcastTyping($channel_id);
        $this->plugin->writeOutboundData($pk);
        return ApiResolver::create($pk->getUID());
    }

    /**
     * Sends a new presence to replace the current one the bot has.
     *
     * @param Activity $activity
     * @param string $status See Member::STATUS_ constants.
     * @return PromiseInterface Resolves with no data.
     */
    public function updateBotPresence(Activity $activity, string $status = Member::STATUS_ONLINE): PromiseInterface{
        if(!in_array($status, [Member::STATUS_ONLINE, Member::STATUS_IDLE, Member::STATUS_OFFLINE, Member::STATUS_DND])){
            return rejectPromise(new ApiRejection("Invalid status '$status'."));
        }
        $pk = new RequestUpdatePresence($activity, $status);
        $this->plugin->writeOutboundData($pk);
        return ApiResolver::create($pk->getUID());
    }

    /**
     * Attempt to ban a member.
     *
     * @param Ban $ban
     * @return PromiseInterface Resolves with no data.
     */
    public function initialiseBan(Ban $ban): PromiseInterface{
        $pk = new RequestInitialiseBan($ban);
        $this->plugin->writeOutboundData($pk);
        return ApiResolver::create($pk->getUID());
    }

    /**
     * Attempt to revoke a ban.
     *
     * @param string $server_id
     * @param string $user_id
     * @return PromiseInterface Resolves with no data.
     */
    public function revokeBan(string $server_id, string $user_id): PromiseInterface{
        if(!Utils::validDiscordSnowflake($server_id)){
            return rejectPromise(new ApiRejection("Invalid server ID '$server_id'."));
        }
        if(!Utils::validDiscordSnowflake($user_id)){
            return rejectPromise(new ApiRejection("Invalid user ID '$user_id'."));
        }
        $pk = new RequestRevokeBan($server_id, $user_id);
        $this->plugin->writeOutboundData($pk);
        return ApiResolver::create($pk->getUID());
    }

    /**
     * Attempt to kick a member.
     *
     * @param string $member_id
     * @return PromiseInterface Resolves with no data.
     */
    public function kickMember(string $member_id): PromiseInterface{
        [$sid, $uid] = explode(".", $member_id);
        if(!Utils::validDiscordSnowflake($sid) or !Utils::validDiscordSnowflake($uid)){
            return rejectPromise(new ApiRejection("Invalid member ID '$member_id'."));
        }
        $pk = new RequestKickMember($sid, $uid);
        $this->plugin->writeOutboundData($pk);
        return ApiResolver::create($pk->getUID());
    }

    /**
     * Sends the Message to discord.
     *
     * @param Message $message
     * @return PromiseInterface Resolves with a Message model.
     */
    public function sendMessage(Message $message): PromiseInterface{
        if($message instanceof WebhookMessage){
            //You can execute webhooks yourself using Api::fetchWebhooks() and use its token.
            return rejectPromise(new ApiRejection("Webhook messages cannot be sent, only received."));
        }
        if(strlen($message->getContent()) > 2000){
            return rejectPromise(new ApiRejection("Message content cannot be larger than 2000 characters for bots."));
        }
        $pk = new RequestSendMessage($message);
        $this->plugin->writeOutboundData($pk);
        return ApiResolver::create($pk->getUID());
    }

    /**
     * Send a local file to a text channel.
     *
     * @param string      $channel_id
     * @param string      $file_path Full file path on disk.
     * @param string      $message   Optional text/message to send with the file
     * @param string|null $file_name Optional file_name to show in discord, Prefix with 'SPOILER_' to make as spoiler.
     * @return PromiseInterface Resolves with a Message model.
     */
    public function sendFile(string $channel_id, string $file_path, string $message = "", string $file_name = null): PromiseInterface{
        if(!Utils::validDiscordSnowflake($channel_id)){
            return rejectPromise(new ApiRejection("Invalid channel ID '$channel_id'."));
        }
        if(!is_file($file_path)){
            return rejectPromise(new ApiRejection("Invalid file path '$file_path' no such file exists."));
        }
        if(strlen($message) > 2000){
            return rejectPromise(new ApiRejection("Message cannot be larger than 2000 characters for bots."));
        }
        if($file_name === null){
            $file_name = basename($file_path);
        }
        $pk = new RequestSendFile($channel_id, $file_name, $file_path, $message);
        $this->plugin->writeOutboundData($pk);
        return ApiResolver::create($pk->getUID());
    }

    /**
     * Edit a sent message.
     *
     * Note you can't convert a 'REPLY' message to a normal 'MESSAGE'.
     *
     * @param Message $message
     * @return PromiseInterface Resolves with a Message model.
     */
    public function editMessage(Message $message): PromiseInterface{
        if($message->getId() === null){
            return rejectPromise(new ApiRejection("Message must have a valid ID to be able to edit it."));
        }
        if(strlen($message->getContent()) > 2000){
            return rejectPromise(new ApiRejection("Message content cannot be larger than 2000 characters for bots."));
        }
        $pk = new RequestEditMessage($message);
        $this->plugin->writeOutboundData($pk);
        return ApiResolver::create($pk->getUID());
    }

    /**
     * Delete a sent message.
     *
     * @param string $message_id
     * @param string $channel_id
     * @return PromiseInterface Resolves with no data.
     */
    public function deleteMessage(string $message_id, string $channel_id): PromiseInterface{
        if(!Utils::validDiscordSnowflake($channel_id)){
            return rejectPromise(new ApiRejection("Invalid channel ID '$channel_id'."));
        }
        if(!Utils::validDiscordSnowflake($message_id)){
            return rejectPromise(new ApiRejection("Invalid message ID '$message_id'."));
        }
        $pk = new RequestDeleteMessage($message_id, $channel_id);
        $this->plugin->writeOutboundData($pk);
        return ApiResolver::create($pk->getUID());
    }

    /**
     * Create a server channel.
     *
     * @param ServerChannel $channel CategoryChannel, TextChannel or VoiceChannel.
     * @return PromiseInterface Resolves with a Channel model of same type provided.
     */
    public function createChannel(ServerChannel $channel): PromiseInterface{
        $pk = new RequestCreateChannel($channel);
        $this->plugin->writeOutboundData($pk);
        return ApiResolver::create($pk->getUID());
    }

    /**
     * Update a server channel, ID Must be present.
     *
     * Note, Pins can NOT be updated directly.
     *
     * @see Api::pinMessage()
     * @see Api::unpinMessage()
     *
     * @param ServerChannel $channel
     * @return PromiseInterface Resolves with a Channel model of same type provided.
     */
    public function updateChannel(ServerChannel $channel): PromiseInterface{
        $pk = new RequestUpdateChannel($channel);
        $this->plugin->writeOutboundData($pk);
        return ApiResolver::create($pk->getUID());
    }

    /**
     * Delete a channel in a server, you cannot delete private channels (DM's)
     *
     * @param string $server_id
     * @param string $channel_id
     * @return PromiseInterface Resolves with no data.
     */
    public function deleteChannel(string $server_id, string $channel_id): PromiseInterface{
        if(!Utils::validDiscordSnowflake($server_id)){
            return rejectPromise(new ApiRejection("Invalid server ID '$server_id'."));
        }
        if(!Utils::validDiscordSnowflake($channel_id)){
            return rejectPromise(new ApiRejection("Invalid channel ID '$channel_id'."));
        }
        $pk = new RequestDeleteChannel($server_id, $channel_id);
        $this->plugin->writeOutboundData($pk);
        return ApiResolver::create($pk->getUID());
    }

    /**
     * Initialise if possible the given invite.
     *
     * @param Invite $invite
     * @return PromiseInterface Resolves with a Invite model.
     */
    public function initialiseInvite(Invite $invite): PromiseInterface{
        $pk = new RequestInitialiseInvite($invite);
        $this->plugin->writeOutboundData($pk);
        return ApiResolver::create($pk->getUID());
    }

    /**
     * Revoke an initialised invite.
     *
     * @param string $server_id
     * @param string $invite_code
     * @return PromiseInterface Resolves with a Invite model.
     */
    public function revokeInvite(string $server_id, string $invite_code): PromiseInterface{
        if(!Utils::validDiscordSnowflake($server_id)){
            return rejectPromise(new ApiRejection("Invalid server ID '$server_id'."));
        }
        $pk = new RequestRevokeInvite($server_id, $invite_code);
        $this->plugin->writeOutboundData($pk);
        return ApiResolver::create($pk->getUID());
    }

    /**
     * Update a members nickname (set to null to remove)
     *
     * @param string $member_id
     * @param null|string $nickname Null to remove nickname.
     * @return PromiseInterface Resolves with no data.
     */
    public function updateNickname(string $member_id, ?string $nickname = null): PromiseInterface{
        [$sid, $uid] = explode(".", $member_id);
        if(!Utils::validDiscordSnowflake($sid) or !Utils::validDiscordSnowflake($uid)){
            return rejectPromise(new ApiRejection("Invalid member ID '$member_id'."));
        }
        $pk = new RequestUpdateNickname($sid, $uid, $nickname);
        $this->plugin->writeOutboundData($pk);
        return ApiResolver::create($pk->getUID());
    }
}