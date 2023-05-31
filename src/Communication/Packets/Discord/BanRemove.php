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

namespace JaxkDev\DiscordBot\Communication\Packets\Discord;

use JaxkDev\DiscordBot\Communication\Packets\Packet;

class BanRemove extends Packet{

    /** @var string */
    private $ban_id;

    public function __construct(string $ban_id){
        parent::__construct();
        $this->ban_id = $ban_id;
    }

    public function getBanId(): string{
        return $this->ban_id;
    }

    public function __serialize(): array{
        return [
            $this->UID,
            $this->ban_id
        ];
    }

    public function __unserialize(array $data): void{
        [
            $this->UID,
            $this->ban_id
        ] = $data;
    }
}