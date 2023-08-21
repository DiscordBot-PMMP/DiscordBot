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
 * Emitted when multiple messages have been deleted at once (in bulk).
 *
 * If message was made/updated before bot started it will only have message id listed in $message_ids.
 * If it was made/updated after bot started it may have the full message model (if cached) in $messages.
 *
 * @see MessageUpdated
 * @see MessageSent
 * @see MessageDeletd
 */
class MessagesBulkDeleted extends DiscordBotEvent{

    private ?string $guild_id;

    private string $channel_id;

    /** @var string[] Message deleted will either be just its ID here, or model in $messages, never both. */
    private array $message_ids;

    /** @var Message[] */
    private array $messages;

    /**
     * @param string[]  $message_ids
     * @param Message[] $messages
     */
    public function __construct(Plugin $plugin, ?string $guild_id, string $channel_id, array $message_ids, array $messages){
        parent::__construct($plugin);
        $this->guild_id = $guild_id;
        $this->channel_id = $channel_id;
        $this->message_ids = $message_ids;
        $this->messages = $messages;
    }

    public function getGuildId(): ?string{
        return $this->guild_id;
    }

    public function getChannelId(): string{
        return $this->channel_id;
    }

    /** @return string[] */
    public function getMessageIds(): array{
        return $this->message_ids;
    }

    /** @return Message[] */
    public function getMessages(): array{
        return $this->messages;
    }
}