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
use JaxkDev\DiscordBot\Models\User;
use JaxkDev\DiscordBot\Communication\Packets\Packet;

class MemberJoin extends Packet{

    public const ID = 48;

    private Member $member;

    private User $user;

    public function __construct(Member $member, User $user, ?int $uid = null){
        parent::__construct($uid);
        $this->member = $member;
        $this->user = $user;
    }

    public function getMember(): Member{
        return $this->member;
    }

    public function getUser(): User{
        return $this->user;
    }

    public function jsonSerialize(): array{
        return [
            "uid" => $this->UID,
            "member" => $this->member->jsonSerialize(),
            "user" => $this->user->jsonSerialize()
        ];
    }

    public static function fromJson(array $data): self{
        return new self(
            Member::fromJson($data["member"]),
            User::fromJson($data["user"]),
            $data["uid"]
        );
    }
}