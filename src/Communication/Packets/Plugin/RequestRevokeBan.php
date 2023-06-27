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

class RequestRevokeBan extends Packet{

    private string $guild_id;

    private string $user_id;

    public function __construct(string $guild_id, string $user_id){
        parent::__construct();
        $this->guild_id = $guild_id;
        $this->user_id = $user_id;
    }

    public function getGuildId(): string{
        return $this->guild_id;
    }

    public function getUserId(): string{
        return $this->user_id;
    }

    public function __serialize(): array{
        return [
            $this->UID,
            $this->guild_id,
            $this->user_id
        ];
    }

    public function __unserialize(array $data): void{
        [
            $this->UID,
            $this->guild_id,
            $this->user_id
        ] = $data;
    }
}