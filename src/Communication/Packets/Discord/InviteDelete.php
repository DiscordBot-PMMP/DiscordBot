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

class InviteDelete extends Packet{

    public const ID = 47;

    private string $invite_code;

    public function __construct(string $invite_code, ?int $uid = null){
        parent::__construct($uid);
        $this->invite_code = $invite_code;
    }

    public function getInviteCode(): string{
        return $this->invite_code;
    }

    public function jsonSerialize(): array{
        return [
            "uid" => $this->UID,
            "invite_code" => $this->invite_code
        ];
    }

    public static function fromJson(array $data): self{
        return new self(
            $data["invite_code"],
            $data["uid"]
        );
    }
}