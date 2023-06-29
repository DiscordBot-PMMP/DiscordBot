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

enum Status: string implements \JsonSerializable{

    case ONLINE = "online";
    case IDLE = "idle";
    case DND = "dnd";
    case OFFLINE = "offline";

    public function jsonSerialize(): string{
        return $this->value;
    }

    public static function fromJson(string $value): self{
        return self::from($value);
    }
}
