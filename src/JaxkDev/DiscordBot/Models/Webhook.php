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

namespace JaxkDev\DiscordBot\Models;

use JaxkDev\DiscordBot\Plugin\Utils;

class Webhook implements \Serializable{

    const TYPE_NORMAL = 1, //Standard webhook
        TYPE_FOLLOWER = 2; //Receiving 'news' from another channel.

    /** @var string|null Only present on created webhooks. */
    private $id;

    /**
     * @see Webhook::TYPE_NORMAL
     * @see Webhook::TYPE_FOLLOWER
     * @var int
     */
    private $type;

    /** @var string|null User that added/created this webhook, only present on created webhooks. */
    private $user_id;

    /** @var string */
    private $channel_id;

    /** @var string */
    private $name;

    /** @var string|null */
    private $avatar;

    /** @var string|null Only present on TYPE_NORMAL. */
    private $token;

    public function __construct(int $type, string $channel_id, string $name, ?string $id = null, ?string $user_id = null,
                                ?string $avatar = null, ?string $token = null){
        $this->setId($id);
        $this->setType($type);
        $this->setUserId($user_id);
        $this->setChannelId($channel_id);
        $this->setName($name);
        $this->setAvatar($avatar);
        $this->setToken($token);
    }

    public function getId(): ?string{
        return $this->id;
    }

    public function setId(?string $id): void{
        if($id !== null and !Utils::validDiscordSnowflake($id)){
            throw new \AssertionError("ID '$id' is invalid.");
        }
        $this->id = $id;
    }

    public function getUserId(): ?string{
        return $this->user_id;
    }

    public function setUserId(?string $user_id): void{
        if($user_id !== null and !Utils::validDiscordSnowflake($user_id)){
            throw new \AssertionError("User ID '$user_id' is invalid.");
        }
        $this->user_id = $user_id;
    }

    public function getType(): int{
        return $this->type;
    }

    public function setType(int $type): void{
        if($type > self::TYPE_FOLLOWER or $type < self::TYPE_NORMAL){
            throw new \AssertionError("Type '$type' is invalid.");
        }
        $this->type = $type;
    }

    public function getChannelId(): string{
        return $this->channel_id;
    }

    public function setChannelId(string $channel_id): void{
        if(!Utils::validDiscordSnowflake($channel_id)){
            throw new \AssertionError("Channel ID '$channel_id' is invalid.");
        }
        $this->channel_id = $channel_id;
    }

    public function getName(): string{
        return $this->name;
    }

    public function setName(string $name): void{
        $this->name = $name;
    }

    public function getAvatar(): ?string{
        return $this->avatar;
    }

    public function setAvatar(?string $avatar): void{
        $this->avatar = $avatar;
    }

    public function getToken(): ?string{
        return $this->token;
    }

    public function setToken(?string $token): void{
        $this->token = $token;
    }

    //----- Serialization -----//

    public function serialize(): ?string{
        return serialize([
            $this->id,
            $this->type,
            $this->user_id,
            $this->channel_id,
            $this->name,
            $this->avatar,
            $this->token
        ]);
    }

    public function unserialize($data): void{
        [
            $this->id,
            $this->type,
            $this->user_id,
            $this->channel_id,
            $this->name,
            $this->avatar,
            $this->token
        ] = unserialize($data);
    }
}