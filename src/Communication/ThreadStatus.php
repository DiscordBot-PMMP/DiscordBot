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

namespace JaxkDev\DiscordBot\Communication;

enum ThreadStatus: int{

    case STARTING = 0;
    case STARTED = 1;
    case RUNNING = 2;
    case STOPPING = 3;
    case STOPPED = 4;
}