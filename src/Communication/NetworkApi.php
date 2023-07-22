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

namespace JaxkDev\DiscordBot\Communication;

use JaxkDev\DiscordBot\Communication\Packets\Discord\BanAdd;
use JaxkDev\DiscordBot\Communication\Packets\Discord\BanRemove;
use JaxkDev\DiscordBot\Communication\Packets\Discord\ChannelCreate;
use JaxkDev\DiscordBot\Communication\Packets\Discord\ChannelDelete;
use JaxkDev\DiscordBot\Communication\Packets\Discord\ChannelPinsUpdate;
use JaxkDev\DiscordBot\Communication\Packets\Discord\ChannelUpdate;
use JaxkDev\DiscordBot\Communication\Packets\Discord\DiscordConnected;
use JaxkDev\DiscordBot\Communication\Packets\Discord\GuildJoin;
use JaxkDev\DiscordBot\Communication\Packets\Discord\GuildLeave;
use JaxkDev\DiscordBot\Communication\Packets\Discord\GuildUpdate;
use JaxkDev\DiscordBot\Communication\Packets\Discord\InviteCreate;
use JaxkDev\DiscordBot\Communication\Packets\Discord\InviteDelete;
use JaxkDev\DiscordBot\Communication\Packets\Discord\MemberJoin;
use JaxkDev\DiscordBot\Communication\Packets\Discord\MemberLeave;
use JaxkDev\DiscordBot\Communication\Packets\Discord\MemberUpdate;
use JaxkDev\DiscordBot\Communication\Packets\Discord\MessageDelete;
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
use JaxkDev\DiscordBot\Communication\Packets\Discord\VoiceStateUpdate;
use JaxkDev\DiscordBot\Communication\Packets\External\Connect;
use JaxkDev\DiscordBot\Communication\Packets\External\Disconnect;
use JaxkDev\DiscordBot\Communication\Packets\Heartbeat;
use JaxkDev\DiscordBot\Communication\Packets\Packet;
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
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestInitialiseBan;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestInitialiseInvite;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestKickMember;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestLeaveGuild;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestPinMessage;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestRemoveAllReactions;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestRemoveReaction;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestRevokeBan;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestRevokeInvite;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestSendFile;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestSendMessage;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestUnpinMessage;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestUpdateChannel;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestUpdateNickname;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestUpdatePresence;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestUpdateRole;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestUpdateWebhook;
use JaxkDev\DiscordBot\Communication\Packets\Resolution;

class NetworkApi{

    //Version will change for any breaking changes to:
    //Models, Packets, Magic.
    public const VERSION = 1;
    public const MAGIC = 0x4a61786b; //Jaxk :) (max 4 bytes)

    /**
     * @var array<int, class-string<Packet>>
     */
    public const PACKETS_MAP = [
        Connect::SERIALIZE_ID => Connect::class,
        Disconnect::SERIALIZE_ID => Disconnect::class,
        Heartbeat::SERIALIZE_ID => Heartbeat::class,
        Resolution::SERIALIZE_ID => Resolution::class,
        /*RequestAddReaction::ID => RequestAddReaction::class,
        RequestAddRole::ID => RequestAddRole::class,
        RequestBroadcastTyping::ID => RequestBroadcastTyping::class,
        RequestCreateChannel::ID => RequestCreateChannel::class,
        RequestCreateRole::ID => RequestCreateRole::class,
        RequestCreateWebhook::ID => RequestCreateWebhook::class,
        RequestDeleteChannel::ID => RequestDeleteChannel::class,
        RequestDeleteMessage::ID => RequestDeleteMessage::class,
        RequestDeleteRole::ID => RequestDeleteRole::class,
        RequestDeleteWebhook::ID => RequestDeleteWebhook::class,
        RequestEditMessage::ID => RequestEditMessage::class,
        RequestFetchMessage::ID => RequestFetchMessage::class,
        RequestFetchPinnedMessages::ID => RequestFetchPinnedMessages::class,
        RequestFetchWebhooks::ID => RequestFetchWebhooks::class,
        RequestInitialiseBan::ID => RequestInitialiseBan::class,
        RequestInitialiseInvite::ID => RequestInitialiseInvite::class,
        RequestKickMember::ID => RequestKickMember::class,
        RequestLeaveGuild::ID => RequestLeaveGuild::class,
        RequestPinMessage::ID => RequestPinMessage::class,
        RequestRemoveAllReactions::ID => RequestRemoveAllReactions::class,
        RequestRemoveReaction::ID => RequestRemoveReaction::class,
        RequestRevokeBan::ID => RequestRevokeBan::class,
        RequestRevokeInvite::ID => RequestRevokeInvite::class,
        RequestSendFile::ID => RequestSendFile::class,
        RequestSendMessage::ID => RequestSendMessage::class,
        RequestUnpinMessage::ID => RequestUnpinMessage::class,
        RequestUpdateChannel::ID => RequestUpdateChannel::class,
        RequestUpdateNickname::ID => RequestUpdateNickname::class,
        RequestUpdatePresence::ID => RequestUpdatePresence::class,
        RequestUpdateRole::ID => RequestUpdateRole::class,
        RequestUpdateWebhook::ID => RequestUpdateWebhook::class,
        BanAdd::ID => BanAdd::class,
        BanRemove::ID => BanRemove::class,
        ChannelCreate::ID => ChannelCreate::class,
        ChannelDelete::ID => ChannelDelete::class,
        ChannelPinsUpdate::ID => ChannelPinsUpdate::class,
        ChannelUpdate::ID => ChannelUpdate::class,
        DiscordConnected::ID => DiscordConnected::class,
        GuildJoin::ID => GuildJoin::class,
        GuildLeave::ID => GuildLeave::class,
        GuildUpdate::ID => GuildUpdate::class,
        InviteCreate::ID => InviteCreate::class,
        InviteDelete::ID => InviteDelete::class,
        MemberJoin::ID => MemberJoin::class,
        MemberLeave::ID => MemberLeave::class,
        MemberUpdate::ID => MemberUpdate::class,
        MessageDelete::ID => MessageDelete::class,
        MessageReactionAdd::ID => MessageReactionAdd::class,
        MessageReactionRemove::ID => MessageReactionRemove::class,
        MessageReactionRemoveAll::ID => MessageReactionRemoveAll::class,
        MessageReactionRemoveEmoji::ID => MessageReactionRemoveEmoji::class,
        MessageSent::ID => MessageSent::class,
        MessageUpdate::ID => MessageUpdate::class,
        PresenceUpdate::ID => PresenceUpdate::class,
        RoleCreate::ID => RoleCreate::class,
        RoleDelete::ID => RoleDelete::class,
        RoleUpdate::ID => RoleUpdate::class,
        VoiceStateUpdate::ID => VoiceStateUpdate::class,*/
        //65 Next ID
    ];

    /** @var array<int, class-string>  */
    public const MODELS_MAP = [
        //TODO
    ];

    /**
     * @param int $id
     * @return class-string<Packet>|null
     */
    public static function getPacketClass(int $id): ?string{
        return self::PACKETS_MAP[$id] ?? null;
    }

    public static function getModelClass(int $id): ?string{
        return null;
    }
}