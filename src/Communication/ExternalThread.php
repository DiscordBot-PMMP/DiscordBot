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

use JaxkDev\DiscordBot\ExternalBot\ServerSocket;

/**
 * ExternalThread is used to host the bot outside the server, and just open a socket to read/write data.
 */
class ExternalThread extends Thread{

    public function run(): void{
        require_once(\JaxkDev\DiscordBot\COMPOSER);

        (new ServerSocket($this))->start();
    }
}