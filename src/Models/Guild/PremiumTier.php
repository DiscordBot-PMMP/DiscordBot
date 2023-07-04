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
 * @implements BinarySerializable<PremiumTier>
 * @link https://discord.com/developers/docs/resources/guild#guild-object-premium-tier
 */
enum PremiumTier: int implements BinarySerializable{

    /** Guild has not unlocked any Server Boost perks */
    case NONE = 0;

    /** Guild has unlocked Server Boost level 1 perks */
    case TIER_1 = 1;

    /** Guild has unlocked Server Boost level 2 perks */
    case TIER_2 = 2;

    /** Guild has unlocked Server Boost level 3 perks */
    case TIER_3 = 3;

    public function binarySerialize(): BinaryStream{
        $stream = new BinaryStream();
        $stream->putByte($this->value);
        return $stream;
    }

    public static function fromBinary(BinaryStream $stream): self{
        return self::from($stream->getByte());
    }
}
