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

namespace JaxkDev\DiscordBot\Communication\Packets\Plugin;

use JaxkDev\DiscordBot\Communication\BinaryStream;
use JaxkDev\DiscordBot\Communication\Packets\Packet;
use JaxkDev\DiscordBot\Models\Role;

final class RequestUpdateRole extends Packet{

    public const SERIALIZE_ID = 82;

    private Role $role;

    private ?string $reason;

    public function __construct(Role $role, ?string $reason = null, ?int $uid = null){
        parent::__construct($uid);
        $this->role = $role;
        $this->reason = $reason;
    }

    public function getRole(): Role{
        return $this->role;
    }

    public function getReason(): ?string{
        return $this->reason;
    }

    public function binarySerialize(): BinaryStream{
        $stream = new BinaryStream();
        $stream->putInt($this->getUID());
        $stream->putSerializable($this->role);
        $stream->putNullableString($this->reason);
        return $stream;
    }

    public static function fromBinary(BinaryStream $stream): self{
        $uid = $stream->getInt();
        return new self(
            $stream->getSerializable(Role::class), // role
            $stream->getNullableString(),          // reason
            $uid
        );
    }
}