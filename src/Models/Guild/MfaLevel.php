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

namespace JaxkDev\DiscordBot\Models\Guild;

use JaxkDev\DiscordBot\Communication\BinarySerializable;
use pocketmine\utils\BinaryStream;

/** @link https://discord.com/developers/docs/resources/guild#guild-object-mfa-level */
enum MfaLevel: int implements \JsonSerializable, BinarySerializable{

    /** Guild has no MFA/2FA requirement for moderation actions */
    case NONE = 0;

    /** Guild has a 2FA requirement for moderation actions */
    case ELEVATED = 1;

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