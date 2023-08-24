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

use JaxkDev\DiscordBot\Models\Channels\ChannelType;
use JaxkDev\DiscordBot\Plugin\Utils;
use pocketmine\plugin\Plugin;

/**
 * Emitted when a Thread gets created.
 *
 * @see ThreadCreatedEvent
 * @see ThreadUpdated
 */
final class ThreadDeleted extends DiscordBotEvent{

    private ChannelType $type;

    private string $id;

    private string $guild_id;

    private string $parent_id;

    public function __construct(Plugin $plugin, ChannelType $type, string $id, string $guild_id, string $parent_id){
        parent::__construct($plugin);
        if(!$type->isThread()){
            throw new \AssertionError("Channel must be a thread.");
        }
        if(!Utils::validDiscordSnowflake($id)){
            throw new \AssertionError("Invalid id given.");
        }
        if(!Utils::validDiscordSnowflake($guild_id)){
            throw new \AssertionError("Invalid guild_id given.");
        }
        if(!Utils::validDiscordSnowflake($parent_id)){
            throw new \AssertionError("Invalid parent_id given.");
        }
        $this->type = $type;
        $this->id = $id;
        $this->guild_id = $guild_id;
        $this->parent_id = $parent_id;
    }

    public function getType(): ChannelType{
        return $this->type;
    }

    public function getId(): string{
        return $this->id;
    }

    public function getGuildId(): string{
        return $this->guild_id;
    }

    public function getParentId(): string{
        return $this->parent_id;
    }
}