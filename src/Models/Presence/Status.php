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

use JaxkDev\DiscordBot\Communication\BinarySerializable;
use JaxkDev\DiscordBot\Communication\BinaryStream;

enum Status: string implements \JsonSerializable, BinarySerializable{

    case ONLINE = "online";
    case IDLE = "idle";
    case DND = "dnd";
    case OFFLINE = "offline";

    public function binarySerialize(): BinaryStream{
        $stream = new BinaryStream();
        $stream->putString($this->value);
        return $stream;
    }

    public static function fromBinary(BinaryStream $stream): self{
        return self::from($stream->getString());
    }

    public function jsonSerialize(): string{
        return $this->value;
    }

    public static function fromJson(string $value): self{
        return self::from($value);
    }
}
