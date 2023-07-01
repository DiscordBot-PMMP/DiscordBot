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

class Ping extends Packet{

    public const ID = 64;

    private int $ping;

    public function __construct(int $ping){
        parent::__construct();
        $this->ping = $ping;
    }

    public function getPing(): int{
        return $this->ping;
    }

    public function binarySerialize(): BinaryStream{
        $stream = new BinaryStream();
        $stream->putInt($this->ping);
        return $stream;
    }

    public static function fromBinary(BinaryStream $stream): Packet{

    }

    public function jsonSerialize(): array{
        return [
            "uid" => $this->UID,
            "ping" => $this->ping
        ];
    }

    public static function fromJson(array $data): self{
        return new self(
            $data["ping"],
            $data["uid"]
        );
    }
}