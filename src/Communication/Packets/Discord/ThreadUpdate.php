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

final class ThreadUpdate extends Packet{

    public const SERIALIZE_ID = 38;

    private Channel $thread;

    private ?Channel $old_thread;

    public function __construct(Channel $thread, ?Channel $old_thread, ?int $uid = null){
        parent::__construct($uid);
        $this->thread = $thread;
        $this->old_thread = $old_thread;
    }

    public function getThread(): Channel{
        return $this->thread;
    }

    public function getOldThread(): ?Channel{
        return $this->old_thread;
    }

    public function binarySerialize(): BinaryStream{
        $stream = new BinaryStream();
        $stream->putInt($this->getUID());
        $stream->putSerializable($this->thread);
        $stream->putNullableSerializable($this->old_thread);
        return $stream;
    }

    public static function fromBinary(BinaryStream $stream): self{
        $uid = $stream->getInt();
        return new self(
            $stream->getSerializable(Channel::class),         // thread
            $stream->getNullableSerializable(Channel::class), // old_thread
            $uid
        );
    }
}