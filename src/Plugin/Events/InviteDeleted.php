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

use pocketmine\plugin\Plugin;

/**
 * Emitted when a invite gets deleted/revoked/expires.
 * 
 * @see InviteCreated
 */
class InviteDeleted extends DiscordBotEvent{

    private string $invite_code;

    public function __construct(Plugin $plugin, string $invite_code){
        parent::__construct($plugin);
        $this->invite_code = $invite_code;
    }

    public function getInviteCode(): string{
        return $this->invite_code;
    }
}