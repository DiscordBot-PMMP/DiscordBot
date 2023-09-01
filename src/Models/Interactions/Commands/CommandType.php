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

namespace JaxkDev\DiscordBot\Models\Interactions\Commands;

/** @link https://discord.com/developers/docs/interactions/application-commands#application-command-object-application-command-types */
enum CommandType: int{

    /** Slash commands; a text-based command that shows up when a user types / */
    case CHAT_INPUT = 1;
    /** A UI-based command that shows up when you right-click or tap on a user */
    case USER = 2;
    /** A UI-based command that shows up when you right-click or tap on a message */
    case MESSAGE = 3;
}
