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
use JaxkDev\DiscordBot\Models\Invite;

class RequestInitialiseInvite extends Packet{

    public const ID = 18;

    private Invite $invite;

    public function __construct(Invite $invite, ?int $uid = null){
        parent::__construct($uid);
        $this->invite = $invite;
    }

    public function getInvite(): Invite{
        return $this->invite;
    }

    public function jsonSerialize(): array{
        return [
            "uid" => $this->UID,
            "invite" => $this->invite->jsonSerialize()
        ];
    }

    public static function fromJson(array $data): self{
        return new self(
            Invite::fromJson($data["invite"]),
            $data["uid"]
        );
    }
}