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
 * Emitted when a channel gets updated.
 *
 * @see ChannelDeleted
 * @see ChannelCreated
 */
final class ChannelUpdated extends DiscordBotEvent{

    private Channel $channel;

    public function __construct(Plugin $plugin, Channel $channel){
        parent::__construct($plugin);
        if(!$channel->getType()->isThread()){
            throw new \AssertionError("Channel cannot be a thread.");
        }
        $this->channel = $channel;
    }

    public function getChannel(): Channel{
        return $this->channel;
    }
}