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
 * Emitted when ALL reactions are removed from a message.
 *
 * @see MessageReactionAdd
 * @see MessageReactionRemove
 * @see MessageReactionRemoveEmoji
 */
class MessageReactionRemoveAll extends DiscordBotEvent{

    private ?string $guild_id;

    private string $channel_id;

    private string $message_id;

    public function __construct(Plugin $plugin, ?string $guild_id, string $channel_id, string $message_id){
        parent::__construct($plugin);
        if($guild_id !== null && !Utils::validDiscordSnowflake($guild_id)){
            throw new \AssertionError("Invalid guild ID given.");
        }
        if(!Utils::validDiscordSnowflake($channel_id)){
            throw new \AssertionError("Invalid channel ID given.");
        }
        if(!Utils::validDiscordSnowflake($message_id)){
            throw new \AssertionError("Invalid message ID given.");
        }
        $this->guild_id = $guild_id;
        $this->channel_id = $channel_id;
        $this->message_id = $message_id;
    }

    public function getGuildId(): ?string{
        return $this->guild_id;
    }

    public function getChannelId(): string{
        return $this->channel_id;
    }

    public function getMessageId(): string{
        return $this->message_id;
    }
}