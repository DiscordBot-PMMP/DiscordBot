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

use JaxkDev\DiscordBot\Communication\BinarySerializable;
use JaxkDev\DiscordBot\Communication\BinaryStream;
use JaxkDev\DiscordBot\Communication\Packets\Packet;
use JaxkDev\DiscordBot\Models\Webhook;

class RequestCreateWebhook extends Packet{

    public const SERIALIZE_ID = 45;

    private Webhook $webhook;

    public function __construct(Webhook $webhook, ?int $uid = null){
        parent::__construct($uid);
        $this->webhook = $webhook;
    }

    public function getWebhook(): Webhook{
        return $this->webhook;
    }

    public function binarySerialize(): BinaryStream{
        $stream = new BinaryStream();
        $stream->putSerializable($this->webhook);
        return $stream;
    }

    public static function fromBinary(BinaryStream $stream): self{
        return new self($stream->getSerializable(Webhook::class));
    }
}