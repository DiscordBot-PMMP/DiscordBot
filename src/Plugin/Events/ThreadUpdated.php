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

use JaxkDev\DiscordBot\Models\Channels\Channel;
use pocketmine\plugin\Plugin;

/**
 * Emitted when a Thread gets created.
 *
 * @see ThreadCreatedEvent
 * @see ThreadDeleted
 */
final class ThreadUpdated extends DiscordBotEvent{

    private Channel $thread;

    /** Old thread if cached. */
    private ?Channel $old_thread;

    public function __construct(Plugin $plugin, Channel $thread, ?Channel $old_thread){
        parent::__construct($plugin);
        if(!$thread->getType()->isThread()){
            throw new \AssertionError("Channel must be a thread.");
        }
        if($old_thread !== null && !$old_thread->getType()->isThread()){
            throw new \AssertionError("Old channel must be a thread or null.");
        }
        $this->thread = $thread;
        $this->old_thread = $old_thread;
    }

    public function getThread(): Channel{
        return $this->thread;
    }

    public function getOldThread(): ?Channel{
        return $this->old_thread;
    }
}