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
use JaxkDev\DiscordBot\Models\Messages\Message;
use JaxkDev\DiscordBot\Communication\Packets\Packet;

class MessageSent extends Packet{

    public const SERIALIZE_ID = 25;

    private Message $message;

    public function __construct(Message $message, ?int $uid = null){
        parent::__construct($uid);
        $this->message = $message;
    }

    public function getMessage(): Message{
        return $this->message;
    }

    public function binarySerialize(): BinaryStream{
        $stream = new BinaryStream();
        $stream->putSerializable($this->message);
        return $stream;
    }

    public static function fromBinary(BinaryStream $stream): self{
        return new self(
            $stream->getSerializable(Message::class)
        );
    }
}