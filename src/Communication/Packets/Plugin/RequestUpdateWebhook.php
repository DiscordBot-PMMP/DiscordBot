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
use JaxkDev\DiscordBot\Models\Webhook;

class RequestUpdateWebhook extends Packet{

    public const SERIALIZE_ID = 83;

    private Webhook $webhook;

    private ?string $reason;

    public function __construct(Webhook $webhook, ?string $reason = null, ?int $uid = null){
        parent::__construct($uid);
        $this->webhook = $webhook;
        $this->reason = $reason;
    }

    public function getWebhook(): Webhook{
        return $this->webhook;
    }

    public function getReason(): ?string{
        return $this->reason;
    }

    public function binarySerialize(): BinaryStream{
        $stream = new BinaryStream();
        $stream->putInt($this->getUID());
        $stream->putSerializable($this->webhook);
        $stream->putNullableString($this->reason);
        return $stream;
    }

    public static function fromBinary(BinaryStream $stream): self{
        $uid = $stream->getInt();
        return new self(
            $stream->getSerializable(Webhook::class), // webhook
            $stream->getNullableString(),             // reason
            $uid
        );
    }
}