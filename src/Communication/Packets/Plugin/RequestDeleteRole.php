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

namespace JaxkDev\DiscordBot\Communication\Packets\Plugin;

use JaxkDev\DiscordBot\Communication\Packets\Packet;

class RequestDeleteRole extends Packet{

    /** @var string */
    private $guild_id;

    /** @var string */
    private $role_id;

    public function __construct(string $guild_id, string $role_id){
        parent::__construct();
        $this->guild_id = $guild_id;
        $this->role_id = $role_id;
    }

    public function getGuildId(): string{
        return $this->guild_id;
    }

    public function getRoleId(): string{
        return $this->role_id;
    }

    public function __serialize(): array{
        return [
            $this->UID,
            $this->guild_id,
            $this->role_id
        ];
    }

    public function __unserialize(array $data): void{
        [
            $this->UID,
            $this->guild_id,
            $this->role_id
        ] = $data;
    }
}