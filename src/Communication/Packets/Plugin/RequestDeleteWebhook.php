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

namespace JaxkDev\DiscordBot\Communication\Packets\Plugin;

use JaxkDev\DiscordBot\Communication\BinaryStream;
use JaxkDev\DiscordBot\Communication\Packets\Packet;

class RequestDeleteWebhook extends Packet{

    public const SERIALIZE_ID = 49;

    private string $webhook_id;

    private string $channel_id;

    public function __construct(string $channel_id, string $webhook_id, ?int $uid = null){
        parent::__construct($uid);
        $this->webhook_id = $webhook_id;
        $this->channel_id = $channel_id;
    }

    public function getChannelId(): string{
        return $this->channel_id;
    }

    public function getWebhookId(): string{
        return $this->webhook_id;
    }

    public function binarySerialize(): BinaryStream{
        $stream = new BinaryStream();
        $stream->putString($this->channel_id);
        $stream->putString($this->webhook_id);
        return $stream;
    }

    public static function fromBinary(BinaryStream $stream): self{
        return new self(
            $stream->getString(), // channel_id
            $stream->getString()  // webhook_id
        );
    }
}