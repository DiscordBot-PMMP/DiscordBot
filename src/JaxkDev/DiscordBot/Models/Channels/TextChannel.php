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

namespace JaxkDev\DiscordBot\Models\Channels;

class TextChannel extends ServerChannel{

    /** @var string AKA Description. */
    private $topic;

    /** @var bool */
    private $nsfw = false;

    /** @var ?int In seconds | null when disabled. */
    private $rate_limit = null;

    //Pins can be found via API::fetchPinnedMessages();

    //Webhooks can be found via API::fetchWebhooks();

    /**
     * TextChannel constructor.
     *
     * @param string      $topic
     * @param string      $name
     * @param int         $position
     * @param string      $server_id
     * @param bool        $nsfw
     * @param int|null    $rate_limit
     * @param string|null $category_id
     * @param string|null $id
     */
    public function __construct(string $topic, string $name, int $position, string $server_id, bool $nsfw = false,
                                   ?int $rate_limit = null, ?string $category_id = null, ?string $id = null){
        parent::__construct($name, $position, $server_id, $category_id, $id);
        $this->setTopic($topic);
        $this->setNsfw($nsfw);
        $this->setRateLimit($rate_limit);
    }

    public function getTopic(): string{
        return $this->topic;
    }

    public function setTopic(string $topic): void{
        $this->topic = $topic;
    }

    public function isNsfw(): bool{
        return $this->nsfw;
    }

    public function setNsfw(bool $nsfw): void{
        $this->nsfw = $nsfw;
    }

    public function getRateLimit(): ?int{
        return $this->rate_limit;
    }

    /**
     * @param int|null $rate_limit 0-21600 seconds.
     */
    public function setRateLimit(?int $rate_limit): void{
        if($rate_limit !== null and ($rate_limit < 0 or $rate_limit > 21600)){
            throw new \AssertionError("Rate limit '$rate_limit' is outside the bounds 0-21600.");
        }
        $this->rate_limit = $rate_limit;
    }

    //----- Serialization -----//

    public function serialize(): ?string{
        return serialize([
            $this->id,
            $this->name,
            $this->position,
            $this->member_permissions,
            $this->role_permissions,
            $this->server_id,
            $this->topic,
            $this->nsfw,
            $this->rate_limit,
            $this->category_id
        ]);
    }

    public function unserialize($data): void{
        [
            $this->id,
            $this->name,
            $this->position,
            $this->member_permissions,
            $this->role_permissions,
            $this->server_id,
            $this->topic,
            $this->nsfw,
            $this->rate_limit,
            $this->category_id
        ] = unserialize($data);
    }
}