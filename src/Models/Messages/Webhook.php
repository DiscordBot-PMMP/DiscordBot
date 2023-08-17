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

namespace JaxkDev\DiscordBot\Models\Messages;

use JaxkDev\DiscordBot\Models\Messages\Embed\Embed;

class Webhook extends Message{

    /** @var Embed[] Max 10 in webhook message. */
    private array $embeds = [];

    private string $webhook_id;

    /**
     * @param Attachment[] $attachments
     * @param string[]     $users_mentioned
     * @param string[]     $roles_mentioned
     * @param string[]     $channels_mentioned
     */
    public function __construct(string $channel_id, string $webhook_id, array $embeds = [], ?string $id = null, string $content = "",
                                ?string $author_id = null, ?string $guild_id = null, ?float $timestamp = null,
                                array $attachments = [], bool $everyone_mentioned = false, array $users_mentioned = [],
                                array $roles_mentioned = [], array $channels_mentioned = []){
        parent::__construct($channel_id, $id, $content, null, $author_id, $guild_id, $timestamp, $attachments,
            $everyone_mentioned, $users_mentioned, $roles_mentioned, $channels_mentioned);
        $this->setWebhookId($webhook_id);
        $this->setEmbeds($embeds);
    }

    //Hmm...
    public function getEmbed(): ?Embed{
        throw new \AssertionError("Webhook messages must use getEmbeds()");
    }

    public function setEmbed(?Embed $embed): void{
        if($embed !== null) throw new \AssertionError("Webhook messages must use setEmbeds()");
    }

    /** @return Embed[] */
    public function getEmbeds(): array{
        return $this->embeds;
    }

    /** @param Embed[] $embeds */
    public function setEmbeds(array $embeds): void{
        if(sizeof($embeds) > 10){
            throw new \AssertionError("Webhook messages are limited to 10 embeds.");
        }
        $this->embeds = $embeds;
    }

    public function getWebhookId(): string{
        return $this->webhook_id;
    }

    public function setWebhookId(string $webhook_id): void{
        $this->webhook_id = $webhook_id;
    }

    //----- Serialization -----//

    public function __serialize(): array{
        return [
            $this->id,
            $this->content,
            $this->embeds,
            $this->author_id,
            $this->channel_id,
            $this->guild_id,
            $this->timestamp,
            $this->attachments,
            $this->everyone_mentioned,
            $this->users_mentioned,
            $this->roles_mentioned,
            $this->channels_mentioned,
            $this->webhook_id
        ];
    }

    public function __unserialize(array $data): void{
        [
            $this->id,
            $this->content,
            $this->embeds,
            $this->author_id,
            $this->channel_id,
            $this->guild_id,
            $this->timestamp,
            $this->attachments,
            $this->everyone_mentioned,
            $this->users_mentioned,
            $this->roles_mentioned,
            $this->channels_mentioned,
            $this->webhook_id
        ] = $data;
    }
}