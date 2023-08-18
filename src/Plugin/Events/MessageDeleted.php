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

use JaxkDev\DiscordBot\Models\Messages\Message;
use pocketmine\plugin\Plugin;

/**
 * Emitted when a message has been deleted.
 *
 * If message was made/updated before bot started it will only have message id, channel id and guild id.
 * If it was made/updated after bot started it will have the full message model.
 *
 * @see MessageUpdated
 * @see MessageSent
 */
class MessageDeleted extends DiscordBotEvent{

    /**
     * @var Message|array{"message_id": string, "channel_id": string, "guild_id": ?string}
     */
    private Message|array $message;

    /**
     * @param Message|array{"message_id": string, "channel_id": string, "guild_id": ?string} $message
     */
    public function __construct(Plugin $plugin, Message|array $message){
        parent::__construct($plugin);
        $this->message = $message;
    }

    /**
     * @return Message|array{"message_id": string, "channel_id": string, "guild_id": ?string}
     */
    public function getMessage(): Message|array{
        return $this->message;
    }
}