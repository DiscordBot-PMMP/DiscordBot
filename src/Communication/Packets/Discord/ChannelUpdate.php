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

namespace JaxkDev\DiscordBot\Communication\Packets\Discord;

use JaxkDev\DiscordBot\Communication\BinaryStream;
use JaxkDev\DiscordBot\Communication\Packets\Packet;
use JaxkDev\DiscordBot\Models\Channels\Channel;

final class ChannelUpdate extends Packet{

    public const SERIALIZE_ID = 206;

    private Channel $channel;

    /** Old channel if cached. */
    private ?Channel $old_channel;

    public function __construct(Channel $channel, ?Channel $old_channel, ?int $uid = null){
        parent::__construct($uid);
        $this->channel = $channel;
        $this->old_channel = $old_channel;
    }

    public function getChannel(): Channel{
        return $this->channel;
    }

    public function getOldChannel(): ?Channel{
        return $this->old_channel;
    }

    public function binarySerialize(): BinaryStream{
        $stream = new BinaryStream();
        $stream->putInt($this->getUID());
        $stream->putSerializable($this->channel);
        $stream->putNullableSerializable($this->old_channel);
        return $stream;
    }

    public static function fromBinary(BinaryStream $stream): self{
        $uid = $stream->getInt();
        return new self(
            $stream->getSerializable(Channel::class),         // channel
            $stream->getNullableSerializable(Channel::class), // old_channel
            $uid
        );
    }
}