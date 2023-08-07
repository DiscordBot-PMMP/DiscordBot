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

    private string $member_id;

    public function __construct(Plugin $plugin, string $emoji, string $message_id, string $channel_id, string $member_id){
        parent::__construct($plugin);
        $this->emoji = $emoji;
        $this->message_id = $message_id;
        if(Utils::validDiscordSnowflake($channel_id)){
            $this->channel_id = $channel_id;
        }else{
            throw new \AssertionError("Invalid channel id given.");
        }
        if(Utils::validDiscordSnowflake($member_id)){
            $this->member_id = $member_id;
        }else{
            throw new \AssertionError("Invalid member id given.");
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

    public function getMemberId(): string{
        return $this->member_id;
    }
}