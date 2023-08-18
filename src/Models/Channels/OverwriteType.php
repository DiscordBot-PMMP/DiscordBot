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

namespace JaxkDev\DiscordBot\Models\Channels;

/**
 * @link https://discord.com/developers/docs/resources/channel#overwrite-object-overwrite-structure
 */
enum OverwriteType: int{

    case ROLE = 0;
    case MEMBER = 1;
}
