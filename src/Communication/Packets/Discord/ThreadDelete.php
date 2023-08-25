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
use JaxkDev\DiscordBot\Models\Channels\ChannelType;

final class ThreadDelete extends Packet{

    public const SERIALIZE_ID = 37;

    private ChannelType $type;

    private string $id;

    private string $guild_id;

    private string $parent_id;

    private ?Channel $cached_thread = null;

    public function __construct(ChannelType $type, string $id, string $guild_id, string $parent_id,
                                ?Channel $cached_thread, ?int $uid = null){
        parent::__construct($uid);
        $this->type = $type;
        $this->id = $id;
        $this->guild_id = $guild_id;
        $this->parent_id = $parent_id;
        $this->cached_thread = $cached_thread;
    }

    public function getType(): ChannelType{
        return $this->type;
    }

    public function getId(): string{
        return $this->id;
    }

    public function getGuildId(): string{
        return $this->guild_id;
    }

    public function getParentId(): string{
        return $this->parent_id;
    }

    public function getCachedThread(): ?Channel{
        return $this->cached_thread;
    }

    public function binarySerialize(): BinaryStream{
        $stream = new BinaryStream();
        $stream->putInt($this->getUID());
        $stream->putByte($this->type->value);
        $stream->putString($this->id);
        $stream->putString($this->guild_id);
        $stream->putString($this->parent_id);
        $stream->putNullableSerializable($this->cached_thread);
        return $stream;
    }

    public static function fromBinary(BinaryStream $stream): self{
        $uid = $stream->getInt();
        return new self(
            ChannelType::from($stream->getByte()),            // type
            $stream->getString(),                             // id
            $stream->getString(),                             // guild_id
            $stream->getString(),                             // parent_id
            $stream->getNullableSerializable(Channel::class), // cached_thread
            $uid
        );
    }
}