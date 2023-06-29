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

use JaxkDev\DiscordBot\Models\Member;
use JaxkDev\DiscordBot\Communication\Packets\Packet;

class MemberUpdate extends Packet{

    public const ID = 50;

    private Member $member;

    public function __construct(Member $member, ?int $uid = null){
        parent::__construct($uid);
        $this->member = $member;
    }

    public function getMember(): Member{
        return $this->member;
    }

    public function jsonSerialize(): array{
        return [
            "uid" => $this->UID,
            "member" => $this->member->jsonSerialize()
        ];
    }

    public static function fromJson(array $data): self{
        return new self(
            Member::fromJson($data["member"]),
            $data["uid"]
        );
    }
}