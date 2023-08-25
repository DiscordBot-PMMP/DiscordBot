<?php

/*
 * DiscordBot, PocketMine-MP Plugin.
 *
 * Licensed under the Open Software License version 3.0 (OSL-3.0)
 * Copyright (C) 2020-present JaxkDev
 *
 * Discord :: JaxkDev
 * Email   :: JaxkDev@gmail.com
 */

namespace JaxkDev\DiscordBot\Communication\Packets\Discord;

use JaxkDev\DiscordBot\Communication\BinaryStream;
use JaxkDev\DiscordBot\Communication\Packets\Packet;
use JaxkDev\DiscordBot\Models\Role;

final class RoleUpdate extends Packet{

    public const SERIALIZE_ID = 30;

    private Role $role;

    private ?Role $old_role;

    public function __construct(Role $role, ?Role $old_role, ?int $uid = null){
        parent::__construct($uid);
        $this->role = $role;
        $this->old_role = $old_role;
    }

    public function getRole(): Role{
        return $this->role;
    }

    public function getOldRole(): ?Role{
        return $this->old_role;
    }

    public function binarySerialize(): BinaryStream{
        $stream = new BinaryStream();
        $stream->putInt($this->getUID());
        $stream->putSerializable($this->role);
        $stream->putNullableSerializable($this->old_role);
        return $stream;
    }

    public static function fromBinary(BinaryStream $stream): self{
        $uid = $stream->getInt();
        return new self(
            $stream->getSerializable(Role::class),         // role
            $stream->getNullableSerializable(Role::class), // old_role
            $uid
        );
    }
}