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

use JaxkDev\DiscordBot\Plugin\Utils;
use pocketmine\plugin\Plugin;

/**
 * Emitted when a message is pinned or unpinned, note we dont know what message was pinned or unpinned only the channel ID.
 */
class ChannelPinsUpdated extends DiscordBotEvent{

    private string $channel_id;

    public function __construct(Plugin $plugin, string $channel_id){
        parent::__construct($plugin);
        if(Utils::validDiscordSnowflake($channel_id)){
            $this->channel_id = $channel_id;
        }else{
            throw new \AssertionError("Invalid channel ID given.");
        }
    }

    public function getChannelId(): string{
        return $this->channel_id;
    }
}