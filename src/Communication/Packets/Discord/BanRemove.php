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

namespace JaxkDev\DiscordBot\Communication\Packets\Discord;

use JaxkDev\DiscordBot\Communication\Packets\Packet;

class BanRemove extends Packet{

    public const ID = 36;

    private string $ban_id;

    public function __construct(string $ban_id, ?int $uid = null){
        parent::__construct($uid);
        $this->ban_id = $ban_id;
    }

    public function getBanId(): string{
        return $this->ban_id;
    }

    public function jsonSerialize(): array{
        return [
            "uid" => $this->UID,
            "ban_id" => $this->ban_id
        ];
    }

    public static function fromJson(array $data): self{
        return new self(
            $data["ban_id"],
            $data["uid"]
        );
    }
}