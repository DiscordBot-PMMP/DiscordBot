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

final class RequestCreateWebhook extends Packet{

    public const SERIALIZE_ID = 45;

    private string $guild_id;

    private string $channel_id;

    private string $name;

    private ?string $avatar_hash;

    private ?string $reason;

    public function __construct(string $guild_id, string $channel_id, string $name, ?string $avatar_hash = null,
                                ?string $reason = null, ?int $uid = null){
        parent::__construct($uid);
        $this->guild_id = $guild_id;
        $this->channel_id = $channel_id;
        $this->name = $name;
        $this->avatar_hash = $avatar_hash;
        $this->reason = $reason;
    }

    public function getGuildId(): string{
        return $this->guild_id;
    }

    public function getChannelId(): string{
        return $this->channel_id;
    }

    public function getName(): string{
        return $this->name;
    }

    public function getAvatarHash(): ?string{
        return $this->avatar_hash;
    }

    public function getReason(): ?string{
        return $this->reason;
    }

    public function binarySerialize(): BinaryStream{
        $stream = new BinaryStream();
        $stream->putInt($this->getUID());
        $stream->putString($this->guild_id);
        $stream->putString($this->channel_id);
        $stream->putString($this->name);
        $stream->putNullableString($this->avatar_hash);
        $stream->putNullableString($this->reason);
        return $stream;
    }

    public static function fromBinary(BinaryStream $stream): self{
        $uid = $stream->getInt();
        return new self(
            $stream->getString(),         // guild_id
            $stream->getString(),         // channel_id
            $stream->getString(),         // name
            $stream->getNullableString(), // avatar_hash
            $stream->getNullableString(), // reason
            $uid
        );
    }
}