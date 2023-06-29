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
use JaxkDev\DiscordBot\Models\Guild\Guild;

class GuildUpdate extends Packet{

    public const ID = 45;

    private Guild $guild;

    public function __construct(Guild $guild, ?int $uid = null){
        parent::__construct($uid);
        $this->guild = $guild;
    }

    public function getGuild(): Guild{
        return $this->guild;
    }

    public function jsonSerialize(): array{
        return [
            "uid" => $this->UID,
            "guild" => $this->guild->jsonSerialize()
        ];
    }

    public static function fromJson(array $data): self{
        return new self(
            Guild::fromJson($data["guild"]),
            $data["uid"]
        );
    }
}