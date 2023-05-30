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
use JaxkDev\DiscordBot\Models\Role;

class RequestUpdateRole extends Packet{

    /** @var Role */
    private $role;

    public function __construct(Role $role){
        parent::__construct();
        $this->role = $role;
    }

    public function getRole(): Role{
        return $this->role;
    }

    public function __serialize(): array{
        return [
            $this->UID,
            $this->role
        ];
    }

    public function __unserialize($data): void{
        try{
            [
                $this->UID,
                $this->role
            ] = $data;
        }catch (\Throwable $e){
            throw new \InvalidArgumentException("Failed to unserialize packet: " . $e->getMessage());
        }
    }
}