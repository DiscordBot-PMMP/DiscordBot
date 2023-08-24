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

namespace JaxkDev\DiscordBot\Models\Messages\Component;

/**
 * @link https://discord.com/developers/docs/interactions/message-components#button-object-button-styles
 */
enum ButtonStyle: int{

    case PRIMARY = 1;
    case SECONDARY = 2;
    case SUCCESS = 3;
    case DANGER = 4;
    case LINK = 5;
}
