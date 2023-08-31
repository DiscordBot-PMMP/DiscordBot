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

namespace JaxkDev\DiscordBot\Communication;

use JaxkDev\DiscordBot\Communication\Packets\Discord\BanCreate;
use JaxkDev\DiscordBot\Communication\Packets\Discord\BanDelete;
use JaxkDev\DiscordBot\Communication\Packets\Discord\BotUserUpdate;
use JaxkDev\DiscordBot\Communication\Packets\Discord\ChannelCreate;
use JaxkDev\DiscordBot\Communication\Packets\Discord\ChannelDelete;
use JaxkDev\DiscordBot\Communication\Packets\Discord\ChannelPinsUpdate;
use JaxkDev\DiscordBot\Communication\Packets\Discord\ChannelUpdate;
use JaxkDev\DiscordBot\Communication\Packets\Discord\DiscordConnected;
use JaxkDev\DiscordBot\Communication\Packets\Discord\GuildJoin;
use JaxkDev\DiscordBot\Communication\Packets\Discord\GuildLeave;
use JaxkDev\DiscordBot\Communication\Packets\Discord\GuildUpdate;
use JaxkDev\DiscordBot\Communication\Packets\Discord\InteractionReceived;
use JaxkDev\DiscordBot\Communication\Packets\Discord\InviteCreate;
use JaxkDev\DiscordBot\Communication\Packets\Discord\InviteDelete;
use JaxkDev\DiscordBot\Communication\Packets\Discord\MemberJoin;
use JaxkDev\DiscordBot\Communication\Packets\Discord\MemberLeave;
use JaxkDev\DiscordBot\Communication\Packets\Discord\MemberUpdate;
use JaxkDev\DiscordBot\Communication\Packets\Discord\MessageDelete;
use JaxkDev\DiscordBot\Communication\Packets\Discord\MessageDeleteBulk;
use JaxkDev\DiscordBot\Communication\Packets\Discord\MessageReactionAdd;
use JaxkDev\DiscordBot\Communication\Packets\Discord\MessageReactionRemove;
use JaxkDev\DiscordBot\Communication\Packets\Discord\MessageReactionRemoveAll;
use JaxkDev\DiscordBot\Communication\Packets\Discord\MessageReactionRemoveEmoji;
use JaxkDev\DiscordBot\Communication\Packets\Discord\MessageSent;
use JaxkDev\DiscordBot\Communication\Packets\Discord\MessageUpdate;
use JaxkDev\DiscordBot\Communication\Packets\Discord\PresenceUpdate;
use JaxkDev\DiscordBot\Communication\Packets\Discord\RoleCreate;
use JaxkDev\DiscordBot\Communication\Packets\Discord\RoleDelete;
use JaxkDev\DiscordBot\Communication\Packets\Discord\RoleUpdate;
use JaxkDev\DiscordBot\Communication\Packets\Discord\ThreadCreate;
use JaxkDev\DiscordBot\Communication\Packets\Discord\ThreadDelete;
use JaxkDev\DiscordBot\Communication\Packets\Discord\ThreadUpdate;
use JaxkDev\DiscordBot\Communication\Packets\Discord\VoiceStateUpdate;
use JaxkDev\DiscordBot\Communication\Packets\Discord\WebhooksUpdate;
use JaxkDev\DiscordBot\Communication\Packets\External\Connect;
use JaxkDev\DiscordBot\Communication\Packets\External\Disconnect;
use JaxkDev\DiscordBot\Communication\Packets\Heartbeat;
use JaxkDev\DiscordBot\Communication\Packets\Packet;
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
use JaxkDev\DiscordBot\Communication\Packets\Resolution;
use JaxkDev\DiscordBot\Models\Ban;
use JaxkDev\DiscordBot\Models\Channels\Channel;
use JaxkDev\DiscordBot\Models\Emoji;
use JaxkDev\DiscordBot\Models\Guild\Guild;
use JaxkDev\DiscordBot\Models\Invite;
use JaxkDev\DiscordBot\Models\Member;
use JaxkDev\DiscordBot\Models\Messages\Component\ActionRow;
use JaxkDev\DiscordBot\Models\Messages\Component\Button;
use JaxkDev\DiscordBot\Models\Messages\Component\SelectMenu;
use JaxkDev\DiscordBot\Models\Messages\Component\TextInput;
use JaxkDev\DiscordBot\Models\Messages\Message;
use JaxkDev\DiscordBot\Models\Presence\Activity\Activity;
use JaxkDev\DiscordBot\Models\Presence\Presence;
use JaxkDev\DiscordBot\Models\Role;
use JaxkDev\DiscordBot\Models\User;
use JaxkDev\DiscordBot\Models\VoiceState;
use JaxkDev\DiscordBot\Models\Webhook;

