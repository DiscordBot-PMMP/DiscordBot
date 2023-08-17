<?php

/*
 * DiscordBot, PocketMine-MP Plugin.
 *
 * Licensed under the Open Software License version 3.0 (OSL-3.0)
 * Copyright (C) 2020-present JaxkDev
 *
 * Twitter :: @JaxkDev
 * Discord :: JaxkDev
 * Email   :: JaxkDev@gmail.com
 */

namespace JaxkDev\DiscordBot\Plugin;

use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestAddReaction;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestAddRole;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestBroadcastTyping;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestCreateChannel;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestCreateRole;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestCreateWebhook;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestDeleteChannel;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestDeleteMessage;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestDeleteRole;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestDeleteWebhook;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestEditMessage;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestFetchMessage;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestFetchPinnedMessages;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestFetchWebhooks;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestBanMember;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestCreateInvite;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestKickMember;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestLeaveGuild;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestPinMessage;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestRemoveAllReactions;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestRemoveReaction;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestRemoveRole;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestUnbanMember;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestDeleteInvite;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestSendFile;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestSendMessage;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestUnpinMessage;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestUpdateBotPresence;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestUpdateChannel;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestUpdateNickname;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestUpdateRole;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestUpdateWebhook;
use JaxkDev\DiscordBot\Libs\React\Promise\PromiseInterface;
use JaxkDev\DiscordBot\Models\Channels\GuildChannel;
use JaxkDev\DiscordBot\Models\Emoji;
use JaxkDev\DiscordBot\Models\Messages\Message;
use JaxkDev\DiscordBot\Models\Messages\Webhook as WebhookMessage;
use JaxkDev\DiscordBot\Models\Permissions\RolePermissions;
use JaxkDev\DiscordBot\Models\Presence\Activity\Activity;
use JaxkDev\DiscordBot\Models\Presence\Presence;
use JaxkDev\DiscordBot\Models\Presence\Status;
use JaxkDev\DiscordBot\Models\Role;
use JaxkDev\DiscordBot\Models\User;
use JaxkDev\DiscordBot\Models\Webhook;
use JaxkDev\DiscordBot\Models\WebhookType;
use JaxkDev\DiscordBot\Plugin\Events\DiscordReady;
use pocketmine\event\EventPriority;
use function JaxkDev\DiscordBot\Libs\React\Promise\reject as rejectPromise;

/**
 * For internal and developers use for interacting with the discord bot.
 *
 * @see Main::getApi() To get instance.
 */
class Api{

    private Main $plugin;

    /** @var bool If the API is ready to be used. */
    private bool $ready = false;

    /** @var User The connected bot user */
    private User $bot_user;

    public function __construct(Main $plugin){
        $this->plugin = $plugin;
        try{
            $this->plugin->getServer()->getPluginManager()->registerEvent(DiscordReady::class, function(DiscordReady $event){
                $this->bot_user = $event->getBotUser();
                $this->ready = true;
                $this->plugin->getLogger()->debug("API Ready for requests.");
            }, EventPriority::LOWEST, $this->plugin, true);
        }catch(\Throwable $e){
            $this->plugin->getLogger()->logException($e);
        }
    }

    /**
     * @return bool Whether the API is ready to be used.
     */
    public function isReady(): bool{
        return $this->ready;
    }

    public function getBotUser(): User{
        return $this->bot_user;
    }

    /**
     * Creates a normal webhook inside a channel.
     *
     * @param string $name max 80chars, 'clyde' and 'discord' not allowed in name.
     * @param ?string $avatar_data If null, the default webhook avatar will be used. (see Utils::imageToDiscordData())
     * @see Utils::imageToDiscordData()
     *
     * @return PromiseInterface Resolves with a Webhook model.
     */
    public function createWebhook(string $guild_id, string $channel_id, string $name, ?string $avatar_data = null,
                                  ?string $reason = null): PromiseInterface{
        if(!$this->ready){
            return rejectPromise(new ApiRejection("API is not ready for requests."));
        }
        if(!Utils::validDiscordSnowflake($guild_id)){
            return rejectPromise(new ApiRejection("Webhook guild ID is invalid."));
        }
        if(!Utils::validDiscordSnowflake($channel_id)){
            return rejectPromise(new ApiRejection("Webhook channel ID is invalid."));
        }
        if($avatar_data !== null and !Utils::validImageData($avatar_data)){
            return rejectPromise(new ApiRejection("Webhook avatar data is invalid."));
        }
        $pk = new RequestCreateWebhook($guild_id, $channel_id, $name, $avatar_data, $reason);
        $this->plugin->writeOutboundData($pk);
        return ApiResolver::create($pk->getUID());
    }

