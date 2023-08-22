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

use JaxkDev\DiscordBot\Communication\BinaryStream;
use JaxkDev\DiscordBot\Communication\Packets\Packet;

final class InviteDelete extends Packet{

    public const SERIALIZE_ID = 16;

    private ?string $guild_id;

    private ?string $channel_id;

    private string $invite_code;

    public function __construct(?string $guild_id, ?string $channel_id, string $invite_code, ?int $uid = null){
        parent::__construct($uid);
        $this->guild_id = $guild_id;
        $this->channel_id = $channel_id;
        $this->invite_code = $invite_code;
    }

    public function getGuildId(): ?string{
        return $this->guild_id;
    }

    public function getChannelId(): ?string{
        return $this->channel_id;
    }

    public function getInviteCode(): string{
        return $this->invite_code;
    }

    public function binarySerialize(): BinaryStream{
        $stream = new BinaryStream();
        $stream->putInt($this->getUID());
        $stream->putNullableString($this->guild_id);
        $stream->putNullableString($this->channel_id);
        $stream->putString($this->invite_code);
        return $stream;
    }

    public static function fromBinary(BinaryStream $stream): self{
        $uid = $stream->getInt();
        return new self(
            $stream->getNullableString(), // guild_id
            $stream->getNullableString(), // channel_id
            $stream->getString(),         // invite_code
            $uid
        );
    }
}