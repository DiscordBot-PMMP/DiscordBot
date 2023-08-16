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

use JaxkDev\DiscordBot\Communication\BinaryStream;
use JaxkDev\DiscordBot\Communication\Packets\Packet;

class RequestFetchWebhooks extends Packet{

    public const SERIALIZE_ID = 65;

    private string $guild_id;

    private ?string $channel_id;

    public function __construct(string $guild_id, ?string $channel_id = null, ?int $uid = null){
        parent::__construct($uid);
        $this->guild_id = $guild_id;
        $this->channel_id = $channel_id;
    }

    public function getGuildId(): string{
        return $this->guild_id;
    }

    public function getChannelId(): ?string{
        return $this->channel_id;
    }

    public function binarySerialize(): BinaryStream{
        $stream = new BinaryStream();
        $stream->putString($this->guild_id);
        $stream->putNullableString($this->channel_id);
        return $stream;
    }

    public static function fromBinary(BinaryStream $stream): self{
        return new self(
            $stream->getString(),        // guild_id
            $stream->getNullableString() // channel_id
        );
    }
}