    /**
     * Update a webhooks name or avatar hash.
     *
     * @return PromiseInterface Resolves with a Webhook model.
     */
    public function updateWebhook(Webhook $webhook, ?string $reason = null): PromiseInterface{
        if(!$this->ready){
            return rejectPromise(new ApiRejection("API is not ready for requests."));
        }
        if($webhook->getType() !== WebhookType::INCOMING){
            return rejectPromise(new ApiRejection("Only Incoming webhooks can be edited."));
        }
        if($webhook->getToken() === null){
            return rejectPromise(new ApiRejection("Webhook does not have a token, it cannot be edited before being created."));
        }
        if(!Utils::validDiscordSnowflake($webhook->getId())){
            return rejectPromise(new ApiRejection("Invalid webhook ID '{$webhook->getId()}'."));
        }
        $pk = new RequestUpdateWebhook($webhook, $reason);
        $this->plugin->writeOutboundData($pk);
        return ApiResolver::create($pk->getUID());
    }

    /**
     * Delete a webhook
     *
     * @return PromiseInterface Resolves with no data.
     */
    public function deleteWebhook(string $guild_id, string $channel_id, string $webhook_id, ?string $reason = null): PromiseInterface{
        if(!$this->ready){
            return rejectPromise(new ApiRejection("API is not ready for requests."));
        }
        if(!Utils::validDiscordSnowflake($guild_id)){
            return rejectPromise(new ApiRejection("Webhook guild ID is invalid."));
        }
        if(!Utils::validDiscordSnowflake($channel_id)){
            return rejectPromise(new ApiRejection("Invalid channel ID '$channel_id'."));
        }
        if(!Utils::validDiscordSnowflake($webhook_id)){
            return rejectPromise(new ApiRejection("Invalid webhook ID '$webhook_id'."));
        }
        $pk = new RequestDeleteWebhook($guild_id, $channel_id, $webhook_id, $reason);
        $this->plugin->writeOutboundData($pk);
        return ApiResolver::create($pk->getUID());
    }

    /**
     * Fetch all webhooks that are linked to a guild (all channels) or more specifically just a single channel
     *
     * @return PromiseInterface Resolves with an array of Webhook models.
     */
    public function fetchWebhooks(string $guild_id, ?string $channel_id = null): PromiseInterface{
        if(!$this->ready){
            return rejectPromise(new ApiRejection("API is not ready for requests."));
        }
        if(!Utils::validDiscordSnowflake($guild_id)){
            return rejectPromise(new ApiRejection("Invalid guild ID '$guild_id'."));
        }
        if($channel_id !== null and !Utils::validDiscordSnowflake($channel_id)){
            return rejectPromise(new ApiRejection("Invalid channel ID '$channel_id'."));
        }
        $pk = new RequestFetchWebhooks($guild_id, $channel_id);
        $this->plugin->writeOutboundData($pk);
        return ApiResolver::create($pk->getUID());
    }

    //createGuild will not be added due to security issues,
    //If you find a genuine use for createGuild please open an issue.

    /**
     * Leave a discord guild.
     *
     * @return PromiseInterface Resolves with no data.
     */
    public function leaveGuild(string $guild_id): PromiseInterface{
        if(!$this->ready){
            return rejectPromise(new ApiRejection("API is not ready for requests."));
        }
        if(!Utils::validDiscordSnowflake($guild_id)){
            return rejectPromise(new ApiRejection("Invalid guild ID '$guild_id'."));
        }
        $pk = new RequestLeaveGuild($guild_id);
        $this->plugin->writeOutboundData($pk);
        return ApiResolver::create($pk->getUID());
    }

