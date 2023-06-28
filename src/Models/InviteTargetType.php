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

namespace JaxkDev\DiscordBot\Models;

/** @link https://discord.com/developers/docs/resources/invite#invite-object-invite-target-types */
enum InviteTargetType: int{

    case STREAM = 1;
    case EMBEDDED_APPLICATION = 2;
}
