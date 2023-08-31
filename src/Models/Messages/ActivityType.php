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

namespace JaxkDev\DiscordBot\Models\Messages;

/**
 * @link https://discord.com/developers/docs/resources/channel#message-object-message-activity-types
 */
enum ActivityType: int{

    case JOIN = 1;
    case SPECTATE = 2;
    case LISTEN = 3;
    case JOIN_REQUEST = 5;
}
