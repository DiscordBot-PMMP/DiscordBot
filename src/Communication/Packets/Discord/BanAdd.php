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

use JaxkDev\DiscordBot\Models\Ban;
use JaxkDev\DiscordBot\Communication\Packets\Packet;

class BanAdd extends Packet{

    public const ID = 35;

    private Ban $ban;

    public function __construct(Ban $ban, ?int $uid = null){
        parent::__construct($uid);
        $this->ban = $ban;
    }

    public function getBan(): Ban{
        return $this->ban;
    }

    public function jsonSerialize(): array{
        return [
            "uid" => $this->UID,
            "ban" => $this->ban->jsonSerialize()
        ];
    }

    public static function fromJson(array $data): self{
        return new self(
            Ban::fromJson($data["ban"]),
            $data["uid"]
        );
    }
}