final class NetworkApi{

    // Version will change for any breaking changes to the protocol (Models / Packets)
    public const VERSION = 1;
    public const MAGIC = 0x4a61786b; //Jaxk (max 4 bytes)

    /** @var array<int, class-string<Packet>> */
    public const PACKETS_MAP = [
        /* 01-99 Misc Packets */
        /* 01 */ Heartbeat::SERIALIZE_ID => Heartbeat::class,
        /* 02 */ Resolution::SERIALIZE_ID => Resolution::class,
        /* 03-99 Reserved */

        /* 100-199 External->PMMP Packets */
        /* 100 */ Connect::SERIALIZE_ID => Connect::class,
        /* 101 */ Disconnect::SERIALIZE_ID => Disconnect::class,
        /* 102-199 Reserved */

        /* 200-399 Discord->PMMP Packets */
        /* 200 */ BanCreate::SERIALIZE_ID => BanCreate::class,
        /* 201 */ BanDelete::SERIALIZE_ID => BanDelete::class,
        /* 202 */ BotUserUpdate::SERIALIZE_ID => BotUserUpdate::class,
        /* 203 */ ChannelCreate::SERIALIZE_ID => ChannelCreate::class,
        /* 204 */ ChannelDelete::SERIALIZE_ID => ChannelDelete::class,
        /* 205 */ ChannelPinsUpdate::SERIALIZE_ID => ChannelPinsUpdate::class,
        /* 206 */ ChannelUpdate::SERIALIZE_ID => ChannelUpdate::class,
        /* 207 */ DiscordConnected::SERIALIZE_ID => DiscordConnected::class,
        /* 208 */ GuildJoin::SERIALIZE_ID => GuildJoin::class,
        /* 209 */ GuildLeave::SERIALIZE_ID => GuildLeave::class,
        /* 210 */ GuildUpdate::SERIALIZE_ID => GuildUpdate::class,
        /* 211 */ InteractionReceived::SERIALIZE_ID => InteractionReceived::class,
        /* 212 */ InviteCreate::SERIALIZE_ID => InviteCreate::class,
        /* 213 */ InviteDelete::SERIALIZE_ID => InviteDelete::class,
        /* 214 */ MemberJoin::SERIALIZE_ID => MemberJoin::class,
        /* 215 */ MemberLeave::SERIALIZE_ID => MemberLeave::class,
        /* 216 */ MemberUpdate::SERIALIZE_ID => MemberUpdate::class,
        /* 217 */ MessageDelete::SERIALIZE_ID => MessageDelete::class,
        /* 218 */ MessageDeleteBulk::SERIALIZE_ID => MessageDeleteBulk::class,
        /* 219 */ MessageReactionAdd::SERIALIZE_ID => MessageReactionAdd::class,
        /* 220 */ MessageReactionRemove::SERIALIZE_ID => MessageReactionRemove::class,
        /* 221 */ MessageReactionRemoveAll::SERIALIZE_ID => MessageReactionRemoveAll::class,
        /* 222 */ MessageReactionRemoveEmoji::SERIALIZE_ID => MessageReactionRemoveEmoji::class,
        /* 223 */ MessageSent::SERIALIZE_ID => MessageSent::class,
        /* 224 */ MessageUpdate::SERIALIZE_ID => MessageUpdate::class,
        /* 225 */ PresenceUpdate::SERIALIZE_ID => PresenceUpdate::class,
        /* 226 */ RoleCreate::SERIALIZE_ID => RoleCreate::class,
        /* 227 */ RoleDelete::SERIALIZE_ID => RoleDelete::class,
        /* 228 */ RoleUpdate::SERIALIZE_ID => RoleUpdate::class,
        /* 229 */ ThreadCreate::SERIALIZE_ID => ThreadCreate::class,
        /* 230 */ ThreadDelete::SERIALIZE_ID => ThreadDelete::class,
        /* 231 */ ThreadUpdate::SERIALIZE_ID => ThreadUpdate::class,
        /* 232 */ VoiceStateUpdate::SERIALIZE_ID => VoiceStateUpdate::class,
        /* 233 */ WebhooksUpdate::SERIALIZE_ID => WebhooksUpdate::class,
        /* 234-399 Reserved */

        /* 400-599 PMMP->Discord Packets */
        /* 400 */ RequestAddReaction::SERIALIZE_ID => RequestAddReaction::class,
        /* 401 */ RequestAddRole::SERIALIZE_ID => RequestAddRole::class,
        /* 402 */ RequestBanMember::SERIALIZE_ID => RequestBanMember::class,
        /* 403 */ RequestBroadcastTyping::SERIALIZE_ID => RequestBroadcastTyping::class,
        /* 404 */ RequestBulkDeleteMessages::SERIALIZE_ID => RequestBulkDeleteMessages::class,
        /* 405 */ RequestCreateChannel::SERIALIZE_ID => RequestCreateChannel::class,
        /* 406 */ RequestCreateInvite::SERIALIZE_ID => RequestCreateInvite::class,
        /* 407 */ RequestCreateRole::SERIALIZE_ID => RequestCreateRole::class,
        /* 408 */ RequestCreateThread::SERIALIZE_ID => RequestCreateThread::class,
        /* 409 */ RequestCreateThreadFromMessage::SERIALIZE_ID => RequestCreateThreadFromMessage::class,
        /* 410 */ RequestCreateWebhook::SERIALIZE_ID => RequestCreateWebhook::class,
        /* 411 */ RequestDeleteChannel::SERIALIZE_ID => RequestDeleteChannel::class,
        /* 412 */ RequestDeleteInvite::SERIALIZE_ID => RequestDeleteInvite::class,
        /* 413 */ RequestDeleteMessage::SERIALIZE_ID => RequestDeleteMessage::class,
        /* 414 */ RequestDeleteRole::SERIALIZE_ID => RequestDeleteRole::class,
        /* 415 */ RequestDeleteWebhook::SERIALIZE_ID => RequestDeleteWebhook::class,
        /* 416 */ RequestEditMessage::SERIALIZE_ID => RequestEditMessage::class,
        /* 417 */ RequestFetchBans::SERIALIZE_ID => RequestFetchBans::class,
        /* 418 */ RequestFetchChannel::SERIALIZE_ID => RequestFetchChannel::class,
        /* 419 */ RequestFetchChannels::SERIALIZE_ID => RequestFetchChannels::class,
        /* 420 */ RequestFetchGuild::SERIALIZE_ID => RequestFetchGuild::class,
        /* 421 */ RequestFetchGuilds::SERIALIZE_ID => RequestFetchGuilds::class,
        /* 422 */ RequestFetchInvites::SERIALIZE_ID => RequestFetchInvites::class,
        /* 423 */ RequestFetchMember::SERIALIZE_ID => RequestFetchMember::class,
        /* 424 */ RequestFetchMembers::SERIALIZE_ID => RequestFetchMembers::class,
        /* 425 */ RequestFetchMessage::SERIALIZE_ID => RequestFetchMessage::class,
        /* 426 */ RequestFetchPinnedMessages::SERIALIZE_ID => RequestFetchPinnedMessages::class,
        /* 427 */ RequestFetchRole::SERIALIZE_ID => RequestFetchRole::class,
        /* 428 */ RequestFetchRoles::SERIALIZE_ID => RequestFetchRoles::class,
        /* 429 */ RequestFetchUser::SERIALIZE_ID => RequestFetchUser::class,
        /* 430 */ RequestFetchUsers::SERIALIZE_ID => RequestFetchUsers::class,
        /* 431 */ RequestFetchWebhooks::SERIALIZE_ID => RequestFetchWebhooks::class,
        /* 432 */ RequestInteractionRespondWithAutocomplete::SERIALIZE_ID => RequestInteractionRespondWithAutocomplete::class,
        /* 433 */ RequestInteractionRespondWithMessage::SERIALIZE_ID => RequestInteractionRespondWithMessage::class,
        /* 434 */ RequestInteractionRespondWithModal::SERIALIZE_ID => RequestInteractionRespondWithModal::class,
        /* 435 */ RequestKickMember::SERIALIZE_ID => RequestKickMember::class,
        /* 436 */ RequestLeaveGuild::SERIALIZE_ID => RequestLeaveGuild::class,
        /* 437 */ RequestPinMessage::SERIALIZE_ID => RequestPinMessage::class,
        /* 438 */ RequestRemoveAllReactions::SERIALIZE_ID => RequestRemoveAllReactions::class,
        /* 439 */ RequestRemoveReaction::SERIALIZE_ID => RequestRemoveReaction::class,
        /* 440 */ RequestRemoveRole::SERIALIZE_ID => RequestRemoveRole::class,
        /* 441 */ RequestSendMessage::SERIALIZE_ID => RequestSendMessage::class,
        /* 442 */ RequestUnbanMember::SERIALIZE_ID => RequestUnbanMember::class,
        /* 443 */ RequestUnpinMessage::SERIALIZE_ID => RequestUnpinMessage::class,
        /* 444 */ RequestUpdateBotPresence::SERIALIZE_ID => RequestUpdateBotPresence::class,
        /* 445 */ RequestUpdateChannel::SERIALIZE_ID => RequestUpdateChannel::class,
        /* 446 */ RequestUpdateNickname::SERIALIZE_ID => RequestUpdateNickname::class,
        /* 447 */ RequestUpdateRole::SERIALIZE_ID => RequestUpdateRole::class,
        /* 448 */ RequestUpdateWebhook::SERIALIZE_ID => RequestUpdateWebhook::class,
        /* 449-599 Reserved */

        /* 600-65535 Unreserved IDs */
    ];

