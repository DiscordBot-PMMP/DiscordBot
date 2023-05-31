<?php
/*
 * DiscordBot, PocketMine-MP Plugin.
 *
 * Licensed under the Open Software License version 3.0 (OSL-3.0)
 * Copyright (C) 2020-present JaxkDev
 *
 * Twitter :: @JaxkDev
 * Discord :: JaxkDev#2698
 * Email   :: JaxkDev@gmail.com
 */

namespace JaxkDev\DiscordBot\Communication\Packets\Discord;

use JaxkDev\DiscordBot\Models\Member;
use JaxkDev\DiscordBot\Communication\Packets\Packet;

class MemberUpdate extends Packet{

    /** @var Member */
    private $member;

    public function __construct(Member $member){
        parent::__construct();
        $this->member = $member;
    }

    public function getMember(): Member{
        return $this->member;
    }

    public function __serialize(): array{
        return [
            $this->UID,
            $this->member
        ];
    }

    public function __unserialize(array $data): void{
        [
            $this->UID,
            $this->member
        ] = $data;
    }
}