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

namespace JaxkDev\DiscordBot\Models\Channels;

/**
 * @link https://discord.com/developers/docs/resources/channel#channel-object-channel-types
 */
enum ChannelType: int{

    case GUILD_TEXT = 0;
    case DM = 1;
    case GUILD_VOICE = 2;
    case GROUP_DM = 3;
    case GUILD_CATEGORY = 4;
    case GUILD_ANNOUNCEMENT = 5;
    case ANNOUNCEMENT_THREAD = 10;
    case PUBLIC_THREAD = 11;
    case PRIVATE_THREAD = 12;
    case GUILD_STAGE_VOICE = 13;
    case GUILD_DIRECTORY = 14;
    case GUILD_FORUM = 15;

    case GUILD_MEDIA = 16; //WIP, Do not use.

    public function isGuild(): bool{
        return match($this){
            self::GUILD_TEXT, self::GUILD_VOICE, self::GUILD_CATEGORY, self::GUILD_ANNOUNCEMENT, self::GUILD_STAGE_VOICE, self::GUILD_DIRECTORY, self::GUILD_FORUM => true,
            default => false
        };
    }

    public function isThread(): bool{
        return match($this){
            self::ANNOUNCEMENT_THREAD, self::PUBLIC_THREAD, self::PRIVATE_THREAD => true,
            default => false
        };
    }
}
