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

class RequestCreateInvite extends Packet{

    public const SERIALIZE_ID = 67;

    private string $guild_id;

    private string $channel_id;

    private int $max_age;

    private int $max_uses;

    private bool $temporary;

    private bool $unique;

    private ?string $reason;

    public function __construct(string $guild_id, string $channel_id, int $max_age, int $max_uses, bool $temporary,
                                bool $unique, ?string $reason = null, ?int $uid = null){
        parent::__construct($uid);
        $this->guild_id = $guild_id;
        $this->channel_id = $channel_id;
        $this->max_age = $max_age;
        $this->max_uses = $max_uses;
        $this->temporary = $temporary;
        $this->unique = $unique;
        $this->reason = $reason;
    }

    public function getGuildId(): string{
        return $this->guild_id;
    }

    public function getChannelId(): string{
        return $this->channel_id;
    }

    public function getMaxAge(): int{
        return $this->max_age;
    }

    public function getMaxUses(): int{
        return $this->max_uses;
    }

    public function getTemporary(): bool{
        return $this->temporary;
    }

    public function getUnique(): bool{
        return $this->unique;
    }

    public function getReason(): ?string{
        return $this->reason;
    }

    public function binarySerialize(): BinaryStream{
        $stream = new BinaryStream();
        $stream->putString($this->guild_id);
        $stream->putString($this->channel_id);
        $stream->putInt($this->max_age);
        $stream->putShort($this->max_uses);
        $stream->putBool($this->temporary);
        $stream->putBool($this->unique);
        $stream->putNullableString($this->reason);
        return $stream;
    }

    public static function fromBinary(BinaryStream $stream): self{
        return new self(
            $stream->getString(),        // guild_id
            $stream->getString(),        // channel_id
            $stream->getInt(),           // max_age
            $stream->getShort(),         // max_uses
            $stream->getBool(),          // temporary
            $stream->getBool(),          // unique
            $stream->getNullableString() // reason
        );
    }
}