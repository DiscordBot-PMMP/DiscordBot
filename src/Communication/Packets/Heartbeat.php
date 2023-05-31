<?php
/*
 * DiscordBot, PocketMine-MP Plugin.
 *
 * Licensed under the Open Software License version 3.0 (OSL-3.0)
 * Copyright (C) 2020-present JaxkDev
 *
 * Twitter :: @JaxkDev
 * Discord :: JaxkDev#2698
 * Email   :: JaxkDev@gmail.com
 */

namespace JaxkDev\DiscordBot\Communication\Packets;

class Heartbeat extends Packet{

    /** @var float */
    private $heartbeat;

    public function __construct(float $heartbeat){
        parent::__construct();
        $this->heartbeat = $heartbeat;
    }

    public function getHeartbeat(): float{
        return $this->heartbeat;
    }

    public function __serialize(): array{
        return [
            $this->UID,
            $this->heartbeat
        ];
    }

    public function __unserialize(array $data): void{
        [
            $this->UID,
            $this->heartbeat
        ] = $data;
    }
}