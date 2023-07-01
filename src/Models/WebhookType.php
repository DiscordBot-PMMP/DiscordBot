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
use pocketmine\utils\BinaryStream;

/**
 * @link https://discord.com/developers/docs/resources/webhook#webhook-object-webhook-types
 */
enum WebhookType: int implements \JsonSerializable, BinarySerializable{

    /**
     * Standard webhook
     * "Incoming Webhooks can post messages to channels with a generated token"
     */
    case INCOMING = 1;

    /**
     * Receiving 'news' from another channel.
     * "Channel Follower Webhooks are internal webhooks used with Channel Following to post new messages into channels"
     */
    case CHANNEL_FOLLOWER = 2;

    /**
     * "Application webhooks are webhooks used with Interactions"
     */
    case APPLICATION = 3;

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