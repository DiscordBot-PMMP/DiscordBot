<?php

/*
 * DiscordBot, PocketMine-MP Plugin.
 *
 * Licensed under the Open Software License version 3.0 (OSL-3.0)
 * Copyright (C) 2020-present JaxkDev
 *
 * Discord :: JaxkDev
 * Email   :: JaxkDev@gmail.com
 */

namespace JaxkDev\DiscordBot\Plugin;

use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestAddReaction;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestAddRole;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestBanMember;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestBroadcastTyping;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestBulkDeleteMessages;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestCreateChannel;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestCreateInvite;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestCreateRole;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestCreateThread;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestCreateThreadFromMessage;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestCreateWebhook;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestDeleteChannel;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestDeleteInvite;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestDeleteMessage;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestDeleteRole;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestDeleteWebhook;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestEditMessage;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestFetchBans;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestFetchChannel;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestFetchChannels;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestFetchGuild;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestFetchGuilds;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestFetchInvites;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestFetchMember;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestFetchMembers;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestFetchMessage;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestFetchPinnedMessages;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestFetchRole;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestFetchRoles;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestFetchUser;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestFetchUsers;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestFetchWebhooks;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestInteractionRespondWithAutocomplete;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestInteractionRespondWithMessage;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestInteractionRespondWithModal;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestKickMember;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestLeaveGuild;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestPinMessage;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestRemoveAllReactions;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestRemoveReaction;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestRemoveRole;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestSendMessage;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestUnbanMember;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestUnpinMessage;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestUpdateBotPresence;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestUpdateChannel;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestUpdateNickname;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestUpdateRole;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestUpdateWebhook;
use JaxkDev\DiscordBot\Libs\React\Promise\PromiseInterface;
use JaxkDev\DiscordBot\Models\Channels\Channel;
use JaxkDev\DiscordBot\Models\Channels\ChannelType;
use JaxkDev\DiscordBot\Models\Channels\ForumTag;
use JaxkDev\DiscordBot\Models\Channels\Overwrite;
use JaxkDev\DiscordBot\Models\Channels\VideoQualityMode;
use JaxkDev\DiscordBot\Models\Emoji;
use JaxkDev\DiscordBot\Models\Interactions\Commands\CommandOptionChoice;
use JaxkDev\DiscordBot\Models\Interactions\Interaction;
use JaxkDev\DiscordBot\Models\Interactions\InteractionType;
use JaxkDev\DiscordBot\Models\Messages\Component\ActionRow;
use JaxkDev\DiscordBot\Models\Messages\Component\ComponentType;
use JaxkDev\DiscordBot\Models\Messages\Embed\Embed;
use JaxkDev\DiscordBot\Models\Messages\Message;
use JaxkDev\DiscordBot\Models\Permissions\RolePermissions;
use JaxkDev\DiscordBot\Models\Presence\Activity\Activity;
use JaxkDev\DiscordBot\Models\Presence\Presence;
use JaxkDev\DiscordBot\Models\Presence\Status;
use JaxkDev\DiscordBot\Models\Role;
use JaxkDev\DiscordBot\Models\User;
use JaxkDev\DiscordBot\Models\Webhook;
use JaxkDev\DiscordBot\Models\WebhookType;
use JaxkDev\DiscordBot\Plugin\Events\BotUserUpdated;
use JaxkDev\DiscordBot\Plugin\Events\DiscordClosed;
use JaxkDev\DiscordBot\Plugin\Events\DiscordReady;
use pocketmine\event\EventPriority;
use function count;
use function in_array;
use function JaxkDev\DiscordBot\Libs\React\Promise\reject as rejectPromise;
use function sizeof;
use function strlen;

/**
 * For internal and developers use for interacting with the discord bot.
 *
 * @see Main::getApi() To get instance.
 */
