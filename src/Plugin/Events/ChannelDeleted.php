<?php
/*
 * DiscordBot, PocketMine-MP Plugin.
 *
 * Licensed under the Open Software License version 3.0 (OSL-3.0)
 * Copyright (C) 2020-present JaxkDev
 *
 * Twitter :: @JaxkDev
 * Discord :: JaxkDev#2698
 * Email   :: JaxkDev@gmail.com
 */

namespace JaxkDev\DiscordBot\Plugin\Events;

use JaxkDev\DiscordBot\Models\Channels\GuildChannel;
use pocketmine\plugin\Plugin;

/**
 * Emitted when a channel gets deleted.
 * 
 * @see ChannelUpdated
 * @see ChannelCreated
 */
class ChannelDeleted extends DiscordBotEvent{

    private GuildChannel $channel;

    public function __construct(Plugin $plugin, GuildChannel $channel){
        parent::__construct($plugin);
        $this->channel = $channel;
    }

    public function getChannel(): GuildChannel{
        return $this->channel;
    }
}