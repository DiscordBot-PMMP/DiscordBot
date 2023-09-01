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
 * @link https://discord.com/developers/docs/interactions/message-components#text-inputs-text-input-styles
 */
enum TextInputStyle: int{

    /** Single-line input */
    case SHORT = 1;

    /** Multi-line input */
    case PARAGRAPH = 2;
}
