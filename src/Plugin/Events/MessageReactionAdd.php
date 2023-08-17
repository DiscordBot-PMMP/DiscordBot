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

use Discord\WebSockets\Events\MessageReactionRemove;
use JaxkDev\DiscordBot\Plugin\Utils;
use pocketmine\plugin\Plugin;

/**
 * Emitted when a reaction is added to a message.
 *
 * @see MessageReactionRemove
 * @see MessageReactionRemoveAll
 * @see MessageReactionRemoveEmoji
 */
class MessageReactionAdd extends DiscordBotEvent{

    /** @var string|null Can be null for DMs */
    private ?string $guild_id;

    private string $channel_id;

    private string $message_id;

    private string $emoji;

    private string $user_id;

    public function __construct(Plugin $plugin, ?string $guild_id, string $channel_id, string $message_id,
                                string $emoji, string $user_id){
        parent::__construct($plugin);
        if($guild_id !== null and !Utils::validDiscordSnowflake($guild_id)){
            throw new \AssertionError("Invalid guild ID given.");
        }
        if(!Utils::validDiscordSnowflake($channel_id)){
            throw new \AssertionError("Invalid channel ID given.");
        }
        if(!Utils::validDiscordSnowflake($message_id)){
            throw new \AssertionError("Invalid message ID given.");
        }
        if(!Utils::validDiscordSnowflake($user_id)){
            throw new \AssertionError("Invalid user ID given.");
        }
        $this->guild_id = $guild_id;
        $this->channel_id = $channel_id;
        $this->message_id = $message_id;
        $this->emoji = $emoji;
        $this->user_id = $user_id;
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

    public function getEmoji(): string{
        return $this->emoji;
    }

    public function getUserId(): string{
        return $this->user_id;
    }
}