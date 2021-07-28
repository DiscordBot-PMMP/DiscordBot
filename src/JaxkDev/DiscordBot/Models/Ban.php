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

class Ban implements \Serializable{

    /** @var string */
    private $server_id;

    /** @var string */
    private $user_id;

    /** @var string|null */
    private $reason;

    /** @var int|null Only present on banRequest. */
    private $days_to_delete;

    public function __construct(string $server_id, string $user_id, ?string $reason = null, ?int $days_to_delete = null){
        $this->setServerId($server_id);
        $this->setUserId($user_id);
        $this->setReason($reason);
        $this->setDaysToDelete($days_to_delete);
    }

    public function getId(): string{
        return $this->server_id.".".$this->user_id;
    }

    public function getServerId(): string{
        return $this->server_id;
    }

    public function setServerId(string $server_id): void{
        if(!Utils::validDiscordSnowflake($server_id)){
            throw new \AssertionError("Server ID '$server_id' is invalid.");
        }
        $this->server_id = $server_id;
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

    public function serialize(): ?string{
        return serialize([
            $this->server_id,
            $this->user_id,
            $this->reason,
            $this->days_to_delete
        ]);
    }

    public function unserialize($data): void{
        [
            $this->server_id,
            $this->user_id,
            $this->reason,
            $this->days_to_delete
        ] = unserialize($data);
    }
}