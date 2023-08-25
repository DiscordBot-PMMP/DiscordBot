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

use JaxkDev\DiscordBot\Models\Messages\Message;
use JaxkDev\DiscordBot\Plugin\Utils;
use pocketmine\plugin\Plugin;

/**
 * Emitted when a message has been updated.
 *
 *  If message was made before bot started it will only have message id, channel id and guild id.
 *  If it was made after bot started it may have the full message models (if cached).
 *
 * @see MessageDeleted
 * @see MessageSent
 */
final class MessageUpdated extends DiscordBotEvent{

    private ?string $guild_id;

    private string $channel_id;

    private string $message_id;

    /** Null if no new message was provided from discord. */
    private ?Message $new_message;

    /** Null if old message was not cached. */
    private ?Message $old_message;

    public function __construct(Plugin $plugin, ?string $guild_id, string $channel_id, string $message_id,
                                ?Message $new_message, ?Message $old_message){
        parent::__construct($plugin);
        if($guild_id !== null && !Utils::validDiscordSnowflake($guild_id)){
            throw new \AssertionError("Invalid guild_id given.");
        }
        if(!Utils::validDiscordSnowflake($channel_id)){
            throw new \AssertionError("Invalid channel_id given.");
        }
        if(!Utils::validDiscordSnowflake($message_id)){
            throw new \AssertionError("Invalid message_id given.");
        }
        $this->guild_id = $guild_id;
        $this->channel_id = $channel_id;
        $this->message_id = $message_id;
        $this->new_message = $new_message;
        $this->old_message = $old_message;
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

    public function getNewMessage(): ?Message{
        return $this->new_message;
    }

    public function getOldMessage(): ?Message{
        return $this->old_message;
    }
}