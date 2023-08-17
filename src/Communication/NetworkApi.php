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

use JaxkDev\DiscordBot\Communication\Packets\Discord\BanCreate;
use JaxkDev\DiscordBot\Communication\Packets\Discord\BanDelete;
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
use JaxkDev\DiscordBot\Communication\Packets\Resolution;

class NetworkApi{

    //Version will change for any breaking changes to:
    //Models, Packets, Magic.
    public const VERSION = 1;
    public const MAGIC = 0x4a61786b; //Jaxk (max 4 bytes)

    /**
     * @var array<int, class-string<Packet>>
     */
    public const PACKETS_MAP = [
        //1-2 Misc Packets
        Heartbeat::SERIALIZE_ID => Heartbeat::class,
        Resolution::SERIALIZE_ID => Resolution::class,

        //3-4 External->PMMP Packets
        Connect::SERIALIZE_ID => Connect::class,
        Disconnect::SERIALIZE_ID => Disconnect::class,

        //5-31 Discord->PMMP Packets
        DiscordConnected::SERIALIZE_ID => DiscordConnected::class,
        BanCreate::SERIALIZE_ID => BanCreate::class,
        BanDelete::SERIALIZE_ID => BanDelete::class,
        ChannelCreate::SERIALIZE_ID => ChannelCreate::class,
        ChannelDelete::SERIALIZE_ID => ChannelDelete::class,
        ChannelPinsUpdate::SERIALIZE_ID => ChannelPinsUpdate::class,
        ChannelUpdate::SERIALIZE_ID => ChannelUpdate::class,
        GuildJoin::SERIALIZE_ID => GuildJoin::class,
        GuildLeave::SERIALIZE_ID => GuildLeave::class,
        GuildUpdate::SERIALIZE_ID => GuildUpdate::class,
        InviteCreate::SERIALIZE_ID => InviteCreate::class,
        InviteDelete::SERIALIZE_ID => InviteDelete::class,
        MemberJoin::SERIALIZE_ID => MemberJoin::class,
        MemberLeave::SERIALIZE_ID => MemberLeave::class,
        MemberUpdate::SERIALIZE_ID => MemberUpdate::class,
        MessageDelete::SERIALIZE_ID => MessageDelete::class,
        MessageReactionAdd::SERIALIZE_ID => MessageReactionAdd::class,
        MessageReactionRemove::SERIALIZE_ID => MessageReactionRemove::class,
        MessageReactionRemoveAll::SERIALIZE_ID => MessageReactionRemoveAll::class,
        MessageReactionRemoveEmoji::SERIALIZE_ID => MessageReactionRemoveEmoji::class,
        MessageSent::SERIALIZE_ID => MessageSent::class,
        MessageUpdate::SERIALIZE_ID => MessageUpdate::class,
        PresenceUpdate::SERIALIZE_ID => PresenceUpdate::class,
        RoleCreate::SERIALIZE_ID => RoleCreate::class,
        RoleDelete::SERIALIZE_ID => RoleDelete::class,
        RoleUpdate::SERIALIZE_ID => RoleUpdate::class,
        VoiceStateUpdate::SERIALIZE_ID => VoiceStateUpdate::class,
        //Reserved 31-39

        //40-83 PMMP->Discord Packets
        RequestAddReaction::SERIALIZE_ID => RequestAddReaction::class,
        RequestAddRole::SERIALIZE_ID => RequestAddRole::class,
        RequestBroadcastTyping::SERIALIZE_ID => RequestBroadcastTyping::class,
        RequestCreateChannel::SERIALIZE_ID => RequestCreateChannel::class,
        RequestCreateRole::SERIALIZE_ID => RequestCreateRole::class,
        RequestCreateWebhook::SERIALIZE_ID => RequestCreateWebhook::class,
        RequestDeleteChannel::SERIALIZE_ID => RequestDeleteChannel::class,
        RequestDeleteMessage::SERIALIZE_ID => RequestDeleteMessage::class,
        RequestDeleteRole::SERIALIZE_ID => RequestDeleteRole::class,
        RequestDeleteWebhook::SERIALIZE_ID => RequestDeleteWebhook::class,
        RequestEditMessage::SERIALIZE_ID => RequestEditMessage::class,
        RequestFetchBans::SERIALIZE_ID => RequestFetchBans::class,
        RequestFetchChannel::SERIALIZE_ID => RequestFetchChannel::class,
        RequestFetchChannels::SERIALIZE_ID => RequestFetchChannels::class,
        RequestFetchGuild::SERIALIZE_ID => RequestFetchGuild::class,
        RequestFetchGuilds::SERIALIZE_ID => RequestFetchGuilds::class,
        RequestFetchInvites::SERIALIZE_ID => RequestFetchInvites::class,
        RequestFetchMember::SERIALIZE_ID => RequestFetchMember::class,
        RequestFetchMembers::SERIALIZE_ID => RequestFetchMembers::class,
        RequestFetchMessage::SERIALIZE_ID => RequestFetchMessage::class,
        RequestFetchPinnedMessages::SERIALIZE_ID => RequestFetchPinnedMessages::class,
        RequestFetchRole::SERIALIZE_ID => RequestFetchRole::class,
        RequestFetchRoles::SERIALIZE_ID => RequestFetchRoles::class,
        RequestFetchUser::SERIALIZE_ID => RequestFetchUser::class,
        RequestFetchUsers::SERIALIZE_ID => RequestFetchUsers::class,
        RequestFetchWebhooks::SERIALIZE_ID => RequestFetchWebhooks::class,
        RequestBanMember::SERIALIZE_ID => RequestBanMember::class,
        RequestCreateInvite::SERIALIZE_ID => RequestCreateInvite::class,
        RequestKickMember::SERIALIZE_ID => RequestKickMember::class,
        RequestLeaveGuild::SERIALIZE_ID => RequestLeaveGuild::class,
        RequestPinMessage::SERIALIZE_ID => RequestPinMessage::class,
        RequestRemoveAllReactions::SERIALIZE_ID => RequestRemoveAllReactions::class,
        RequestRemoveReaction::SERIALIZE_ID => RequestRemoveReaction::class,
        RequestRemoveRole::SERIALIZE_ID => RequestRemoveRole::class,
        RequestUnbanMember::SERIALIZE_ID => RequestUnbanMember::class,
        RequestDeleteInvite::SERIALIZE_ID => RequestDeleteInvite::class,
        RequestSendFile::SERIALIZE_ID => RequestSendFile::class,
        RequestSendMessage::SERIALIZE_ID => RequestSendMessage::class,
        RequestUnpinMessage::SERIALIZE_ID => RequestUnpinMessage::class,
        RequestUpdateBotPresence::SERIALIZE_ID => RequestUpdateBotPresence::class,
        RequestUpdateChannel::SERIALIZE_ID => RequestUpdateChannel::class,
        RequestUpdateNickname::SERIALIZE_ID => RequestUpdateNickname::class,
        RequestUpdateRole::SERIALIZE_ID => RequestUpdateRole::class,
        RequestUpdateWebhook::SERIALIZE_ID => RequestUpdateWebhook::class
        //Reserved 84-99

        //100 Next ID
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