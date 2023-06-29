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

class RequestDeleteChannel extends Packet{

    public const ID = 9;

    private string $guild_id;

    private string $channel_id;

    public function __construct(string $guild_id, string $channel_id, ?int $uid = null){
        parent::__construct($uid);
        $this->guild_id = $guild_id;
        $this->channel_id = $channel_id;
    }

    public function getGuildId(): string{
        return $this->guild_id;
    }

    public function getChannelId(): string{
        return $this->channel_id;
    }

    public function jsonSerialize(): array{
        return [
            "uid" => $this->UID,
            "guild_id" => $this->guild_id,
            "channel_id" => $this->channel_id
        ];
    }

    public static function fromJson(array $data): self{
        return new self(
            $data["guild_id"],
            $data["channel_id"],
            $data["uid"]
        );
    }
}