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
use JaxkDev\DiscordBot\Models\Messages\Message;

class MessageDelete extends Packet{

    public const SERIALIZE_ID = 20;

    /**
     * @var Message|array{"message_id": string, "channel_id": string, "guild_id": string}
     */
    private Message|array $message;

    /**
     * @param Message|array{"message_id": string, "channel_id": string, "guild_id": string} $message
     */
    public function __construct(Message|array $message, ?int $uid = null){
        parent::__construct($uid);
        $this->message = $message;
    }

    /**
     * @return Message|array{"message_id": string, "channel_id": string, "guild_id": string}
     */
    public function getMessage(): Message|array{
        return $this->message;
    }

    public function binarySerialize(): BinaryStream{
        $stream = new BinaryStream();
        $stream->putBool($this->message instanceof Message);
        if($this->message instanceof Message){
            $stream->putSerializable($this->message);
        }else{
            $stream->putString($this->message["message_id"]);
            $stream->putString($this->message["guild_id"]);
            $stream->putString($this->message["channel_id"]);
        }
        return $stream;
    }

    public static function fromBinary(BinaryStream $stream): self{
        if($stream->getBool()){
            return new self(
                $stream->getSerializable(Message::class)
            );
        }else{
            return new self(
                [
                    "message_id" => $stream->getString(),
                    "guild_id" => $stream->getString(),
                    "channel_id" => $stream->getString()
                ]
            );
        }
    }
}