    /** @var array<int, class-string<BinarySerializable<mixed>>>  */
    public const MODELS_MAP = [
        /* 01 */ Guild::SERIALIZE_ID => Guild::class,
        /* 02 */ Activity::SERIALIZE_ID => Activity::class,
        /* 03 */ Presence::SERIALIZE_ID => Presence::class,
        /* 04 */ Ban::SERIALIZE_ID => Ban::class,
        /* 05 */ Emoji::SERIALIZE_ID => Emoji::class,
        /* 06 */ Invite::SERIALIZE_ID => Invite::class,
        /* 07 */ Member::SERIALIZE_ID => Member::class,
        /* 08 */ Role::SERIALIZE_ID => Role::class,
        /* 09 */ User::SERIALIZE_ID => User::class,
        /* 10 */ VoiceState::SERIALIZE_ID => VoiceState::class,
        /* 11 */ Webhook::SERIALIZE_ID => Webhook::class,
        /* 12 */ Message::SERIALIZE_ID => Message::class,
        /* 13 */ Channel::SERIALIZE_ID => Channel::class,
        /* 14 */ ActionRow::SERIALIZE_ID => ActionRow::class,
        /* 15 */ Button::SERIALIZE_ID => Button::class,
        /* 16 */ SelectMenu::SERIALIZE_ID => SelectMenu::class,
        /* 17 */ TextInput::SERIALIZE_ID => TextInput::class,

        /* 18-65535 Unreserved IDs */
    ];

    /**
     * @return class-string<Packet>|null
     */
    public static function getPacketClass(int $id): ?string{
        return self::PACKETS_MAP[$id] ?? null;
    }

    /**
     * @return class-string<BinarySerializable<mixed>>|null
     */
    public static function getModelClass(int $id): ?string{
        return self::MODELS_MAP[$id] ?? null;
    }
}