    /**
     * Fetch all the pinned messages in a channel.
     *
     * Note you could fetch individual messages by id using fetchMessage from channel::pins but this is easier.
     *
     * @return PromiseInterface Resolves with an array of Message models.
     */
    public function fetchPinnedMessages(?string $guild_id, string $channel_id): PromiseInterface{
        if(!$this->ready){
            return rejectPromise(new ApiRejection("API is not ready for requests."));
        }
        if($guild_id !== null and !Utils::validDiscordSnowflake($guild_id)){
            return rejectPromise(new ApiRejection("Invalid guild ID '$guild_id'."));
        }
        if(!Utils::validDiscordSnowflake($channel_id)){
            return rejectPromise(new ApiRejection("Invalid channel ID '$channel_id'."));
        }
        $pk = new RequestFetchPinnedMessages($guild_id, $channel_id);
        $this->plugin->writeOutboundData($pk);
        return ApiResolver::create($pk->getUID());
    }

    /**
     * Fetch a message by ID.
     *
     * @return PromiseInterface Resolves with a Message model.
     */
    public function fetchMessage(?string $guild_id, string $channel_id, string $message_id): PromiseInterface{
        if(!$this->ready){
            return rejectPromise(new ApiRejection("API is not ready for requests."));
        }
        if($guild_id !== null and !Utils::validDiscordSnowflake($guild_id)){
            return rejectPromise(new ApiRejection("Invalid guild ID '$guild_id'."));
        }
        if(!Utils::validDiscordSnowflake($channel_id)){
            return rejectPromise(new ApiRejection("Invalid channel ID '$channel_id'."));
        }
        if(!Utils::validDiscordSnowflake($message_id)){
            return rejectPromise(new ApiRejection("Invalid message ID '$message_id'."));
        }
        $pk = new RequestFetchMessage($guild_id, $channel_id, $message_id);
        $this->plugin->writeOutboundData($pk);
        return ApiResolver::create($pk->getUID());
    }

    /**
     * Pin a message to the channel.
     *
     * @return PromiseInterface Resolves with no data.
     */
    public function pinMessage(?string $guild_id, string $channel_id, string $message_id, ?string $reason = null): PromiseInterface{
        if(!$this->ready){
            return rejectPromise(new ApiRejection("API is not ready for requests."));
        }
        if($guild_id !== null and !Utils::validDiscordSnowflake($guild_id)){
            return rejectPromise(new ApiRejection("Invalid guild ID '$guild_id'."));
        }
        if(!Utils::validDiscordSnowflake($channel_id)){
            return rejectPromise(new ApiRejection("Invalid channel ID '$channel_id'."));
        }
        if(!Utils::validDiscordSnowflake($message_id)){
            return rejectPromise(new ApiRejection("Invalid message ID '$message_id'."));
        }
        $pk = new RequestPinMessage($guild_id, $channel_id, $message_id, $reason);
        $this->plugin->writeOutboundData($pk);
        return ApiResolver::create($pk->getUID());
    }

    /**
     * Un-pin a message to the channel.
     *
     * @return PromiseInterface Resolves with no data.
     */
    public function unpinMessage(?string $guild_id, string $channel_id, string $message_id, ?string $reason = null): PromiseInterface{
        if(!$this->ready){
            return rejectPromise(new ApiRejection("API is not ready for requests."));
        }
        if($guild_id !== null and !Utils::validDiscordSnowflake($guild_id)){
            return rejectPromise(new ApiRejection("Invalid guild ID '$guild_id'."));
        }
        if(!Utils::validDiscordSnowflake($channel_id)){
            return rejectPromise(new ApiRejection("Invalid channel ID '$channel_id'."));
        }
        if(!Utils::validDiscordSnowflake($message_id)){
            return rejectPromise(new ApiRejection("Invalid message ID '$message_id'."));
        }
        $pk = new RequestUnpinMessage($guild_id, $channel_id, $message_id, $reason);
        $this->plugin->writeOutboundData($pk);
        return ApiResolver::create($pk->getUID());
    }

