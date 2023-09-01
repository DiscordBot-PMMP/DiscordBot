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

namespace JaxkDev\DiscordBot\Plugin\Events;

use JaxkDev\DiscordBot\Models\Member;
use pocketmine\plugin\Plugin;

/**
 * Emitted when a member is updated, eg roles, nickname etc.
 *
 * @see MemberJoined
 * @see MemberLeft
 */
final class MemberUpdated extends DiscordBotEvent{

    private Member $member;

    /** Old member if cached. */
    private ?Member $old_member;

    public function __construct(Plugin $plugin, Member $member, ?Member $old_member){
        parent::__construct($plugin);
        $this->member = $member;
        $this->old_member = $old_member;
    }

    public function getMember(): Member{
        return $this->member;
    }

    public function getOldMember(): ?Member{
        return $this->old_member;
    }
}