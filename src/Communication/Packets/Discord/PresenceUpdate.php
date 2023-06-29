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
use JaxkDev\DiscordBot\Models\Presence\Presence;

class PresenceUpdate extends Packet{

    public const ID = 58;

    private string $member_id;

    private Presence $presence;

    public function __construct(string $member_id, Presence $presence, ?int $uid = null){
        parent::__construct($uid);
        $this->member_id = $member_id;
        $this->presence = $presence;
    }

    public function getMemberId(): string{
        return $this->member_id;
    }

    public function getPresence(): Presence{
        return $this->presence;
    }

    public function jsonSerialize(): array{
        return [
            "uid" => $this->UID,
            "member_id" => $this->member_id,
            "presence" => $this->presence->jsonSerialize()
        ];
    }

    public static function fromJson(array $data): self{
        return new self(
            $data["member_id"],
            Presence::fromJson($data["presence"]),
            $data["uid"]
        );
    }
}