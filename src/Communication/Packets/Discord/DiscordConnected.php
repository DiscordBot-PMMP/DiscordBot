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

namespace JaxkDev\DiscordBot\Communication\Packets\Discord;

use JaxkDev\DiscordBot\Communication\Packets\Packet;

class DiscordConnected extends Packet{

    public const ID = 42;

    public function jsonSerialize(): array{
        return [
            "uid" => $this->UID
        ];
    }

    public static function fromJson(array $data): self{
        return new self(
            $data["uid"]
        );
    }
}