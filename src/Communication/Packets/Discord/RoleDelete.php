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

namespace JaxkDev\DiscordBot\Communication\Packets\Discord;

use JaxkDev\DiscordBot\Communication\Packets\Packet;

class RoleDelete extends Packet{

    public const ID = 60;

    private string $role_id;

    public function __construct(string $role_id, ?int $uid = null){
        parent::__construct($uid);
        $this->role_id = $role_id;
    }

    public function getRoleId(): string{
        return $this->role_id;
    }

    public function jsonSerialize(): array{
        return [
            "uid" => $this->UID,
            "role_id" => $this->role_id
        ];
    }

    public static function fromJson(array $data): self{
        return new self(
            $data["role_id"],
            $data["uid"]
        );
    }
}