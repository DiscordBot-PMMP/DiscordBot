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

namespace JaxkDev\DiscordBot\InternalBot;

final class Discord extends \Discord\Discord{
    public function handleWsClose(int $op, string $reason): void{
        $this->emit("ws_closed", [$op, $reason]);
        parent::handleWsClose($op, $reason);
    }
}