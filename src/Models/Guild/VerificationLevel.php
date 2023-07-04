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
use JaxkDev\DiscordBot\Communication\BinaryStream;

/**
 * @implements BinarySerializable<VerificationLevel>
 * @link https://discord.com/developers/docs/resources/guild#guild-object-verification-level
 */
enum VerificationLevel: int implements BinarySerializable{

    /** Unrestricted */
    case NONE = 0;

    /** Must have verified email on account */
    case LOW = 1;

    /** Must be registered on Discord for longer than 5 minutes */
    case MEDIUM = 2;

    /** Must be a member of the server for longer than 10 minutes */
    case HIGH = 3;

    /** Must have a verified phone number */
    case VERY_HIGH = 4;

    public function binarySerialize(): BinaryStream{
        $stream = new BinaryStream();
        $stream->putByte($this->value);
        return $stream;
    }

    public static function fromBinary(BinaryStream $stream): self{
        return self::from($stream->getByte());
    }
}