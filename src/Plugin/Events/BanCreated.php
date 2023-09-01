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

use JaxkDev\DiscordBot\Models\Ban;
use pocketmine\plugin\Plugin;

/**
 * Emitted when a ban gets created (AKA Initialised).
 *
 * @see BanDeleted (AKA Revoked)
 */
final class BanCreated extends DiscordBotEvent{

    private Ban $ban;

    public function __construct(Plugin $plugin, Ban $ban){
        parent::__construct($plugin);
        $this->ban = $ban;
    }

    public function getBan(): Ban{
        return $this->ban;
    }
}