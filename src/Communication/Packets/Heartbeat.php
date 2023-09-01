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

namespace JaxkDev\DiscordBot\Communication\Packets;

use JaxkDev\DiscordBot\Communication\BinaryStream;

final class Heartbeat extends Packet{

    public const SERIALIZE_ID = 1;

    private int $heartbeat;

    public function __construct(int $heartbeat, ?int $uid = null){
        parent::__construct($uid);
        $this->heartbeat = $heartbeat;
    }

    public function getHeartbeat(): int{
        return $this->heartbeat;
    }

    public function binarySerialize(): BinaryStream{
        $stream = new BinaryStream();
        $stream->putInt($this->getUID());
        $stream->putInt($this->heartbeat);
        return $stream;
    }

    public static function fromBinary(BinaryStream $stream): self{
        $uid = $stream->getInt();
        return new self(
            $stream->getInt(), // heartbeat
            $uid
        );
    }
}