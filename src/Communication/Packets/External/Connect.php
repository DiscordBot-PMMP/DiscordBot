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

namespace JaxkDev\DiscordBot\Communication\Packets\External;

use JaxkDev\DiscordBot\Communication\Packets\Packet;
use pocketmine\utils\BinaryStream;

/**
 * Packet for external bot to verify its connection with the correct Version and MAGIC.
 */
class Connect extends Packet{

    public const ID = 0;

    private int $version;
    private int $magic;

    public function __construct(int $version, int $magic){
        parent::__construct();
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
        $stream->putByte($this->version);
        $stream->putInt($this->magic);
        return $stream;
    }

    public static function fromBinary(BinaryStream $stream): Packet{
        $version = $stream->getByte();
        $magic = $stream->getInt();
        return new self(
            $version,
            $magic
        );
    }

    public function jsonSerialize(): array{
        return [
            "version" => $this->version,
            "magic" => $this->magic
        ];
    }

    public static function fromJson(array $data): Packet{
        return new self(
            $data["version"],
            $data["magic"]
        );
    }
}