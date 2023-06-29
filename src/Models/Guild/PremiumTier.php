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

/** @link https://discord.com/developers/docs/resources/guild#guild-object-premium-tier */
enum PremiumTier: int implements \JsonSerializable{

    /** Guild has not unlocked any Server Boost perks */
    case NONE = 0;

    /** Guild has unlocked Server Boost level 1 perks */
    case TIER_1 = 1;

    /** Guild has unlocked Server Boost level 2 perks */
    case TIER_2 = 2;

    /** Guild has unlocked Server Boost level 3 perks */
    case TIER_3 = 3;

    public function jsonSerialize(): int{
        return $this->value;
    }

    public static function fromJson(int $value): self{
        return self::from($value);
    }
}
