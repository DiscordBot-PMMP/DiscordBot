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

namespace JaxkDev\DiscordBot\Models\Presence;

enum Status: string{

    case STATUS_ONLINE = "online";
    case STATUS_IDLE = "idle";
    case STATUS_DND = "dnd";
    case STATUS_OFFLINE = "offline";
}
