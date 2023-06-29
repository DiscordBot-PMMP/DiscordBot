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
use JaxkDev\DiscordBot\Models\Presence\Presence;

class RequestUpdatePresence extends Packet{

    public const ID = 32;

    private Presence $presence;

    public function __construct(Presence $presence, ?int $uid = null){
        parent::__construct($uid);
        $this->presence = $presence;
    }

    public function getPresence(): Presence{
        return $this->presence;
    }

    public function jsonSerialize(): array{
        return [
            "uid" => $this->UID,
            "presence" => $this->presence->jsonSerialize()
        ];
    }

    public static function fromJson(array $data): self{
        return new self(
            Presence::fromJson($data["presence"]),
            $data["uid"]
        );
    }
}