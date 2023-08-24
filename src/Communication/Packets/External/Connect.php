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

namespace JaxkDev\DiscordBot\Communication\Packets\External;

use JaxkDev\DiscordBot\Communication\BinaryStream;
use JaxkDev\DiscordBot\Communication\Packets\Packet;

/**
 * Packet for external bot to verify its connection with the correct Version and MAGIC.
 */
final class Connect extends Packet{

    public const SERIALIZE_ID = 3;

    private int $version;
    private int $magic;

    public function __construct(int $version, int $magic, ?int $uid = null){
        parent::__construct($uid);
        $this->version = $version;
        $this->magic = $magic;
    }

    public function getVersion(): int{
        return $this->version;
    }

    public function getMagic(): int{
        return $this->magic;
    }

    public function binarySerialize(): BinaryStream{
        $stream = new BinaryStream();
        $stream->putInt($this->getUID());
        $stream->putByte($this->version);
        $stream->putInt($this->magic);
        return $stream;
    }

    public static function fromBinary(BinaryStream $stream): self{
        $uid = $stream->getInt();
        return new self(
            $stream->getByte(), // version
            $stream->getInt(),  // magic
            $uid
        );
    }
}