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

namespace JaxkDev\DiscordBot\Communication\Packets\Plugin;

use JaxkDev\DiscordBot\Communication\Packets\Packet;

class RequestUpdateNickname extends Packet{

    public const ID = 31;

    private string $guild_id;

    private string $user_id;

    private ?string $nickname;

    public function __construct(string $guild_id, string $user_id, ?string $nickname = null, ?int $uid = null){
        parent::__construct($uid);
        $this->guild_id = $guild_id;
        $this->user_id = $user_id;
        $this->nickname = $nickname;
    }

    public function getGuildId(): string{
        return $this->guild_id;
    }

    public function getUserId(): string{
        return $this->user_id;
    }

    public function getNickname(): ?string{
        return $this->nickname;
    }

    public function jsonSerialize(): array{
        return [
            "uid" => $this->UID,
            "guild_id" => $this->guild_id,
            "user_id" => $this->user_id,
            "nickname" => $this->nickname
        ];
    }

    public static function fromJson(array $data): self{
        return new self(
            $data["guild_id"],
            $data["user_id"],
            $data["nickname"],
            $data["uid"]
        );
    }
}