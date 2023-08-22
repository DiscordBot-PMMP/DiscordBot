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

use JaxkDev\DiscordBot\Models\User;
use pocketmine\plugin\Plugin;

/**
 * Emitted when the bot user is updated, eg changes username etc.
 */
final class BotUserUpdated extends DiscordBotEvent{

    private User $bot;

    public function __construct(Plugin $plugin, User $bot){
        parent::__construct($plugin);
        $this->bot = $bot;
    }

    public function getBot(): User{
        return $this->bot;
    }
}