    /**
     * Create a role.
     *
     * (Note, icon_data and unicode_emoji only work with guilds with the ROLE_ICONS feature)
     *
     * @return PromiseInterface Resolves with Role model.
     */
    public function createRole(string $guild_id, string $name = "new role", RolePermissions $permissions = null,
                                     int $colour = 0, bool $hoist = false, ?string $icon_data = null,
                                     ?string $unicode_emoji = null, bool $mentionable = false, ?string $reason = null): PromiseInterface{
        if(!$this->ready){
            return rejectPromise(new ApiRejection("API is not ready for requests."));
        }
        if($icon_data !== null and !Utils::validImageData($icon_data)){
            return rejectPromise(new ApiRejection("Invalid icon data '$icon_data'."));
        }
        if(!Utils::validDiscordSnowflake($guild_id)){
            return rejectPromise(new ApiRejection("Invalid guild ID '$guild_id'."));
        }
        $pk = new RequestCreateRole($guild_id, $name, $permissions ?? new RolePermissions(), $colour, $hoist, $icon_data,
            $unicode_emoji, $mentionable, $reason);
        $this->plugin->writeOutboundData($pk);
        return ApiResolver::create($pk->getUID());
    }

    /**
     * Update an already created role, ID must be present.
     *
     * Note you cannot change the hoisted position of the 'everyone' role, or move any role higher than the bots highest role.
     *
     * If hoisted position changed, all roles that move to account for the change will emit an updated event.
     *
     * @return PromiseInterface Resolves with a Role model.
     */
    public function updateRole(Role $role, ?string $reason = null): PromiseInterface{
        if(!$this->ready){
            return rejectPromise(new ApiRejection("API is not ready for requests."));
        }
        if($role->getId() === null){
            return rejectPromise(new ApiRejection("Role must be created before being able to update (missing ID)."));
        }
        $pk = new RequestUpdateRole($role, $reason);
        $this->plugin->writeOutboundData($pk);
        return ApiResolver::create($pk->getUID());
    }

    /**
     * Delete a role.
     *
     * @return PromiseInterface Resolves with no data.
     */
    public function deleteRole(string $guild_id, string $role_id, ?string $reason = null): PromiseInterface{
        if(!$this->ready){
            return rejectPromise(new ApiRejection("API is not ready for requests."));
        }
        if(!Utils::validDiscordSnowflake($guild_id)){
            return rejectPromise(new ApiRejection("Invalid guild ID '$guild_id'."));
        }
        if(!Utils::validDiscordSnowflake($role_id)){
            return rejectPromise(new ApiRejection("Invalid role ID '$role_id'."));
        }
        $pk = new RequestDeleteRole($guild_id, $role_id, $reason);
        $this->plugin->writeOutboundData($pk);
        return ApiResolver::create($pk->getUID());
    }

    /**
     * Remove a role from a member.
     *
     * @return PromiseInterface Resolves with no data.
     */
    public function removeRole(string $guild_id, string $user_id, string $role_id, ?string $reason = null): PromiseInterface{
        if(!$this->ready){
            return rejectPromise(new ApiRejection("API is not ready for requests."));
        }
        if(!Utils::validDiscordSnowflake($guild_id)){
            return rejectPromise(new ApiRejection("Invalid guild ID '$guild_id'."));
        }
        if(!Utils::validDiscordSnowflake($user_id)){
            return rejectPromise(new ApiRejection("Invalid user ID '$user_id'."));
        }
        if(!Utils::validDiscordSnowflake($role_id)){
            return rejectPromise(new ApiRejection("Invalid role ID '$role_id'."));
        }
        $pk = new RequestRemoveRole($guild_id, $user_id, $role_id, $reason);
        $this->plugin->writeOutboundData($pk);
        return ApiResolver::create($pk->getUID());
    }

