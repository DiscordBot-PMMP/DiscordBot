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

use JaxkDev\DiscordBot\Models\Invite;
use pocketmine\plugin\Plugin;

/**
 * Emitted when an invite gets created.
 *
 * @see InviteDeleted
 */
final class InviteCreated extends DiscordBotEvent{

    private Invite $invite;

    public function __construct(Plugin $plugin, Invite $invite){
        parent::__construct($plugin);
        $this->invite = $invite;
    }

    public function getInvite(): Invite{
        return $this->invite;
    }
}