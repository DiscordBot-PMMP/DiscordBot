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

namespace JaxkDev\DiscordBot\Communication\Packets;

use JaxkDev\DiscordBot\Communication\BinaryStream;

class Heartbeat extends Packet{

    public const ID = 1;

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
        //$stream->putInt($this->UID);
        $stream->putInt($this->heartbeat);
        return $stream;
    }

    public static function fromBinary(BinaryStream $stream): self{
        //$uid = $stream->getInt();
        $heartbeat = $stream->getInt();
        return new self(
            $heartbeat,
            //$uid
        );
    }

    public function jsonSerialize(): array{
        return [
            "uid" => $this->UID,
            "heartbeat" => $this->heartbeat
        ];
    }

    public static function fromJson(array $data): self{
        return new self(
            $data["heartbeat"],
            $data["uid"]
        );
    }
}