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

use JaxkDev\DiscordBot\Models\Channels\ChannelType;
use pocketmine\plugin\Plugin;

/**
 * Emitted when a Thread gets created.
 *
 * @see ThreadCreatedEvent
 * @see ThreadUpdated
 */
class ThreadDeleted extends DiscordBotEvent{

    private ChannelType $type;

    private string $id;

    private string $guild_id;

    private string $parent_id;

    public function __construct(Plugin $plugin, ChannelType $type, string $id, string $guild_id, string $parent_id){
        parent::__construct($plugin);
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