    /**
     * Give the member a role.
     *
     * @return PromiseInterface Resolves with no data.
     */
    public function addRole(string $guild_id, string $user_id, string $role_id, ?string $reason = null): PromiseInterface{
        if(!$this->ready){
            return rejectPromise(new ApiRejection("API is not ready for requests."));
        }
        if(!Utils::validDiscordSnowflake($guild_id)){
            return rejectPromise(new ApiRejection("Invalid guild ID '$guild_id'."));
        }
        if(!Utils::validDiscordSnowflake($user_id)){
            return rejectPromise(new ApiRejection("Invalid user ID '$user_id'."));
        }
        if(!Utils::validDiscordSnowflake($role_id)){
            return rejectPromise(new ApiRejection("Invalid role ID '$role_id'."));
        }
        $pk = new RequestAddRole($guild_id, $user_id, $role_id);
        $this->plugin->writeOutboundData($pk);
        return ApiResolver::create($pk->getUID());
    }

    /**
     * Remove a single reaction.
     *
     * @param string $emoji Raw emoji eg '👍' or 'a:NAME:ID' for custom animated or ':NAME:ID' for custom non-animated.
     *                      See Emoji::toApiString() for more info.
     * @see Emoji::toApiString()
     * @return PromiseInterface Resolves with no data.
     */
    public function removeReaction(?string $guild_id, string $channel_id, string $message_id, string $user_id,
                                   string $emoji): PromiseInterface{
        if(!$this->ready){
            return rejectPromise(new ApiRejection("API is not ready for requests."));
        }
        if($guild_id !== null and !Utils::validDiscordSnowflake($guild_id)){
            return rejectPromise(new ApiRejection("Invalid guild ID '$guild_id'."));
        }
        if(!Utils::validDiscordSnowflake($channel_id)){
            return rejectPromise(new ApiRejection("Invalid channel ID '$channel_id'."));
        }
        if(!Utils::validDiscordSnowflake($message_id)){
            return rejectPromise(new ApiRejection("Invalid message ID '$message_id'."));
        }
        if(!Utils::validDiscordSnowflake($user_id)){
            return rejectPromise(new ApiRejection("Invalid user ID '$user_id'."));
        }
        $pk = new RequestRemoveReaction($guild_id, $channel_id, $message_id, $user_id, $emoji);
        $this->plugin->writeOutboundData($pk);
        return ApiResolver::create($pk->getUID());
    }

    /**
     * Remove all reactions on a message.
     *
     * @param string|null $emoji If no emoji specified ALL reactions by EVERYONE will be deleted,
     *                           if specified everyone's reaction with that emoji will be removed.
     *
     *                           Raw emoji eg '👍' or 'a:NAME:ID' for custom animated or ':NAME:ID' for custom non-animated.
     *                           See Emoji::toApiString() for more info.
     * @see Emoji::toApiString()
     * @return PromiseInterface Resolves with no data.
     */
    public function removeAllReactions(?string $guild_id, string $channel_id, string $message_id, ?string $emoji = null): PromiseInterface{
        if(!$this->ready){
            return rejectPromise(new ApiRejection("API is not ready for requests."));
        }
        if($guild_id !== null and !Utils::validDiscordSnowflake($guild_id)){
            return rejectPromise(new ApiRejection("Invalid guild ID '$guild_id'."));
        }
        if(!Utils::validDiscordSnowflake($channel_id)){
            return rejectPromise(new ApiRejection("Invalid channel ID '$channel_id'."));
        }
        if(!Utils::validDiscordSnowflake($message_id)){
            return rejectPromise(new ApiRejection("Invalid message ID '$message_id'."));
        }
        $pk = new RequestRemoveAllReactions($guild_id, $channel_id, $message_id, $emoji);
        $this->plugin->writeOutboundData($pk);
        return ApiResolver::create($pk->getUID());
    }

