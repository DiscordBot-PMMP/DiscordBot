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
use JaxkDev\DiscordBot\Communication\Packets\Packet;
use JaxkDev\DiscordBot\Models\Member;

class MemberUpdate extends Packet{

    public const SERIALIZE_ID = 19;

    private Member $member;

    public function __construct(Member $member, ?int $uid = null){
        parent::__construct($uid);
        $this->member = $member;
    }

    public function getMember(): Member{
        return $this->member;
    }

    public function binarySerialize(): BinaryStream{
        $stream = new BinaryStream();
        $stream->putSerializable($this->member);
        return $stream;
    }

    public static function fromBinary(BinaryStream $stream): self{
        return new self(
            $stream->getSerializable(Member::class)
        );
    }
}