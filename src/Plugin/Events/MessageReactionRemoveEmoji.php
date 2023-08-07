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
 * Emitted when ALL reactions of a specific emoji are removed from a message.
 *
 * @see MessageReactionAdd
 * @see MessageReactionRemove
 * @see MessageReactionRemoveAll
 */
class MessageReactionRemoveEmoji extends DiscordBotEvent{

    private string $emoji;

    private string $message_id;

    private string $channel_id;

    public function __construct(Plugin $plugin, string $emoji, string $message_id, string $channel_id){
        parent::__construct($plugin);
        $this->emoji = $emoji;
        $this->message_id = $message_id;
        if(Utils::validDiscordSnowflake($channel_id)){
            $this->channel_id = $channel_id;
        }else{
            throw new \AssertionError("Invalid channel id given.");
        }
    }

    public function getEmoji(): string{
        return $this->emoji;
    }

    public function getMessageId(): string{
        return $this->message_id;
    }

    public function getChannelId(): string{
        return $this->channel_id;
    }
}