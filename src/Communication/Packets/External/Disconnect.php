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

class Disconnect extends Packet{

    public const ID = 63;

    private string $message;

    public function __construct(?string $message = null, ?int $uid = null){
        parent::__construct($uid);
        $this->message = $message ?? "Unknown";
    }

    public function getMessage(): string{
        return $this->message;
    }

    public function binarySerialize(): BinaryStream{
        $stream = new BinaryStream();
        $stream->putInt($this->UID);
        $stream->putInt(strlen($this->message));
        $stream->put($this->message);
        return $stream;
    }

    public static function fromBinary(BinaryStream $stream): self{
        $uid = $stream->getInt();
        $length = $stream->getInt();
        $message = $stream->get($length);
        return new self(
            $message,
            $uid
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