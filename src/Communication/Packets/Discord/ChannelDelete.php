<?php

/*
 * DiscordBot, PocketMine-MP Plugin.
 *
 * Licensed under the Open Software License version 3.0 (OSL-3.0)
 * Copyright (C) 2020-present JaxkDev
 *
 * Discord :: JaxkDev
 * Email   :: JaxkDev@gmail.com
 */

namespace JaxkDev\DiscordBot\Communication\Packets\Discord;

use JaxkDev\DiscordBot\Communication\BinaryStream;
use JaxkDev\DiscordBot\Communication\Packets\Packet;
use JaxkDev\DiscordBot\Models\Channels\Channel;

final class ChannelDelete extends Packet{

    public const SERIALIZE_ID = 9;

    private ?string $guild_id;

    private string $channel_id;

    private ?Channel $cached_channel;

    public function __construct(?string $guild_id, string $channel_id, ?Channel $cached_channel, ?int $uid = null){
        parent::__construct($uid);
        $this->guild_id = $guild_id;
        $this->channel_id = $channel_id;
        $this->cached_channel = $cached_channel;
    }

    public function getGuildId(): ?string{
        return $this->guild_id;
    }

    public function getChannelId(): string{
        return $this->channel_id;
    }

    public function getCachedChannel(): ?Channel{
        return $this->cached_channel;
    }

    public function binarySerialize(): BinaryStream{
        $stream = new BinaryStream();
        $stream->putInt($this->getUID());
        $stream->putNullableString($this->guild_id);
        $stream->putString($this->channel_id);
        $stream->putNullableSerializable($this->cached_channel);
        return $stream;
    }

    public static function fromBinary(BinaryStream $stream): self{
        $uid = $stream->getInt();
        return new self(
            $stream->getNullableString(),                     // guild_id
            $stream->getString(),                             // channel_id
            $stream->getNullableSerializable(Channel::class), // cached_channel
            $uid
        );
    }
}