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

namespace JaxkDev\DiscordBot\Plugin\Events;

use JaxkDev\DiscordBot\Models\Member;
use pocketmine\plugin\Plugin;

/**
 * Emitted when a member joins a discord guild.
 *
 * @see MemberLeft
 * @see MemberUpdated
 */
class MemberJoined extends DiscordBotEvent{

    private Member $member;

    public function __construct(Plugin $plugin, Member $member){
        parent::__construct($plugin);
        $this->member = $member;
    }

    public function getMember(): Member{
        return $this->member;
    }
}