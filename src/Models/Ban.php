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

namespace JaxkDev\DiscordBot\Models;

use JaxkDev\DiscordBot\Plugin\Utils;

class Ban{

    /** @var string */
    private $guild_id;

    /** @var string */
    private $user_id;

    /** @var string|null */
    private $reason;

    /** @var int|null Only present on banRequest. */
    private $days_to_delete;

    public function __construct(string $guild_id, string $user_id, ?string $reason = null, ?int $days_to_delete = null){
        $this->setGuildId($guild_id);
        $this->setUserId($user_id);
        $this->setReason($reason);
        $this->setDaysToDelete($days_to_delete);
    }

    public function getId(): string{
        return $this->guild_id.".".$this->user_id;
    }

    public function getGuildId(): string{
        return $this->guild_id;
    }

    public function setGuildId(string $guild_id): void{
        if(!Utils::validDiscordSnowflake($guild_id)){
            throw new \AssertionError("Guild ID '$guild_id' is invalid.");
        }
        $this->guild_id = $guild_id;
    }

    public function getUserId(): string{
        return $this->user_id;
    }

    public function setUserId(string $user_id): void{
        if(!Utils::validDiscordSnowflake($user_id)){
            throw new \AssertionError("User ID '$user_id' is invalid.");
        }
        $this->user_id = $user_id;
    }

    public function getReason(): ?string{
        return $this->reason;
    }

    public function setReason(?string $reason): void{
        $this->reason = $reason;
    }

    public function getDaysToDelete(): ?int{
        return $this->days_to_delete;
    }

    public function setDaysToDelete(?int $days_to_delete): void{
        if($days_to_delete !== null and ($days_to_delete < 0 or $days_to_delete > 7)){
            throw new \AssertionError("Days to delete '$days_to_delete' is invalid, 0-7 allowed.");
        }
        $this->days_to_delete = $days_to_delete;
    }

    //----- Serialization -----//

    public function __serialize(): array{
        return [
            $this->guild_id,
            $this->user_id,
            $this->reason,
            $this->days_to_delete
        ];
    }

    public function __unserialize(array $data): void{
        [
            $this->guild_id,
            $this->user_id,
            $this->reason,
            $this->days_to_delete
        ] = $data;
    }
}