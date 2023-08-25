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

final class RoleDelete extends Packet{

    public const SERIALIZE_ID = 29;

    private string $guild_id;

    private string $role_id;

    private ?Role $cached_role;

    public function __construct(string $guild_id, string $role_id, ?Role $cached_role, ?int $uid = null){
        parent::__construct($uid);
        $this->guild_id = $guild_id;
        $this->role_id = $role_id;
        $this->cached_role = $cached_role;
    }

    public function getGuildId(): string{
        return $this->guild_id;
    }

    public function getRoleId(): string{
        return $this->role_id;
    }

    public function getCachedRole(): ?Role{
        return $this->cached_role;
    }

    public function binarySerialize(): BinaryStream{
        $stream = new BinaryStream();
        $stream->putInt($this->getUID());
        $stream->putString($this->guild_id);
        $stream->putString($this->role_id);
        $stream->putNullableSerializable($this->cached_role);
        return $stream;
    }

    public static function fromBinary(BinaryStream $stream): self{
        $uid = $stream->getInt();
        return new self(
            $stream->getString(),                          // guild_id
            $stream->getString(),                          // role_id
            $stream->getNullableSerializable(Role::class), // cached_role
            $uid
        );
    }
}