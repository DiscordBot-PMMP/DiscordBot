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

    public const SERIALIZE_ID = 447;

    private Role $role;

    /** @var string|null If changing icon, set this to validImageData. */
    private ?string $new_icon_data;

    private ?string $reason;

    public function __construct(Role $role, ?string $new_icon_data, ?string $reason = null, ?int $uid = null){
        parent::__construct($uid);
        $this->role = $role;
        $this->new_icon_data = $new_icon_data;
        $this->reason = $reason;
    }

    public function getRole(): Role{
        return $this->role;
    }

    public function getNewIconData(): ?string{
        return $this->new_icon_data;
    }

    public function getReason(): ?string{
        return $this->reason;
    }

    public function binarySerialize(): BinaryStream{
        $stream = new BinaryStream();
        $stream->putInt($this->getUID());
        $stream->putSerializable($this->role);
        $stream->putNullableString($this->new_icon_data);
        $stream->putNullableString($this->reason);
        return $stream;
    }

    public static function fromBinary(BinaryStream $stream): self{
        $uid = $stream->getInt();
        return new self(
            $stream->getSerializable(Role::class), // role
            $stream->getNullableString(),          // new_icon_data
            $stream->getNullableString(),          // reason
            $uid
        );
    }
}