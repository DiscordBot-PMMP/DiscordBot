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

class RoleDelete extends Packet{

    /** @var string */
    private $role_id;

    public function __construct(string $role_id){
        parent::__construct();
        $this->role_id = $role_id;
    }

    public function getRoleId(): string{
        return $this->role_id;
    }

    public function __serialize(): array{
        return [
            $this->UID,
            $this->role_id
        ];
    }

    public function __unserialize(array $data): void{
        [
            $this->UID,
            $this->role_id
        ] = $data;
    }
}