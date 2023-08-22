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
use JaxkDev\DiscordBot\Models\Channels\Channel;

final class RequestUpdateChannel extends Packet{

    public const SERIALIZE_ID = 80;

    private Channel $channel;

    private ?string $reason;

    public function __construct(Channel $channel, ?string $reason = null, ?int $uid = null){
        parent::__construct($uid);
        $this->channel = $channel;
        $this->reason = $reason;
    }

    public function getChannel(): Channel{
        return $this->channel;
    }

    public function getReason(): ?string{
        return $this->reason;
    }

    public function binarySerialize(): BinaryStream{
        $stream = new BinaryStream();
        $stream->putInt($this->getUID());
        $stream->putSerializable($this->channel);
        $stream->putNullableString($this->reason);
        return $stream;
    }

    public static function fromBinary(BinaryStream $stream): self{
        $uid = $stream->getInt();
        return new self(
            $stream->getSerializable(Channel::class),
            $stream->getNullableString(),
            $uid
        );
    }
}