    /**
     * Add a reaction to a message.
     *
     * Note, If you have already reacted with the emoji provided it will still respond with a successful promise resolution.
     *
     * @param string $emoji emoji string eg '👍' OR 'a:NAME:ID' for custom animated or ':NAME:ID' for custom non-animated.
     *                      See Emoji::toApiString() for more info.
     * @see Emoji::toApiString()
     * @return PromiseInterface Resolves with no data.
     */
    public function addReaction(?string $guild_id, string $channel_id, string $message_id, string $emoji): PromiseInterface{
        if(!$this->ready){
            return rejectPromise(new ApiRejection("API is not ready for requests."));
        }
        if($guild_id !== null and !Utils::validDiscordSnowflake($guild_id)){
            return rejectPromise(new ApiRejection("Invalid guild ID '$guild_id'."));
        }
        if(!Utils::validDiscordSnowflake($channel_id)){
            return rejectPromise(new ApiRejection("Invalid channel ID '$channel_id'."));
        }
        if(!Utils::validDiscordSnowflake($message_id)){
            return rejectPromise(new ApiRejection("Invalid message ID '$message_id'."));
        }
        $pk = new RequestAddReaction($guild_id, $channel_id, $message_id, $emoji);
        $this->plugin->writeOutboundData($pk);
        return ApiResolver::create($pk->getUID());
    }

    /**
     * "Generally bots should not implement this. However, if a bot is responding to a command and expects the computation
     * to take a few seconds, this endpoint may be called to let the user know that the bot is processing their message."
     * The 'typing' effect will last for 5s
     *
     * DO NOT ABUSE THIS.
     *
     * @return PromiseInterface Resolves with no data.
     */
    public function broadcastTyping(?string $guild_id, string $channel_id): PromiseInterface{
        if(!$this->ready){
            return rejectPromise(new ApiRejection("API is not ready for requests."));
        }
        if($guild_id !== null and !Utils::validDiscordSnowflake($guild_id)){
            return rejectPromise(new ApiRejection("Invalid guild ID '$guild_id'."));
        }
        if(!Utils::validDiscordSnowflake($channel_id)){
            return rejectPromise(new ApiRejection("Invalid channel ID '$channel_id'."));
        }
        $pk = new RequestBroadcastTyping($guild_id, $channel_id);
        $this->plugin->writeOutboundData($pk);
        return ApiResolver::create($pk->getUID());
    }

    /**
     * Sends a new presence to replace the current one the bot has.
     *
     * @return PromiseInterface Resolves with no data.
     */
    public function updateBotPresence(Status $status = Status::ONLINE, Activity $activity = null): PromiseInterface{
        if(!$this->ready){
            return rejectPromise(new ApiRejection("API is not ready for requests."));
        }
        $pk = new RequestUpdateBotPresence(new Presence($status, $activity === null ? [] : [$activity], null));
        $this->plugin->writeOutboundData($pk);
        return ApiResolver::create($pk->getUID());
    }

    /**
     * Attempt to ban a member.
     *
     * @param int $delete_message_seconds number of seconds to delete messages for, between 0 and 604800 (7 days)
     *
     * @return PromiseInterface Resolves with no data.
     */
    public function banMember(string $guild_id, string $user_id, int $delete_message_seconds = 0, ?string $reason = null): PromiseInterface{
        if(!$this->ready){
            return rejectPromise(new ApiRejection("API is not ready for requests."));
        }
        if(!Utils::validDiscordSnowflake($guild_id)){
            return rejectPromise(new ApiRejection("Invalid guild ID '$guild_id'."));
        }
        if(!Utils::validDiscordSnowflake($user_id)){
            return rejectPromise(new ApiRejection("Invalid user ID '$user_id'."));
        }
        if($delete_message_seconds < 0 or $delete_message_seconds > 604800){
            return rejectPromise(new ApiRejection("Delete message seconds must be between 0 and 604800 (7days)."));
        }
        $pk = new RequestBanMember($guild_id, $user_id, $delete_message_seconds, $reason);
        $this->plugin->writeOutboundData($pk);
        return ApiResolver::create($pk->getUID());
    }

    /**
     * Attempt to unban a member.
     *
     * @return PromiseInterface Resolves with no data.
     */
    public function unbanMember(string $guild_id, string $user_id, ?string $reason = null): PromiseInterface{
        if(!$this->ready){
            return rejectPromise(new ApiRejection("API is not ready for requests."));
        }
        if(!Utils::validDiscordSnowflake($guild_id)){
            return rejectPromise(new ApiRejection("Invalid guild ID '$guild_id'."));
        }
        if(!Utils::validDiscordSnowflake($user_id)){
            return rejectPromise(new ApiRejection("Invalid user ID '$user_id'."));
        }
        $pk = new RequestUnbanMember($guild_id, $user_id, $reason);
        $this->plugin->writeOutboundData($pk);
        return ApiResolver::create($pk->getUID());
    }

