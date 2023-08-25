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
use JaxkDev\DiscordBot\Plugin\Utils;
use pocketmine\plugin\Plugin;

/**
 * Emitted when a channel gets deleted.
 *
 * @see ChannelUpdated
 * @see ChannelCreated
 */
final class ChannelDeleted extends DiscordBotEvent{

    private ?string $guild_id;

    private string $channel_id;

    private ?Channel $cached_channel;

    public function __construct(Plugin $plugin, ?string $guild_id, string $channel_id, ?Channel $cached_channel){
        parent::__construct($plugin);
        if($guild_id !== null && !Utils::validDiscordSnowflake($guild_id)){
            throw new \AssertionError("Invalid guild id given.");
        }
        if(!Utils::validDiscordSnowflake($channel_id)){
            throw new \AssertionError("Invalid channel id given.");
        }
        if($cached_channel !== null && !$cached_channel->getType()->isGuild()){
            throw new \AssertionError("Cached channel must be a guild channel or null.");
        }
        $this->guild_id = $guild_id;
        $this->channel_id = $channel_id;
        $this->cached_channel = $cached_channel;
    }

    public function getGuildId(): ?string{
        return $this->guild_id;
    }

    public function getChannelId(): string{
        return $this->channel_id;
    }

    public function getCachedChannel(): ?Channel{
        return $this->cached_channel;
    }
}