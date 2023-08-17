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
use JaxkDev\DiscordBot\Models\Presence\Presence;
use pocketmine\plugin\Plugin;

/**
 * Emitted when a members presence is updated.
 */
class PresenceUpdated extends DiscordBotEvent{

    private Member $member;

    private Presence $new_presence;

    public function __construct(Plugin $plugin, Member $member, Presence $new_presence){
        parent::__construct($plugin);
        $this->member = $member;
        $this->new_presence = $new_presence;
    }

    public function getMember(): Member{
        return $this->member;
    }

    /**
     * Alias, Member still has old presence at time of event.
     * @see Member::getPresence()
     */
    public function getOldPresence(): ?Presence{
        return $this->member->getPresence();
    }

    public function getNewPresence(): Presence{
        return $this->new_presence;
    }
}