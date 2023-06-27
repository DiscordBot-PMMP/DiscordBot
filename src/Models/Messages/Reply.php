<?php
/*
 * DiscordBot, PocketMine-MP Plugin.
 *
 * Licensed under the Open Software License version 3.0 (OSL-3.0)
 * Copyright (C) 2020-present JaxkDev
 *
 * Twitter :: @JaxkDev
 * Discord :: JaxkDev#2698
 * Email   :: JaxkDev@gmail.com
 */

namespace JaxkDev\DiscordBot\Models\Messages;

use JaxkDev\DiscordBot\Models\Messages\Embed\Embed;

class Reply extends Message{

    /** ID of message replying to. */
    private ?string $referenced_message_id;

    /**
     * @param Attachment[] $attachments
     * @param string[]     $users_mentioned
     * @param string[]     $roles_mentioned
     * @param string[]     $channels_mentioned
     */
    public function __construct(string $channel_id, ?string $referenced_message_id = null, ?string $id = null, string $content = "",
                                ?Embed $embed = null, ?string $author_id = null, ?string $guild_id = null, ?float $timestamp = null,
                                array $attachments = [], bool $everyone_mentioned = false, array $users_mentioned = [],
                                array $roles_mentioned = [], array $channels_mentioned = []){
        parent::__construct($channel_id, $id, $content, $embed, $author_id, $guild_id, $timestamp, $attachments,
            $everyone_mentioned, $users_mentioned, $roles_mentioned, $channels_mentioned);
        $this->setReferencedMessageId($referenced_message_id);
    }

    public function getReferencedMessageId(): ?string{
        return $this->referenced_message_id;
    }

    public function setReferencedMessageId(?string $referenced_message_id): void{
        $this->referenced_message_id = $referenced_message_id;
    }

    //----- Serialization -----//

    public function __serialize(): array{
        return [
            $this->id,
            $this->content,
            $this->embed,
            $this->author_id,
            $this->channel_id,
            $this->guild_id,
            $this->timestamp,
            $this->attachments,
            $this->everyone_mentioned,
            $this->users_mentioned,
            $this->roles_mentioned,
            $this->channels_mentioned,
            $this->referenced_message_id
        ];
    }

    public function __unserialize(array $data): void{
        [
            $this->id,
            $this->content,
            $this->embed,
            $this->author_id,
            $this->channel_id,
            $this->guild_id,
            $this->timestamp,
            $this->attachments,
            $this->everyone_mentioned,
            $this->users_mentioned,
            $this->roles_mentioned,
            $this->channels_mentioned,
            $this->referenced_message_id
        ] = $data;
    }
}