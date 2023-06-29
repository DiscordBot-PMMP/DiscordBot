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

/** @link https://discord.com/developers/docs/resources/guild#guild-object-guild-nsfw-level */
enum NsfwLevel: int implements \JsonSerializable{

    case DEFAULT = 0;
    case EXPLICIT = 1;
    case SAFE = 2;
    case AGE_RESTRICTED = 3;

    public function jsonSerialize(): int{
        return $this->value;
    }

    public static function fromJson(int $value): self{
        return self::from($value);
    }
}
