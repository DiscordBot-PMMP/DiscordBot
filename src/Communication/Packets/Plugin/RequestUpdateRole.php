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
use JaxkDev\DiscordBot\Models\Role;

class RequestUpdateRole extends Packet{

    public const ID = 33;

    private Role $role;

    public function __construct(Role $role, ?int $uid = null){
        parent::__construct($uid);
        $this->role = $role;
    }

    public function getRole(): Role{
        return $this->role;
    }

    public function jsonSerialize(): array{
        return [
            "uid" => $this->UID,
            "role" => $this->role->jsonSerialize()
        ];
    }

    public static function fromJson(array $data): self{
        return new self(
            Role::fromJson($data["role"]),
            $data["uid"]
        );
    }
}