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

final class RequestRemoveRole extends Packet{

    public const SERIALIZE_ID = 73;

    private string $guild_id;

    private string $user_id;

    private string $role_id;

    private ?string $reason;

    public function __construct(string $guild_id, string $user_id, string $role_id, ?string $reason = null,
                                ?int $uid = null){
        parent::__construct($uid);
        $this->guild_id = $guild_id;
        $this->user_id = $user_id;
        $this->role_id = $role_id;
        $this->reason = $reason;
    }

    public function getGuildId(): string{
        return $this->guild_id;
    }

    public function getUserId(): string{
        return $this->user_id;
    }

    public function getRoleId(): string{
        return $this->role_id;
    }

    public function getReason(): ?string{
        return $this->reason;
    }

    public function binarySerialize(): BinaryStream{
        $stream = new BinaryStream();
        $stream->putInt($this->getUID());
        $stream->putString($this->guild_id);
        $stream->putString($this->user_id);
        $stream->putString($this->role_id);
        $stream->putNullableString($this->reason);
        return $stream;
    }

    public static function fromBinary(BinaryStream $stream): self{
        $uid = $stream->getInt();
        return new self(
            $stream->getString(),         // guild_id
            $stream->getString(),         // user_id
            $stream->getString(),         // role_id
            $stream->getNullableString(), // reason
            $uid
        );
    }
}