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

/** @link https://discord.com/developers/docs/resources/guild#ban-object */
class Ban{

    /** Guild the user is banned from */
    private string $guild_id;

    /** The banned user */
    private string $user_id;

    /** The reason for the ban */
    private ?string $reason;

    /**
     * The only parameters required to create a new ban model ready to send.
     *
     * @see Api::initialiseBan()
     */
    public static function create(string $guild_id, string $user_id, ?string $reason = null): self{
        return new self($guild_id, $user_id, $reason);
    }

    public function __construct(string $guild_id, string $user_id, ?string $reason = null){
        $this->setGuildId($guild_id);
        $this->setUserId($user_id);
        $this->setReason($reason);
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

    //----- Serialization -----//

    public function __serialize(): array{
        return [
            $this->guild_id,
            $this->user_id,
            $this->reason
        ];
    }

    public function __unserialize(array $data): void{
        [
            $this->guild_id,
            $this->user_id,
            $this->reason
        ] = $data;
    }
}