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

/** @implements BinarySerializable<DefaultMessageNotificationLevel> */
enum DefaultMessageNotificationLevel: int implements BinarySerializable{

    /** Members will receive notifications for all messages by default */
    case ALL_MESSAGES = 0;

    /** Members will receive notifications only for messages that @ mention them by default */
    case ONLY_MENTIONS = 1;

    public function binarySerialize(): BinaryStream{
        $stream = new BinaryStream();
        $stream->putByte($this->value);
        return $stream;
    }

    public static function fromBinary(BinaryStream $stream): self{
        return self::from($stream->getByte());
    }
}