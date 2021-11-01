<?php
/*
 * DiscordBot, PocketMine-MP Plugin.
 *
 * Licensed under the Open Software License version 3.0 (OSL-3.0)
 * Copyright (C) 2020-present JaxkDev
 *
 * Twitter :: @JaxkDev
 * Discord :: JaxkDev#2698
 * Email   :: JaxkDev@gmail.com
 */

namespace JaxkDev\DiscordBot\Communication\Packets\Discord;

use JaxkDev\DiscordBot\Communication\Packets\Packet;

class DiscordReady extends Packet{

    public function serialize(): ?string{
        return serialize($this->UID);
    }

    public function unserialize($data): void{
        $data = unserialize($data);
        if(!is_int($data)){
            throw new \AssertionError("Failed to unserialize packet UID to int, got '".gettype($data)."' instead.");
        }
        $this->UID = $data;
    }
}