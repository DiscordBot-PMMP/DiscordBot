<?php
/*
 * DiscordBot, PocketMine-MP Plugin.
 *
 * Licensed under the Open Software License version 3.0 (OSL-3.0)
 * Copyright (C) 2020-2021 JaxkDev
 *
 * Twitter :: @JaxkDev
 * Discord :: JaxkDev#2698
 * Email   :: JaxkDev@gmail.com
 */

namespace JaxkDev\DiscordBot\Plugin\Events;

use JaxkDev\DiscordBot\Models\Member;
use pocketmine\plugin\Plugin;

/**
 * Emitted when a member leaves a discord server.
 * 
 * @see MemberJoined
 * @see MemberUpdated
 */
class MemberLeft extends DiscordBotEvent{

    /** @var Member */
    private $member;

    public function __construct(Plugin $plugin, Member $member){
        parent::__construct($plugin);
        $this->member = $member;
    }

    public function getMember(): Member{
        return $this->member;
    }
}