    /**
     * Attempt to kick a member.
     *
     * @return PromiseInterface Resolves with no data.
     */
    public function kickMember(string $guild_id, string $user_id, ?string $reason = null): PromiseInterface{
        if(!$this->ready){
            return rejectPromise(new ApiRejection("API is not ready for requests."));
        }
        if(!Utils::validDiscordSnowflake($guild_id)){
            return rejectPromise(new ApiRejection("Invalid guild ID '$guild_id'."));
        }
        if(!Utils::validDiscordSnowflake($user_id)){
            return rejectPromise(new ApiRejection("Invalid user ID '$user_id'."));
        }
        $pk = new RequestKickMember($guild_id, $user_id, $reason);
        $this->plugin->writeOutboundData($pk);
        return ApiResolver::create($pk->getUID());
    }

    /**
     * Sends the Message to discord.
     *
     * @return PromiseInterface Resolves with a Message model.
     */
    public function sendMessage(Message $message): PromiseInterface{
        if(!$this->ready){
            return rejectPromise(new ApiRejection("API is not ready for requests."));
        }
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
     * @param string      $file_path Full file path on disk.
     * @param string      $message   Optional text/message to send with the file
     * @param string|null $file_name Optional file_name to show in discord, Prefix with 'SPOILER_' to make as spoiler.
     * @return PromiseInterface Resolves with a Message model.
     */
    public function sendFile(string $guild_id, string $channel_id, string $file_path, string $message = "",
                             string $file_name = null): PromiseInterface{
        if(!$this->ready){
            return rejectPromise(new ApiRejection("API is not ready for requests."));
        }
        if(!Utils::validDiscordSnowflake($guild_id)){
            return rejectPromise(new ApiRejection("Invalid guild ID '$guild_id'."));
        }
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
        $pk = new RequestSendFile($guild_id, $channel_id, $file_name, $file_path, $message);
        $this->plugin->writeOutboundData($pk);
        return ApiResolver::create($pk->getUID());
    }

    /**
     * Edit a sent message.
     *
     * Note you can't convert a 'REPLY' message to a normal 'MESSAGE'.
     *
     * @return PromiseInterface Resolves with a Message model.
     */
    public function editMessage(Message $message): PromiseInterface{
        if(!$this->ready){
            return rejectPromise(new ApiRejection("API is not ready for requests."));
        }
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
     * @return PromiseInterface Resolves with no data.
     */
    public function deleteMessage(?string $guild_id, string $channel_id, string $message_id, ?string $reason = null): PromiseInterface{
        if(!$this->ready){
            return rejectPromise(new ApiRejection("API is not ready for requests."));
        }
        if($guild_id !== null and !Utils::validDiscordSnowflake($guild_id)){
            return rejectPromise(new ApiRejection("Invalid guild ID '$guild_id'."));
        }
        if(!Utils::validDiscordSnowflake($channel_id)){
            return rejectPromise(new ApiRejection("Invalid channel ID '$channel_id'."));
        }
        if(!Utils::validDiscordSnowflake($message_id)){
            return rejectPromise(new ApiRejection("Invalid message ID '$message_id'."));
        }
        $pk = new RequestDeleteMessage($guild_id, $channel_id, $message_id, $reason);
        $this->plugin->writeOutboundData($pk);
        return ApiResolver::create($pk->getUID());
    }

    /**
     * Create a guild channel.
     *
     * @return PromiseInterface Resolves with a Channel model of same type provided.
     */
    public function createChannel(GuildChannel $channel): PromiseInterface{ //TODO
        if(!$this->ready){
            return rejectPromise(new ApiRejection("API is not ready for requests."));
        }
        $pk = new RequestCreateChannel($channel);
        $this->plugin->writeOutboundData($pk);
        return ApiResolver::create($pk->getUID());
    }

    /**
     * Update a guild channel, ID Must be present.
     *
     * Note, Pins can NOT be updated directly.
     *
     * @see Api::pinMessage()
     * @see Api::unpinMessage()
     *
     * @return PromiseInterface Resolves with a Channel model of same type provided.
     */
    public function updateChannel(GuildChannel $channel): PromiseInterface{
        if(!$this->ready){
            return rejectPromise(new ApiRejection("API is not ready for requests."));
        }
        $pk = new RequestUpdateChannel($channel);
        $this->plugin->writeOutboundData($pk);
        return ApiResolver::create($pk->getUID());
    }

    /**
     * Delete a channel in a guild, you cannot delete private channels (DM's)
     *
     * @return PromiseInterface Resolves with no data.
     */
    public function deleteChannel(string $guild_id, string $channel_id, ?string $reason = null): PromiseInterface{
        if(!$this->ready){
            return rejectPromise(new ApiRejection("API is not ready for requests."));
        }
        if(!Utils::validDiscordSnowflake($guild_id)){
            return rejectPromise(new ApiRejection("Invalid guild ID '$guild_id'."));
        }
        if(!Utils::validDiscordSnowflake($channel_id)){
            return rejectPromise(new ApiRejection("Invalid channel ID '$channel_id'."));
        }
        $pk = new RequestDeleteChannel($guild_id, $channel_id, $reason);
        $this->plugin->writeOutboundData($pk);
        return ApiResolver::create($pk->getUID());
    }

    /**
     * Create a new Invite.
     *
     * @return PromiseInterface Resolves with an Invite model.
     */
    public function createInvite(string $guild_id, string $channel_id, int $max_age = 86400, int $max_uses = 0,
                                 bool $temporary = false, bool $unique = false, ?string $reason = null): PromiseInterface{
        if(!$this->ready){
            return rejectPromise(new ApiRejection("API is not ready for requests."));
        }
        if(!Utils::validDiscordSnowflake($guild_id)){
            return rejectPromise(new ApiRejection("Invalid guild ID '$guild_id'."));
        }
        if(!Utils::validDiscordSnowflake($channel_id)){
            return rejectPromise(new ApiRejection("Invalid channel ID '$channel_id'."));
        }
        if($max_age < 0 or $max_age > 86400){
            return rejectPromise(new ApiRejection("Max age must be between 0(never) and 604800seconds (7 days)."));
        }
        if($max_uses < 0 or $max_uses > 100){
            return rejectPromise(new ApiRejection("Max uses must be between 0(unlimited) and 100."));
        }
        $pk = new RequestCreateInvite($guild_id, $channel_id, $max_age, $max_uses, $temporary, $unique, $reason);
        $this->plugin->writeOutboundData($pk);
        return ApiResolver::create($pk->getUID());
    }

    /**
     * Delete an Invite.
     *
     * @return PromiseInterface Resolves with no data.
     */
    public function deleteInvite(string $guild_id, string $invite_code, ?string $reason = null): PromiseInterface{
        if(!$this->ready){
            return rejectPromise(new ApiRejection("API is not ready for requests."));
        }
        if(!Utils::validDiscordSnowflake($guild_id)){
            return rejectPromise(new ApiRejection("Invalid guild ID '$guild_id'."));
        }
        $pk = new RequestDeleteInvite($guild_id, $invite_code, $reason);
        $this->plugin->writeOutboundData($pk);
        return ApiResolver::create($pk->getUID());
    }

    /**
     * Update a members nickname (set to null to remove)
     *
     * @return PromiseInterface Resolves with no data.
     */
    public function updateNickname(string $guild_id, string $user_id, ?string $nickname = null, ?string $reason = null): PromiseInterface{
        if(!$this->ready){
            return rejectPromise(new ApiRejection("API is not ready for requests."));
        }
        if(!Utils::validDiscordSnowflake($guild_id)){
            return rejectPromise(new ApiRejection("Invalid guild ID '$guild_id'."));
        }
        if(!Utils::validDiscordSnowflake($user_id)){
            return rejectPromise(new ApiRejection("Invalid user ID '$user_id'."));
        }
        $pk = new RequestUpdateNickname($guild_id, $user_id, $nickname, $reason);
        $this->plugin->writeOutboundData($pk);
        return ApiResolver::create($pk->getUID());
    }
}