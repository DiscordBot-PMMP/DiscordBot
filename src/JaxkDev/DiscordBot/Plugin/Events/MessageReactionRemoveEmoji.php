<?php
/*
 * DiscordBot, PocketMine-MP Plugin.
 *
 * Licensed under the Open Software License version 3.0 (OSL-3.0)
 * Copyright (C) 2020-2021 JaxkDev
 *
 * Twitter :: @JaxkDev
 * Discord :: JaxkDev#2698
 * Email   :: JaxkDev@gmail.com
 */

namespace JaxkDev\DiscordBot\Plugin\Events;

use JaxkDev\DiscordBot\Models\Channels\Channel;
use pocketmine\plugin\Plugin;

/**
 * Emitted when ALL reactions of a specific emoji are removed from a message.
 *
 * @see MessageReactionAdd
 * @see MessageReactionRemove
 * @see MessageReactionRemoveAll
 */
class MessageReactionRemoveEmoji extends DiscordBotEvent{

    /** @var string */
    private $emoji;

    /** @var string */
    private $message_id;

    /** @var Channel */
    private $channel;

    public function __construct(Plugin $plugin, string $emoji, string $message_id, Channel $channel){
        parent::__construct($plugin);
        $this->emoji = $emoji;
        $this->message_id = $message_id;
        $this->channel = $channel;
    }

    public function getEmoji(): string{
        return $this->emoji;
    }

    public function getMessageId(): string{
        return $this->message_id;
    }

    public function getChannel(): Channel{
        return $this->channel;
    }
}