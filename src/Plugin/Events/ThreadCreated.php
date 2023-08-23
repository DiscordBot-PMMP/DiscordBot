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

use JaxkDev\DiscordBot\Models\Channels\Channel;
use pocketmine\plugin\Plugin;

/**
 * Emitted when a Thread gets created.
 *
 * @see ThreadDeleted
 * @see ThreadUpdated
 */
final class ThreadCreated extends DiscordBotEvent{

    private Channel $thread;

    public function __construct(Plugin $plugin, Channel $thread){
        parent::__construct($plugin);
        if(!$thread->getType()->isThread()){
            throw new \AssertionError("Channel must be a thread.");
        }
        $this->thread = $thread;
    }

    public function getThread(): Channel{
        return $this->thread;
    }
}