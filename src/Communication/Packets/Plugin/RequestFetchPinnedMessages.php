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

class RequestFetchPinnedMessages extends Packet{

    public const ID = 15;

    private string $channel_id;

    public function __construct(string $channel_id, ?int $uid = null){
        parent::__construct($uid);
        $this->channel_id = $channel_id;
    }

    public function getChannelId(): string{
        return $this->channel_id;
    }

    public function jsonSerialize(): array{
        return [
            "uid" => $this->UID,
            "channel_id" => $this->channel_id
        ];
    }

    public static function fromJson(array $data): self{
        return new self(
            $data["channel_id"],
            $data["uid"]
        );
    }
}