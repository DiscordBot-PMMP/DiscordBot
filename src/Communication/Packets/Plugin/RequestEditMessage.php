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

use JaxkDev\DiscordBot\Communication\Packets\Packet;
use JaxkDev\DiscordBot\Models\Messages\Message;

class RequestEditMessage extends Packet{

    public const ID = 13;

    private Message $message;

    public function __construct(Message $message, ?int $uid = null){
        parent::__construct($uid);
        $this->message = $message;
    }

    public function getMessage(): Message{
        return $this->message;
    }

    public function jsonSerialize(): array{
        return [
            "uid" => $this->UID,
            "message" => $this->message->jsonSerialize()
        ];
    }

    public static function fromJson(array $data): self{
        return new self(
            Message::fromJson($data["message"]),
            $data["uid"]
        );
    }
}