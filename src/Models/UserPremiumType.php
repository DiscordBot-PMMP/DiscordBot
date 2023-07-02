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

use JaxkDev\DiscordBot\Communication\BinarySerializable;
use JaxkDev\DiscordBot\Communication\BinaryStream;

/** @link https://discord.com/developers/docs/resources/user#user-object-premium-types */
enum UserPremiumType: int implements \JsonSerializable, BinarySerializable{

    case NONE = 0;
    case NITRO_CLASSIC = 1;
    case NITRO = 2;
    case NITRO_BASIC = 3;

    public function binarySerialize(): BinaryStream{
        $stream = new BinaryStream();
        $stream->putByte($this->value);
        return $stream;
    }

    public static function fromBinary(BinaryStream $stream): self{
        return self::from($stream->getByte());
    }

    public function jsonSerialize(): int{
        return $this->value;
    }

    public static function fromJson(int $value): self{
        return self::from($value);
    }
}
