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

class Heartbeat extends Packet{

    public const ID = 1;

    private float $heartbeat;

    public function __construct(float $heartbeat, ?int $uid = null){
        parent::__construct($uid);
        $this->heartbeat = $heartbeat;
    }

    public function getHeartbeat(): float{
        return $this->heartbeat;
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