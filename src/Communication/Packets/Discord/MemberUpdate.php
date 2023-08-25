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

final class MemberUpdate extends Packet{

    public const SERIALIZE_ID = 19;

    private Member $member;

    /** Old member if cached. */
    private ?Member $old_member;

    public function __construct(Member $member, ?Member $old_member, ?int $uid = null){
        parent::__construct($uid);
        $this->member = $member;
        $this->old_member = $old_member;
    }

    public function getMember(): Member{
        return $this->member;
    }

    public function getOldMember(): ?Member{
        return $this->old_member;
    }

    public function binarySerialize(): BinaryStream{
        $stream = new BinaryStream();
        $stream->putInt($this->getUID());
        $stream->putSerializable($this->member);
        $stream->putNullableSerializable($this->old_member);
        return $stream;
    }

    public static function fromBinary(BinaryStream $stream): self{
        $uid = $stream->getInt();
        return new self(
            $stream->getSerializable(Member::class),         // member
            $stream->getNullableSerializable(Member::class), // old_member
            $uid
        );
    }
}