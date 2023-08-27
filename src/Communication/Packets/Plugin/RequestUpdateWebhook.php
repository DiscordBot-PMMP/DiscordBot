<?php

/*
 * DiscordBot, PocketMine-MP Plugin.
 *
 * Licensed under the Open Software License version 3.0 (OSL-3.0)
 * Copyright (C) 2020-present JaxkDev
 *
 * Discord :: JaxkDev
 * Email   :: JaxkDev@gmail.com
 */

namespace JaxkDev\DiscordBot\Communication\Packets\Plugin;

use JaxkDev\DiscordBot\Communication\BinaryStream;
use JaxkDev\DiscordBot\Communication\Packets\Packet;
use JaxkDev\DiscordBot\Models\Webhook;

final class RequestUpdateWebhook extends Packet{

    public const SERIALIZE_ID = 448;

    private Webhook $webhook;

    /** @var string|null If changing avatar, set this to validImageData. */
    private ?string $new_avatar_data;

    private ?string $reason;

    public function __construct(Webhook $webhook, ?string $new_avatar_data, ?string $reason = null, ?int $uid = null){
        parent::__construct($uid);
        $this->webhook = $webhook;
        $this->new_avatar_data = $new_avatar_data;
        $this->reason = $reason;
    }

    public function getWebhook(): Webhook{
        return $this->webhook;
    }

    public function getNewAvatarData(): ?string{
        return $this->new_avatar_data;
    }

    public function getReason(): ?string{
        return $this->reason;
    }

    public function binarySerialize(): BinaryStream{
        $stream = new BinaryStream();
        $stream->putInt($this->getUID());
        $stream->putSerializable($this->webhook);
        $stream->putNullableString($this->new_avatar_data);
        $stream->putNullableString($this->reason);
        return $stream;
    }

    public static function fromBinary(BinaryStream $stream): self{
        $uid = $stream->getInt();
        return new self(
            $stream->getSerializable(Webhook::class), // webhook
            $stream->getNullableString(),             // new_avatar_data
            $stream->getNullableString(),             // reason
            $uid
        );
    }
}