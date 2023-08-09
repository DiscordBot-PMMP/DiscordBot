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
 * Emitted when a reaction is removed from a message.
 *
 * @see MessageReactionAdd
 * @see MessageReactionRemoveAll
 * @see MessageReactionRemoveEmoji
 */
class MessageReactionRemove extends DiscordBotEvent{

    private string $emoji;

    private string $message_id;

    private string $channel_id;

    private string $guild_id;

    private string $user_id;

    public function __construct(Plugin $plugin, string $emoji, string $message_id, string $channel_id, string $guild_id, string $user_id){
        parent::__construct($plugin);
        $this->emoji = $emoji;
        $this->message_id = $message_id;
        if(Utils::validDiscordSnowflake($channel_id)){
            $this->channel_id = $channel_id;
        }else{
            throw new \AssertionError("Invalid channel_id provided.");
        }
        if(Utils::validDiscordSnowflake($guild_id)){
            $this->guild_id = $guild_id;
        }else{
            throw new \AssertionError("Invalid guild_id provided.");
        }
        if(Utils::validDiscordSnowflake($user_id)){
            $this->user_id = $user_id;
        }else{
            throw new \AssertionError("Invalid user_id provided.");
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

    public function getGuildId(): string{
        return $this->guild_id;
    }

    public function getUserId(): string{
        return $this->user_id;
    }
}