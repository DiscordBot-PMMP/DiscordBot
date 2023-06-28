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

use JaxkDev\DiscordBot\Communication\Packets\Packet;
use JaxkDev\DiscordBot\Models\Presence\Presence;

class RequestUpdatePresence extends Packet{

    private Presence $presence;

    public function __construct(Presence $presence){
        parent::__construct();
        $this->presence = $presence;
    }

    public function getPresence(): Presence{
        return $this->presence;
    }

    public function __serialize(): array{
        return [
            $this->UID,
            $this->presence
        ];
    }

    public function __unserialize(array $data): void{
        [
            $this->UID,
            $this->presence
        ] = $data;
    }
}