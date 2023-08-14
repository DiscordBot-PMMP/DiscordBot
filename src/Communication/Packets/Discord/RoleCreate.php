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

use JaxkDev\DiscordBot\Communication\BinaryStream;
use JaxkDev\DiscordBot\Models\Role;
use JaxkDev\DiscordBot\Communication\Packets\Packet;

class RoleCreate extends Packet{

    public const SERIALIZE_ID = 28;

    private Role $role;

    public function __construct(Role $role, ?int $uid = null){
        parent::__construct($uid);
        $this->role = $role;
    }

    public function getRole(): Role{
        return $this->role;
    }

    public function binarySerialize(): BinaryStream{
        $stream = new BinaryStream();
        $stream->putSerializable($this->role);
        return $stream;
    }

    public static function fromBinary(BinaryStream $stream): self{
        return new self(
            $stream->getSerializable(Role::class)
        );
    }
}