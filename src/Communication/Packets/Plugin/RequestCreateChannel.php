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
use JaxkDev\DiscordBot\Models\Channels\GuildChannel;

class RequestCreateChannel extends Packet{

    public const ID = 6;

    private GuildChannel $channel;

    public function __construct(GuildChannel $channel, ?int $uid = null){
        parent::__construct($uid);
        $this->channel = $channel;
    }

    public function getChannel(): GuildChannel{
        return $this->channel;
    }

    public function jsonSerialize(): array{
        return [
            "uid" => $this->UID,
            "channel" => $this->channel->jsonSerialize()
        ];
    }

    public static function fromJson(array $data): self{
        return new self(
            GuildChannel::fromJson($data["channel"]),
            $data["uid"]
        );
    }
}