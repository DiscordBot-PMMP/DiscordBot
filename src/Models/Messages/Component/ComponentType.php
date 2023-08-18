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

namespace JaxkDev\DiscordBot\Models\Messages\Component;

/**
 * @link https://discord.com/developers/docs/interactions/message-components#component-object-component-types
 */
enum ComponentType: int{

    case ACTION_ROW = 1;
    case BUTTON = 2;
    case STRING_SELECT = 3;
    case TEXT_INPUT = 4;
    case USER_SELECT = 5;
    case ROLE_SELECT = 6;
    case MENTIONABLE_SELECT = 7;
    case CHANNEL_SELECT = 8;
}
