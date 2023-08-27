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
use JaxkDev\DiscordBot\Models\Member;

final class MemberLeave extends Packet{

    public const SERIALIZE_ID = 215;

    private string $guild_id;

    private string $user_id;

    private ?Member $cached_member;

    public function __construct(string $guild_id, string $user_id, ?Member $cached_member, ?int $uid = null){
        parent::__construct($uid);
        $this->guild_id = $guild_id;
        $this->user_id = $user_id;
        $this->cached_member = $cached_member;
    }

    public function getGuildId(): string{
        return $this->guild_id;
    }

    public function getUserId(): string{
        return $this->user_id;
    }

    public function getCachedMember(): ?Member{
        return $this->cached_member;
    }

    public function binarySerialize(): BinaryStream{
        $stream = new BinaryStream();
        $stream->putInt($this->getUID());
        $stream->putString($this->guild_id);
        $stream->putString($this->user_id);
        $stream->putNullableSerializable($this->cached_member);
        return $stream;
    }

    public static function fromBinary(BinaryStream $stream): self{
        $uid = $stream->getInt();
        return new self(
            $stream->getString(),                            // guild_id
            $stream->getString(),                            // user_id
            $stream->getNullableSerializable(Member::class), // cached_member (nullable
            $uid
        );
    }
}