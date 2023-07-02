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
use JaxkDev\DiscordBot\Communication\BinaryStream;

class Disconnect extends Packet{

    public const SERIALIZE_ID = 2;

    private string $message;

    public function __construct(?string $message = null){
        parent::__construct();
        $this->message = $message ?? "Unknown";
    }

    public function getMessage(): string{
        return $this->message;
    }

    public function binarySerialize(): BinaryStream{
        $stream = new BinaryStream();
        $stream->putString($this->message);
        return $stream;
    }

    public static function fromBinary(BinaryStream $stream): self{
        return new self(
            $stream->getString()
        );
    }

    public function jsonSerialize(): array{
        return [
            "message" => $this->message
        ];
    }

    public static function fromJson(array $data): self{
        return new self(
            $data["message"] ?? "Unknown",
        );
    }
}