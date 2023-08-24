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
 *  If it was made after bot started it may have the full message model (if cached).
 *
 * @see MessageDeleted
 * @see MessageSent
 */
final class MessageUpdated extends DiscordBotEvent{

    /** @var Message|array{"message_id": string, "channel_id": string, "guild_id": ?string} */
    private Message|array $message;

    /** @param Message|array{"message_id": string, "channel_id": string, "guild_id": ?string} $message */
    public function __construct(Plugin $plugin, Message|array $message){
        parent::__construct($plugin);
        if(!$message instanceof Message){
            if(!isset($message["message_id"], $message["channel_id"])){
                throw new \AssertionError("Invalid message given, missing message_id or channel_id.");
            }
            if(!Utils::validDiscordSnowflake($message["message_id"])){
                throw new \AssertionError("Invalid message_id given.");
            }
            if(!Utils::validDiscordSnowflake($message["channel_id"])){
                throw new \AssertionError("Invalid channel_id given.");
            }
            if(isset($message["guild_id"]) && !Utils::validDiscordSnowflake($message["guild_id"])){
                throw new \AssertionError("Invalid guild_id given.");
            }
        }
        $this->message = $message;
    }

    /* @return Message|array{"message_id": string, "channel_id": string, "guild_id": ?string} */
    public function getMessage(): Message|array{
        return $this->message;
    }
}