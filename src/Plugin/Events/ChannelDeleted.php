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
 * Emitted when a channel gets deleted.
 * 
 * @see ChannelUpdated
 * @see ChannelCreated
 */
class ChannelDeleted extends DiscordBotEvent{

    private string $channel_id;

    public function __construct(Plugin $plugin, string $channel_id){
        parent::__construct($plugin);
        if(Utils::validDiscordSnowflake($channel_id)){
            $this->channel_id = $channel_id;
        }else{
            throw new \AssertionError("Invalid channel id given.");
        }
    }

    public function getChannelId(): string{
        return $this->channel_id;
    }
}