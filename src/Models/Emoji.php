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

namespace JaxkDev\DiscordBot\Models;

use JaxkDev\DiscordBot\Plugin\Utils;

/** @link https://discord.com/developers/docs/resources/emoji#emoji-object */
class Emoji{

    /** Emoji ID */
    private ?string $id;

    /**
     * Emoji name.
     *
     * Can only be null for reaction events:
     * "In MESSAGE_REACTION_ADD and MESSAGE_REACTION_REMOVE events name may be null when custom emoji data is
     * not available (for example, if it was deleted from the guild)."
     */
    private ?string $name;

    /**
     * Roles allowed to use this emoji, array of IDs
     * @var string[]
     */
    private ?array $role_ids;

    /** User that created this emoji */
    private ?string $user_id;

    /** Whether this emoji must be wrapped in colons */
    private ?bool $require_colons;

    /** Whether this emoji is managed */
    private ?bool $managed;

    /** Whether this emoji is animated */
    private ?bool $animated;

    /** Whether this emoji can be used, may be false due to loss of Server Boosts */
    private ?bool $available;

    //No support for create/update emoji.

    /** @param ?string[] $role_ids Role IDs */
    public function __construct(?string $id, ?string $name, ?array $role_ids, ?string $user_id, ?bool $require_colons,
                                ?bool $managed, ?bool $animated, ?bool $available){
        $this->setId($id);
        $this->setName($name);
        $this->setRoleIds($role_ids);
        $this->setUserId($user_id);
        $this->setRequireColons($require_colons);
        $this->setManaged($managed);
        $this->setAnimated($animated);
        $this->setAvailable($available);
    }

    public function getId(): ?string{
        return $this->id;
    }

    public function setId(?string $id): void{
        if($id !== null && !Utils::validDiscordSnowflake($id)){
            throw new \AssertionError("Emoji ID '$id' is invalid.");
        }
        $this->id = $id;
    }

    public function getName(): ?string{
        return $this->name;
    }

    public function setName(?string $name): void{
        $this->name = $name;
    }

    /** @return ?string[] */
    public function getRoleIds(): ?array{
        return $this->role_ids;
    }

    /** @param ?string[] $role_ids */
    public function setRoleIds(?array $role_ids): void{
        if($role_ids !== null){
            foreach($role_ids as $role_id){
                if(!Utils::validDiscordSnowflake($role_id)){
                    throw new \AssertionError("Emoji role ID '$role_id' is invalid.");
                }
            }
        }
        $this->role_ids = $role_ids;
    }

    public function getUserId(): ?string{
        return $this->user_id;
    }

    public function setUserId(?string $user_id): void{
        if($user_id !== null && !Utils::validDiscordSnowflake($user_id)){
            throw new \AssertionError("Emoji user ID '$user_id' is invalid.");
        }
        $this->user_id = $user_id;
    }

    public function getRequireColons(): ?bool{
        return $this->require_colons;
    }

    public function setRequireColons(?bool $require_colons): void{
        $this->require_colons = $require_colons;
    }

    public function getManaged(): ?bool{
        return $this->managed;
    }

    public function setManaged(?bool $managed): void{
        $this->managed = $managed;
    }

    public function getAnimated(): ?bool{
        return $this->animated;
    }

    public function setAnimated(?bool $animated): void{
        $this->animated = $animated;
    }

    public function getAvailable(): ?bool{
        return $this->available;
    }

    public function setAvailable(?bool $available): void{
        $this->available = $available;
    }

    //----- Serialization -----//

    public function __serialize(): array{
        return [
            $this->id,
            $this->name,
            $this->role_ids,
            $this->user_id,
            $this->require_colons,
            $this->managed,
            $this->animated,
            $this->available
        ];
    }

    public function __unserialize(array $data): void{
        [
            $this->id,
            $this->name,
            $this->role_ids,
            $this->user_id,
            $this->require_colons,
            $this->managed,
            $this->animated,
            $this->available
        ] = $data;
    }
}