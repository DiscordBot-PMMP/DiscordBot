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

namespace JaxkDev\DiscordBot\Models\Presence\Activity;

/**
 * @link https://discord.com/developers/docs/topics/gateway-events#activity-object-activity-types
 */
enum ActivityType: int{

    /** Playing {name} */
    case GAME = 0;

    /** Streaming {details} */
    case STREAMING = 1;

    /** Listening to {name} */
    case LISTENING = 2;

    /** Watching {name} */
    case WATCHING = 3;

    /** {emoji} {name} */
    case CUSTOM = 4;

    /** Competing in {name} */
    case COMPETING = 5;
}