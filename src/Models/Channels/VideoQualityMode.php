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

/** @link https://discord.com/developers/docs/resources/channel#channel-object-video-quality-modes */
enum VideoQualityMode: int{

    /** Discord auto selects for optimal performance. */
    case AUTO = 1;
    /** 720p */
    case FULL = 2;
}