final class Api{

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
                $this->plugin->getLogger()->notice("DiscordBot Connected, API is ready.");
            }, EventPriority::LOWEST, $this->plugin, true);
            $this->plugin->getServer()->getPluginManager()->registerEvent(DiscordClosed::class, function(DiscordClosed $event){
                $this->ready = false;
                $this->plugin->getLogger()->notice("DiscordBot Disconnected, API no longer ready.");
            }, EventPriority::LOWEST, $this->plugin, true);
            $this->plugin->getServer()->getPluginManager()->registerEvent(BotUserUpdated::class, function(BotUserUpdated $event){
                $this->bot_user = $event->getBot();
                $this->plugin->getLogger()->debug("Updated API Bot user.");
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
     * Fetch all Bans in the specified guild.
     *
     * @return PromiseInterface Resolves with an array of Ban models.
     */
    public function fetchBans(string $guild_id): PromiseInterface{
        if(!$this->ready){
            return rejectPromise(new ApiRejection("API is not ready for requests."));
        }
        if(!Utils::validDiscordSnowflake($guild_id)){
            return rejectPromise(new ApiRejection("Invalid guild ID '$guild_id'."));
        }
        $pk = new RequestFetchBans($guild_id);
        $this->plugin->writeOutboundData($pk);
        return ApiResolver::create($pk->getUID());
    }

    /**
     * Fetch a Channel by ID.
     *
     * @return PromiseInterface Resolves with an array of Channel models.
     */
    public function fetchChannel(?string $guild_id, string $channel_id): PromiseInterface{
        if(!$this->ready){
            return rejectPromise(new ApiRejection("API is not ready for requests."));
        }
        if($guild_id !== null && !Utils::validDiscordSnowflake($guild_id)){
            return rejectPromise(new ApiRejection("Invalid guild ID '$guild_id'."));
        }
        if(!Utils::validDiscordSnowflake($channel_id)){
            return rejectPromise(new ApiRejection("Invalid channel ID '$channel_id'."));
        }
        $pk = new RequestFetchChannel($guild_id, $channel_id);
        $this->plugin->writeOutboundData($pk);
        return ApiResolver::create($pk->getUID());
    }

    /**
     * Fetch all Channels in the specified Guild.
     *
     * @return PromiseInterface Resolves with an array of Channel models.
     */
    public function fetchChannels(string $guild_id): PromiseInterface{
        if(!$this->ready){
            return rejectPromise(new ApiRejection("API is not ready for requests."));
        }
        if(!Utils::validDiscordSnowflake($guild_id)){
            return rejectPromise(new ApiRejection("Invalid guild ID '$guild_id'."));
        }
        $pk = new RequestFetchChannels($guild_id);
        $this->plugin->writeOutboundData($pk);
        return ApiResolver::create($pk->getUID());
    }

    /**
     * Fetch a Guild by ID.
     *
     * @return PromiseInterface Resolves with a Guild model.
     */
    public function fetchGuild(string $guild_id): PromiseInterface{
        if(!$this->ready){
            return rejectPromise(new ApiRejection("API is not ready for requests."));
        }
        if(!Utils::validDiscordSnowflake($guild_id)){
            return rejectPromise(new ApiRejection("Invalid guild ID '$guild_id'."));
        }
        $pk = new RequestFetchGuild($guild_id);
        $this->plugin->writeOutboundData($pk);
        return ApiResolver::create($pk->getUID());
    }

    /**
     * Fetch all Guilds the bot is in.
     *
     * @return PromiseInterface Resolves with an array of Guild models.
     */
    public function fetchGuilds(): PromiseInterface{
        if(!$this->ready){
            return rejectPromise(new ApiRejection("API is not ready for requests."));
        }
        $pk = new RequestFetchGuilds();
        $this->plugin->writeOutboundData($pk);
        return ApiResolver::create($pk->getUID());
    }

    /**
     * Fetch all Invites in the specified Guild.
     *
     * @return PromiseInterface Resolves with an array of Invite models.
     */
    public function fetchInvites(string $guild_id): PromiseInterface{
        if(!$this->ready){
            return rejectPromise(new ApiRejection("API is not ready for requests."));
        }
        if(!Utils::validDiscordSnowflake($guild_id)){
            return rejectPromise(new ApiRejection("Invalid guild ID '$guild_id'."));
        }
        $pk = new RequestFetchInvites($guild_id);
        $this->plugin->writeOutboundData($pk);
        return ApiResolver::create($pk->getUID());
    }

    /**
     * Fetch a Member from the specified Guild by User ID.
     *
     * @return PromiseInterface Resolves with an array of Member models.
     */
    public function fetchMember(string $guild_id, string $user_id): PromiseInterface{
        if(!$this->ready){
            return rejectPromise(new ApiRejection("API is not ready for requests."));
        }
        if(!Utils::validDiscordSnowflake($guild_id)){
            return rejectPromise(new ApiRejection("Invalid guild ID '$guild_id'"));
        }
        if(!Utils::validDiscordSnowflake($user_id)){
            return rejectPromise(new ApiRejection("Invalid user ID '$user_id'"));
        }
        $pk = new RequestFetchMember($guild_id, $user_id);
        $this->plugin->writeOutboundData($pk);
        return ApiResolver::create($pk->getUID());
    }

    /**
     * Fetch all Members in the specified Guild.
     *
     * @return PromiseInterface Resolves with an array of Member models.
     */
    public function fetchMembers(string $guild_id): PromiseInterface{
        if(!$this->ready){
            return rejectPromise(new ApiRejection("API is not ready for requests."));
        }
        if(!Utils::validDiscordSnowflake($guild_id)){
            return rejectPromise(new ApiRejection("Invalid guild ID '$guild_id'"));
        }
        $pk = new RequestFetchMembers($guild_id);
        $this->plugin->writeOutboundData($pk);
        return ApiResolver::create($pk->getUID());
    }

    /**
     * Fetch a Message by ID.
     *
     * @return PromiseInterface Resolves with a Message model.
     */
    public function fetchMessage(?string $guild_id, string $channel_id, string $message_id): PromiseInterface{
        if(!$this->ready){
            return rejectPromise(new ApiRejection("API is not ready for requests."));
        }
        if($guild_id !== null && !Utils::validDiscordSnowflake($guild_id)){
            return rejectPromise(new ApiRejection("Invalid guild ID '$guild_id'"));
        }
        if(!Utils::validDiscordSnowflake($channel_id)){
            return rejectPromise(new ApiRejection("Invalid channel ID '$channel_id'"));
        }
        if(!Utils::validDiscordSnowflake($message_id)){
            return rejectPromise(new ApiRejection("Invalid message ID '$message_id'"));
        }
        $pk = new RequestFetchMessage($guild_id, $channel_id, $message_id);
        $this->plugin->writeOutboundData($pk);
        return ApiResolver::create($pk->getUID());
    }

    /**
     * Fetch all Pinned Messages in the specified Channel.
     *
     * @return PromiseInterface Resolves with an array of Message models.
     */
    public function fetchPinnedMessages(?string $guild_id, string $channel_id): PromiseInterface{
        if(!$this->ready){
            return rejectPromise(new ApiRejection("API is not ready for requests."));
        }
        if($guild_id !== null && !Utils::validDiscordSnowflake($guild_id)){
            return rejectPromise(new ApiRejection("Invalid guild ID '$guild_id'"));
        }
        if(!Utils::validDiscordSnowflake($channel_id)){
            return rejectPromise(new ApiRejection("Invalid channel ID '$channel_id'"));
        }
        $pk = new RequestFetchPinnedMessages($guild_id, $channel_id);
        $this->plugin->writeOutboundData($pk);
        return ApiResolver::create($pk->getUID());
    }

    /**
     * Fetch a Role by ID.
     *
     * @return PromiseInterface Resolves with a Role model.
     */
    public function fetchRole(string $guild_id, string $role_id): PromiseInterface{
        if(!$this->ready){
            return rejectPromise(new ApiRejection("API is not ready for requests."));
        }
        if(!Utils::validDiscordSnowflake($guild_id)){
            return rejectPromise(new ApiRejection("Invalid guild ID '$guild_id'"));
        }
        if(!Utils::validDiscordSnowflake($role_id)){
            return rejectPromise(new ApiRejection("Invalid role ID '$role_id'"));
        }
        $pk = new RequestFetchRole($guild_id, $role_id);
        $this->plugin->writeOutboundData($pk);
        return ApiResolver::create($pk->getUID());
    }

    /**
     * Fetch all Roles in the specified Guild.
     *
     * @return PromiseInterface Resolves with an array of Role models.
     */
    public function fetchRoles(string $guild_id): PromiseInterface{
        if(!$this->ready){
            return rejectPromise(new ApiRejection("API is not ready for requests."));
        }
        if(!Utils::validDiscordSnowflake($guild_id)){
            return rejectPromise(new ApiRejection("Invalid guild ID '$guild_id'"));
        }
        $pk = new RequestFetchRoles($guild_id);
        $this->plugin->writeOutboundData($pk);
        return ApiResolver::create($pk->getUID());
    }

    /**
     * Fetch a User by ID.
     *
     * @return PromiseInterface Resolves with a User model.
     */
    public function fetchUser(string $user_id): PromiseInterface{
        if(!$this->ready){
            return rejectPromise(new ApiRejection("API is not ready for requests."));
        }
        if(!Utils::validDiscordSnowflake($user_id)){
            return rejectPromise(new ApiRejection("Invalid user ID '$user_id'"));
        }
        $pk = new RequestFetchUser($user_id);
        $this->plugin->writeOutboundData($pk);
        return ApiResolver::create($pk->getUID());
    }

    /**
     * Fetch all Users (may not be a complete list).
     *
     * @return PromiseInterface Resolves with an array of User models.
     */
    public function fetchUsers(): PromiseInterface{
        if(!$this->ready){
            return rejectPromise(new ApiRejection("API is not ready for requests."));
        }
        $pk = new RequestFetchUsers();
        $this->plugin->writeOutboundData($pk);
        return ApiResolver::create($pk->getUID());
    }

    /**
     * Fetch all Webhooks in the specified Guild (optionally channel specific webhooks).
     *
     * @return PromiseInterface Resolves with an array of Webhook models.
     */
    public function fetchWebhooks(string $guild_id, ?string $channel_id = null): PromiseInterface{
        if(!$this->ready){
            return rejectPromise(new ApiRejection("API is not ready for requests."));
        }
        if(!Utils::validDiscordSnowflake($guild_id)){
            return rejectPromise(new ApiRejection("Invalid guild ID '$guild_id'"));
        }
        if($channel_id !== null && !Utils::validDiscordSnowflake($channel_id)){
            return rejectPromise(new ApiRejection("Invalid channel ID '$channel_id'"));
        }
        $pk = new RequestFetchWebhooks($guild_id, $channel_id);
        $this->plugin->writeOutboundData($pk);
        return ApiResolver::create($pk->getUID());
    }

    /**
     * Creates a normal webhook inside a channel.
     *
     * @param string  $name        max 80chars, 'clyde' and 'discord' not allowed in name.
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
        if($avatar_data !== null && !Utils::validImageData($avatar_data)){
            return rejectPromise(new ApiRejection("Webhook avatar data is invalid."));
        }
        $pk = new RequestCreateWebhook($guild_id, $channel_id, $name, $avatar_data, $reason);
        $this->plugin->writeOutboundData($pk);
        return ApiResolver::create($pk->getUID());
    }

    /**
     * Update a webhooks name or avatar hash.
     *
     * To change webhook avatar, set avatar to null in Webhook model and provide new VALID IMAGE DATA (Utils::imageToDiscordData()) as $new_avatar_data.
     * To remove webhook avatar, set avatar to null in Webhook model.
     *
     * @return PromiseInterface Resolves with a Webhook model.
     *@link Utils::imageToDiscordData()
     */
    public function updateWebhook(Webhook $webhook, ?string $new_avatar_data = null, ?string $reason = null): PromiseInterface{
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
        if($new_avatar_data !== null && !Utils::validImageData($new_avatar_data)){
            return rejectPromise(new ApiRejection("Webhook new avatar data is invalid."));
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
     * Pin a message to the channel.
     *
     * @return PromiseInterface Resolves with no data.
     */
    public function pinMessage(?string $guild_id, string $channel_id, string $message_id, ?string $reason = null): PromiseInterface{
        if(!$this->ready){
            return rejectPromise(new ApiRejection("API is not ready for requests."));
        }
        if($guild_id !== null && !Utils::validDiscordSnowflake($guild_id)){
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
        if($guild_id !== null && !Utils::validDiscordSnowflake($guild_id)){
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
        if($icon_data !== null && !Utils::validImageData($icon_data)){
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
     * To change role icon, set icon to null in Role model and provide new VALID IMAGE DATA (Utils::imageToDiscordData()) as $new_icon_data.
     * To remove role icon, set icon to null in Role model.
     *
     * @link Utils::imageToDiscordData()
     * @return PromiseInterface Resolves with a Role model.
     */
    public function updateRole(Role $role, ?string $new_icon_data = null, ?string $reason = null): PromiseInterface{
        if(!$this->ready){
            return rejectPromise(new ApiRejection("API is not ready for requests."));
        }
        if($new_icon_data !== null && !Utils::validImageData($new_icon_data)){
            return rejectPromise(new ApiRejection("Invalid icon data '$new_icon_data'."));
        }
        $pk = new RequestUpdateRole($role, $new_icon_data, $reason);
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
     * @param Emoji $emoji e.g. Emoji::fromUnicode('👍') or Emoji::fromPrivate() for custom/private/animated.
     *                     See Emoji::class for more info.
     * @return PromiseInterface Resolves with no data.
     * @see Emoji::fromUnicode()
     * @see Emoji::fromPrivate()
     */
    public function removeReaction(?string $guild_id, string $channel_id, string $message_id, string $user_id,
                                   Emoji $emoji): PromiseInterface{
        if(!$this->ready){
            return rejectPromise(new ApiRejection("API is not ready for requests."));
        }
        if($guild_id !== null && !Utils::validDiscordSnowflake($guild_id)){
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
     * @param Emoji|null $emoji If no emoji specified ALL reactions by EVERYONE will be deleted,
     *                          if specified everyone's reaction with that emoji will be removed.
     *
     *                          e.g. Emoji::fromUnicode('👍') or Emoji::fromPrivate() for custom/private/animated.
     *                          See Emoji::class for more info.
     * @return PromiseInterface Resolves with no data.
     * @see Emoji::fromUnicode()
     * @see Emoji::fromPrivate()
     */
    public function removeAllReactions(?string $guild_id, string $channel_id, string $message_id,
                                       ?Emoji $emoji = null): PromiseInterface{
        if(!$this->ready){
            return rejectPromise(new ApiRejection("API is not ready for requests."));
        }
        if($guild_id !== null && !Utils::validDiscordSnowflake($guild_id)){
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
     * @param Emoji $emoji e.g. Emoji::fromUnicode('👍') or Emoji::fromPrivate() for custom/private/animated.
     *                     See Emoji::class for more info.
     * @return PromiseInterface Resolves with no data.
     * @see Emoji::fromPrivate()
     * @see Emoji::fromUnicode()
     */
    public function addReaction(?string $guild_id, string $channel_id, string $message_id, Emoji $emoji): PromiseInterface{
        if(!$this->ready){
            return rejectPromise(new ApiRejection("API is not ready for requests."));
        }
        if($guild_id !== null && !Utils::validDiscordSnowflake($guild_id)){
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
        if($guild_id !== null && !Utils::validDiscordSnowflake($guild_id)){
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
        if($delete_message_seconds < 0 || $delete_message_seconds > 604800){
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
     * Sends a Message to discord.
     *
     * At least one of "content, embeds, sticker_ids, components, or files" is required.
     *
     * @param string|null                $content          Max 2000 characters. Read note above.
     * @param string|null                $reply_message_id Message ID to reply to, null if not a reply message.
     * @param Embed[]|null               $embeds           Array of embeds, max 10. Read note above.
     * @param bool|null                  $tts              Text to speech message?
     * @param ActionRow[]|null           $components       Array of ActionRow components, max 5. Read note above. (cannot contain TEXT_INPUT components)
     * @param string[]|null              $sticker_ids      Array of sticker IDs, max 3. Read note above.
     * @param array<string, string>|null $files            Array of file data to send, max 8MB total. Read note above.
     *                                                     Key is the file name, value is the file data.
     *                                                     e.g. ['file.png' => 'raw_file_data']
     *
     * @return PromiseInterface Resolves with a Message model.
     */
    public function sendMessage(?string $guild_id, string $channel_id, ?string $content = null, ?string $reply_message_id = null,
                                ?array $embeds = null, ?bool $tts = null, ?array $components = null, ?array $sticker_ids = null,
                                ?array $files = null): PromiseInterface{
        if(!$this->ready){
            return rejectPromise(new ApiRejection("API is not ready for requests."));
        }
        if($guild_id !== null && !Utils::validDiscordSnowflake($guild_id)){
            return rejectPromise(new ApiRejection("Invalid guild ID '$guild_id'."));
        }
        if(!Utils::validDiscordSnowflake($channel_id)){
            return rejectPromise(new ApiRejection("Invalid channel ID '$channel_id'."));
        }
        if(strlen($content ?? "") > 2000){
            return rejectPromise(new ApiRejection("Message content cannot be larger than 2000 characters for bots."));
        }
        if($reply_message_id !== null && !Utils::validDiscordSnowflake($reply_message_id)){
            return rejectPromise(new ApiRejection("Invalid reply message ID '$reply_message_id'."));
        }
        if(count($embeds ?? []) > 10){
            return rejectPromise(new ApiRejection("Embed array cannot contain more than 10 embeds."));
        }
        foreach(($embeds ?? []) as $embed){
            if(!$embed instanceof Embed){
                return rejectPromise(new ApiRejection("Embed array must all be of type '" . Embed::class . "'."));
            }
        }
        if(count($components ?? []) > 5){
            return rejectPromise(new ApiRejection("Components array cannot contain more than 5 ActionRow components."));
        }
        foreach(($components ?? []) as $comp){
            if(!$comp instanceof ActionRow){
                return rejectPromise(new ApiRejection("Components array must all be of type '" . ActionRow::class . "'."));
            }
            foreach($comp->getComponents() as $c){
                if($c->getType() === ComponentType::TEXT_INPUT){
                    //Text inputs are MODAL FORM only, cannot be sent via message only via interaction response.
                    return rejectPromise(new ApiRejection("Components array cannot contain TEXT_INPUT type."));
                }
            }
        }
        if(count($sticker_ids ?? []) > 3){
            return rejectPromise(new ApiRejection("Sticker array cannot contain more than 3 stickers."));
        }
        foreach(($sticker_ids ?? []) as $id){
            if(!Utils::validDiscordSnowflake($id)){
                return rejectPromise(new ApiRejection("Invalid sticker ID '$id'."));
            }
        }
        foreach($files ?? [] as $name => $data){
            if(strlen($name) > 256){
                return rejectPromise(new ApiRejection("File name cannot be larger than 256 characters."));
            }
            if(strlen($data) > 8388608){
                return rejectPromise(new ApiRejection("File data cannot be larger than 8388608 bytes."));
            }
        }
        $pk = new RequestSendMessage($guild_id, $channel_id, $content, $reply_message_id, $embeds, $tts, $components,
            $sticker_ids, $files);
        $this->plugin->writeOutboundData($pk);
        return ApiResolver::create($pk->getUID());
    }

    /**
     * Edit a sent message.
     *
     * TODO-Next-Minor, add support for editing message files & stickers.
     *
     * Note you can't convert a 'REPLY' message to a normal 'MESSAGE'.
     * Note at the moment we don't support editing/removing/adding stickers/files :(
     *
     * @return PromiseInterface Resolves with a Message model.
     */
    public function editMessage(Message $message): PromiseInterface{
        if(!$this->ready){
            return rejectPromise(new ApiRejection("API is not ready for requests."));
        }
        if(strlen($message->getContent() ?? "") > 2000){
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
        if($guild_id !== null && !Utils::validDiscordSnowflake($guild_id)){
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
     * @param string|null $guild_id    Null for DMs
     * @param string[]    $message_ids Unique array of message IDs (limit 100) to delete (messages cannot be older than 2 weeks)
     */
    public function bulkDeleteMessages(?string $guild_id, string $channel_id, array $message_ids, ?string $reason = null): PromiseInterface{
        if(!$this->ready){
            return rejectPromise(new ApiRejection("API is not ready for requests."));
        }
        if(count($message_ids) > 100 || count($message_ids) < 2){
            return rejectPromise(new ApiRejection("Cannot delete more than 100 or less than 2 messages at once."));
        }
        foreach($message_ids as $message_id){
            if(!Utils::validDiscordSnowflake($message_id)){
                return rejectPromise(new ApiRejection("Invalid message ID '$message_id'."));
            }
        }
        if($guild_id !== null && !Utils::validDiscordSnowflake($guild_id)){
            return rejectPromise(new ApiRejection("Invalid guild ID '$guild_id'."));
        }
        if(!Utils::validDiscordSnowflake($channel_id)){
            return rejectPromise(new ApiRejection("Invalid channel ID '$channel_id'."));
        }
        $pk = new RequestBulkDeleteMessages($guild_id, $channel_id, $message_ids, $reason);
        $this->plugin->writeOutboundData($pk);
        return ApiResolver::create($pk->getUID());
    }

    /**
     * @param string   $name                  Name of the thread (1-100 characters)
     * @param int|null $auto_archive_duration The thread will stop showing in the channel list after auto_archive_duration minutes of inactivity, can be set to: 60, 1440, 4320, 10080 (minutes)
     * @param int|null $rate_limit_per_user   Amount of seconds a user has to wait before sending another message (0-21600)
     */
    public function createThreadFromMessage(string $guild_id, string $channel_id, string $message_id, string $name,
                                            ?int $auto_archive_duration = null, ?int $rate_limit_per_user = null,
                                            ?string $reason = null): PromiseInterface{
        if(!$this->ready){
            return rejectPromise(new ApiRejection("API is not ready for requests."));
        }
        if(!Utils::validDiscordSnowflake($guild_id)){
            return rejectPromise(new ApiRejection("Invalid guild ID '$guild_id'."));
        }
        if(!Utils::validDiscordSnowflake($channel_id)){
            return rejectPromise(new ApiRejection("Invalid channel ID '$channel_id'."));
        }
        if(!Utils::validDiscordSnowflake($message_id)){
            return rejectPromise(new ApiRejection("Invalid message ID '$message_id'."));
        }
        if(strlen($name) < 1 || strlen($name) > 100){
            return rejectPromise(new ApiRejection("Channel name must be between 1 and 100 characters."));
        }
        if($auto_archive_duration !== null && !in_array($auto_archive_duration, [60, 1440, 4320, 10080], true)){
            return rejectPromise(new ApiRejection("Auto archive duration must be one of 60, 1440, 4320 or 10080 (minutes)."));
        }
        if($rate_limit_per_user !== null && ($rate_limit_per_user < 0 || $rate_limit_per_user > 21600)){
            return rejectPromise(new ApiRejection("Channel rate limit must be between 0 and 21600 (seconds)."));
        }
        $pk = new RequestCreateThreadFromMessage($guild_id, $channel_id, $message_id, $name, $auto_archive_duration,
            $rate_limit_per_user, $reason);
        $this->plugin->writeOutboundData($pk);
        return ApiResolver::create($pk->getUID());
    }

    /**
     * @param string      $name                  Name of the thread (1-100 characters)
     * @param ChannelType $type                  Type of thread to create (must be thread type, Announcement thread when channel_id is for announcement channel type)
     * @param bool|null   $invitable             Whether non-moderators can add other non-moderators to a thread; only available when creating a private thread
     * @param int|null    $auto_archive_duration The thread will stop showing in the channel list after auto_archive_duration minutes of inactivity, can be set to: 60, 1440, 4320, 10080 (minutes)
     * @param int|null    $rate_limit_per_user   Amount of seconds a user has to wait before sending another message (0-21600)
     */
    public function createThread(string $guild_id, string $channel_id, string $name, ChannelType $type,
                                 ?bool $invitable = null, ?int $auto_archive_duration = null,
                                 ?int $rate_limit_per_user = null, ?string $reason = null): PromiseInterface{
        if(!$this->ready){
            return rejectPromise(new ApiRejection("API is not ready for requests."));
        }
        if(!Utils::validDiscordSnowflake($guild_id)){
            return rejectPromise(new ApiRejection("Invalid guild ID '$guild_id'."));
        }
        if(!Utils::validDiscordSnowflake($channel_id)){
            return rejectPromise(new ApiRejection("Invalid channel ID '$channel_id'."));
        }
        if(strlen($name) < 1 || strlen($name) > 100){
            return rejectPromise(new ApiRejection("Channel name must be between 1 and 100 characters."));
        }
        if(!$type->isThread()){
            return rejectPromise(new ApiRejection("Channel type '{$type->name}' is not creatable, only threads can be created via createThread()."));
        }
        if($invitable !== null && $type !== ChannelType::PRIVATE_THREAD){
            return rejectPromise(new ApiRejection("Invitable can only be set on private threads."));
        }
        if($auto_archive_duration !== null && !in_array($auto_archive_duration, [60, 1440, 4320, 10080], true)){
            return rejectPromise(new ApiRejection("Auto archive duration must be one of 60, 1440, 4320 or 10080 (minutes)."));
        }
        if($rate_limit_per_user !== null && ($rate_limit_per_user < 0 || $rate_limit_per_user > 21600)){
            return rejectPromise(new ApiRejection("Channel rate limit must be between 0 and 21600 (seconds)."));
        }
        $pk = new RequestCreateThread($guild_id, $channel_id, $name, $type, $invitable, $auto_archive_duration,
            $rate_limit_per_user, $reason);
        $this->plugin->writeOutboundData($pk);
        return ApiResolver::create($pk->getUID());
    }

    /**
     * Create a guild channel.
     *
     * @param Overwrite[]     $permission_overwrites
     * @param ForumTag[]|null $available_tags
     * @return PromiseInterface Resolves with a Channel model.
     */
    public function createChannel(string $guild_id,
                                  string $name,
                                  ChannelType $type,
                                  ?string $topic = null,
                                  ?int $bitrate = null,
                                  ?int $user_limit = null,
                                  ?int $rate_limit_per_user = null,
                                  ?int $position = null,
                                  array $permission_overwrites = [],
                                  ?string $parent_id = null,
                                  ?bool $nsfw = null,
                                  ?string $rtc_region = null,
                                  ?VideoQualityMode $video_quality_mode = null,
                                  ?array $available_tags = null,
                                  ?string $reason = null): PromiseInterface{
        if(!$this->ready){
            return rejectPromise(new ApiRejection("API is not ready for requests."));
        }
        if(!Utils::validDiscordSnowflake($guild_id)){
            return rejectPromise(new ApiRejection("Invalid guild ID '$guild_id'."));
        }
        if(strlen($name) < 1 || strlen($name) > 100){
            return rejectPromise(new ApiRejection("Channel name must be between 1 and 100 characters."));
        }
        if(!$type->isGuild()){
            return rejectPromise(new ApiRejection("Channel type '{$type->name}' is not creatable, only guild channels can be created via createChannel()."));
        }
        if($topic !== null){
            if(!in_array($type, [ChannelType::GUILD_TEXT, ChannelType::GUILD_ANNOUNCEMENT, ChannelType::GUILD_FORUM, ChannelType::GUILD_MEDIA], true)){
                return rejectPromise(new ApiRejection("Channel topic can only be set on Text, Announcement, Forum, Media channels."));
            }
            if(strlen($topic) > 1024){
                return rejectPromise(new ApiRejection("Channel topic must be less than 1024 characters."));
            }
        }
        if($bitrate !== null){
            if(!in_array($type, [ChannelType::GUILD_VOICE, ChannelType::GUILD_STAGE_VOICE], true)){
                return rejectPromise(new ApiRejection("Channel bitrate can only be set on Voice, Stage channels."));
            }
            if($bitrate < 8000){
                return rejectPromise(new ApiRejection("Channel bitrate must be above 8000."));
            }
        }
        if($user_limit !== null){
            if(!in_array($type, [ChannelType::GUILD_VOICE, ChannelType::GUILD_STAGE_VOICE], true)){
                return rejectPromise(new ApiRejection("Channel user limit can only be set on Voice, Stage channels."));
            }
            if($user_limit < 0){
                return rejectPromise(new ApiRejection("Channel user limit must be positive."));
            }
        }
        if($rate_limit_per_user !== null){
            if(!in_array($type, [ChannelType::GUILD_TEXT, ChannelType::GUILD_VOICE, ChannelType::GUILD_STAGE_VOICE, ChannelType::GUILD_FORUM, ChannelType::GUILD_MEDIA], true)){
                return rejectPromise(new ApiRejection("Channel rate limit can only be set on Text, Voice, Stage, Forum, Media channels."));
            }
            if($rate_limit_per_user < 0 || $rate_limit_per_user > 21600){
                return rejectPromise(new ApiRejection("Channel rate limit must be between 0 and 21600."));
            }
        }
        if($position !== null){
            if($position < 0){
                return rejectPromise(new ApiRejection("Channel position must be positive."));
            }
        }
        foreach($permission_overwrites as $overwrite){
            if(!($overwrite instanceof Overwrite)){
                return rejectPromise(new ApiRejection("Permission overwrites must be an array of Overwrite models."));
            }
        }
        if($parent_id !== null){
            if(!in_array($type, [ChannelType::GUILD_TEXT, ChannelType::GUILD_VOICE, ChannelType::GUILD_ANNOUNCEMENT, ChannelType::GUILD_STAGE_VOICE, ChannelType::GUILD_FORUM, ChannelType::GUILD_MEDIA], true)){
                return rejectPromise(new ApiRejection("Channel parent ID can only be set on Text, Voice, Announcement, Stage, Forum, Media channels."));
            }
            if(!Utils::validDiscordSnowflake($parent_id)){
                return rejectPromise(new ApiRejection("Invalid parent ID '$parent_id'."));
            }
        }
        if($nsfw !== null){
            if(!in_array($type, [ChannelType::GUILD_TEXT, ChannelType::GUILD_VOICE, ChannelType::GUILD_ANNOUNCEMENT, ChannelType::GUILD_STAGE_VOICE, ChannelType::GUILD_FORUM], true)){
                return rejectPromise(new ApiRejection("Channel NSFW can only be set on Text, Voice, Announcement, Stage, Forum channels."));
            }
        }
        if($rtc_region !== null && !in_array($type, [ChannelType::GUILD_VOICE, ChannelType::GUILD_STAGE_VOICE], true)){
            return rejectPromise(new ApiRejection("Channel RTC region can only be set on Voice, Stage channels."));
        }
        if($video_quality_mode !== null && !in_array($type, [ChannelType::GUILD_VOICE, ChannelType::GUILD_STAGE_VOICE], true)){
            return rejectPromise(new ApiRejection("Channel video quality mode can only be set on Voice, Stage channels."));
        }
        if($available_tags !== null){
            if(!in_array($type, [ChannelType::GUILD_FORUM, ChannelType::GUILD_MEDIA], true)){
                return rejectPromise(new ApiRejection("Channel available tags can only be set on Forum, Media channels."));
            }
            foreach($available_tags as $tag){
                if(!($tag instanceof ForumTag)){
                    return rejectPromise(new ApiRejection("Available tags must be an array of ForumTag models."));
                }
            }
        }
        $pk = new RequestCreateChannel($guild_id, $name, $type, $topic, $bitrate, $user_limit, $rate_limit_per_user,
            $position, $permission_overwrites, $parent_id, $nsfw, $rtc_region, $video_quality_mode, $available_tags, $reason);
        $this->plugin->writeOutboundData($pk);
        return ApiResolver::create($pk->getUID());
    }

    /**
     * Update a channel.
     *
     * @return PromiseInterface Resolves with an updated Channel model.
     */
    public function updateChannel(Channel $channel, ?string $reason = null): PromiseInterface{
        if(!$this->ready){
            return rejectPromise(new ApiRejection("API is not ready for requests."));
        }
        $pk = new RequestUpdateChannel($channel, $reason);
        $this->plugin->writeOutboundData($pk);
        return ApiResolver::create($pk->getUID());
    }

    /**
     * Delete a channel, you cannot delete private channels (DM's)
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
        if($max_age < 0 || $max_age > 86400){
            return rejectPromise(new ApiRejection("Max age must be between 0(never) and 604800seconds (7 days)."));
        }
        if($max_uses < 0 || $max_uses > 100){
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

    public function interactionRespondWithMessage(Interaction $interaction, ?string $content = null, ?array $embeds = null,
                                                  ?bool $tts = null, ?array $components = null, ?array $files = null,
                                                  bool $ephemeral = false): PromiseInterface{
        if(!$this->ready){
            return rejectPromise(new ApiRejection("API is not ready for requests."));
        }
        if($interaction->getType() === InteractionType::APPLICATION_COMMAND_AUTOCOMPLETE || $interaction->getType() === InteractionType::PING){
            return rejectPromise(new ApiRejection("Interaction type '{$interaction->getType()->name}' is not supported by this method."));
        }
        if($interaction->getResponded()){
            return rejectPromise(new ApiRejection("Interaction has already been responded to."));
        }
        if(strlen($content ?? "") > 2000){
            return rejectPromise(new ApiRejection("Message content cannot be larger than 2000 characters for bots."));
        }
        if(count($embeds ?? []) > 10){
            return rejectPromise(new ApiRejection("Embed array cannot contain more than 10 embeds."));
        }
        foreach(($embeds ?? []) as $embed){
            if(!$embed instanceof Embed){
                return rejectPromise(new ApiRejection("Embed array must all be of type '" . Embed::class . "'."));
            }
        }
        if(count($components ?? []) > 5){
            return rejectPromise(new ApiRejection("Components array cannot contain more than 5 ActionRow components."));
        }
        foreach(($components ?? []) as $comp){
            if(!$comp instanceof ActionRow){
                return rejectPromise(new ApiRejection("Components array must all be of type '" . ActionRow::class . "'."));
            }
            foreach($comp->getComponents() as $c){
                if($c->getType() === ComponentType::TEXT_INPUT){
                    //Text inputs are MODAL FORM only, cannot be sent via message response, see Api::interactionRespondWithModal().
                    return rejectPromise(new ApiRejection("Components array cannot contain TEXT_INPUT type."));
                }
            }
        }
        foreach($files ?? [] as $name => $data){
            if(strlen($name) > 256){
                return rejectPromise(new ApiRejection("File name cannot be larger than 256 characters."));
            }
            if(strlen($data) > 8388608){
                return rejectPromise(new ApiRejection("File data cannot be larger than 8388608 bytes."));
            }
        }
        $interaction->setResponded();
        $pk = new RequestInteractionRespondWithMessage($interaction, $content, $embeds, $tts, $components, $files, $ephemeral);
        $this->plugin->writeOutboundData($pk);
        return ApiResolver::create($pk->getUID());
    }

    /**
     * Respond to an interaction with a popup modal form.
     *
     * @param ActionRow[] $components (Only TEXT_INPUT components are supported in modal forms)
     */
    public function interactionRespondWithModal(Interaction $interaction, string $title, string $custom_id,
                                                array $components): PromiseInterface{
        if(!$this->ready){
            return rejectPromise(new ApiRejection("API is not ready for requests."));
        }
        if($interaction->getType() === InteractionType::MODAL_SUBMIT || $interaction->getType() === InteractionType::PING){
            return rejectPromise(new ApiRejection("Interaction type '{$interaction->getType()->name}' is not supported by this method."));
        }
        if($interaction->getResponded()){
            return rejectPromise(new ApiRejection("Interaction has already been responded to."));
        }
        if(strlen($title) > 45){
            return rejectPromise(new ApiRejection("Modal title cannot be larger than 45 characters."));
        }
        if(strlen($custom_id) > 100){
            return rejectPromise(new ApiRejection("Custom ID cannot be larger than 100 characters."));
        }
        if(sizeof($components) > 5 || sizeof($components) === 0){
            return rejectPromise(new ApiRejection("Components array must contain between 1 and 5 ActionRow components."));
        }
        foreach($components as $comp){
            if(!$comp instanceof ActionRow){
                return rejectPromise(new ApiRejection("Components array must all be of type '" . ActionRow::class . "'."));
            }
            foreach($comp->getComponents() as $c){
                if($c->getType() !== ComponentType::TEXT_INPUT){
                    //Only text inputs are supported in modal forms.
                    return rejectPromise(new ApiRejection("Components array can only contain TEXT_INPUT type."));
                }
            }
        }
        $interaction->setResponded();
        $pk = new RequestInteractionRespondWithModal($interaction, $title, $custom_id, $components);
        $this->plugin->writeOutboundData($pk);
        return ApiResolver::create($pk->getUID());
    }

    /**
     * Send discord a response to an interaction with a list of choices for the user to pick from.
     *
     * @param Interaction           $interaction Only valid on APPLICATION_COMMAND_AUTOCOMPLETE interactions.
     * @param CommandOptionChoice[] $choices     Max 25
     */
    public function interactionRespondWithAutocomplete(Interaction $interaction, array $choices): PromiseInterface{
        if(!$this->ready){
            return rejectPromise(new ApiRejection("API is not ready for requests."));
        }
        if($interaction->getType() !== InteractionType::APPLICATION_COMMAND_AUTOCOMPLETE){
            return rejectPromise(new ApiRejection("Interaction type '{$interaction->getType()->name}' is not supported by this method."));
        }
        if($interaction->getResponded()){
            return rejectPromise(new ApiRejection("Interaction has already been responded to."));
        }
        if(sizeof($choices) > 25 || sizeof($choices) === 0){
            return rejectPromise(new ApiRejection("Choices array must contain between 1 and 25 CommandOptionChoice models."));
        }
        foreach($choices as $choice){
            if(!$choice instanceof CommandOptionChoice){
                return rejectPromise(new ApiRejection("Choices array must all be of type '" . CommandOptionChoice::class . "'."));
            }
        }
        $interaction->setResponded();
        $pk = new RequestInteractionRespondWithAutocomplete($interaction, $choices);
        $this->plugin->writeOutboundData($pk);
        return ApiResolver::create($pk->getUID());
    }
}