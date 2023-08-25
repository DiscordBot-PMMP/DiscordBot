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
use JaxkDev\DiscordBot\Models\Messages\Message;

final class MessageUpdate extends Packet{

    public const SERIALIZE_ID = 26;

    private ?string $guild_id;

    private string $channel_id;

    private string $message_id;

    /** Null if no new message was provided from discord. */
    private ?Message $new_message;

    /** Null if old message was not cached. */
    private ?Message $old_message;

    public function __construct(?string $guild_id, string $channel_id, string $message_id, ?Message $new_message,
                                ?Message $old_message, ?int $uid = null){
        parent::__construct($uid);
        $this->guild_id = $guild_id;
        $this->channel_id = $channel_id;
        $this->message_id = $message_id;
        $this->new_message = $new_message;
        $this->old_message = $old_message;
    }

    public function getGuildId(): ?string{
        return $this->guild_id;
    }

    public function getChannelId(): string{
        return $this->channel_id;
    }

    public function getMessageId(): string{
        return $this->message_id;
    }

    public function getNewMessage(): ?Message{
        return $this->new_message;
    }

    public function getOldMessage(): ?Message{
        return $this->old_message;
    }

    public function binarySerialize(): BinaryStream{
        $stream = new BinaryStream();
        $stream->putInt($this->getUID());
        $stream->putNullableString($this->guild_id);
        $stream->putString($this->channel_id);
        $stream->putString($this->message_id);
        $stream->putNullableSerializable($this->new_message);
        $stream->putNullableSerializable($this->old_message);
        return $stream;
    }

    public static function fromBinary(BinaryStream $stream): self{
        $uid = $stream->getInt();
        return new self(
            $stream->getNullableString(),                     // guild_id
            $stream->getString(),                             // channel_id
            $stream->getString(),                             // message_id
            $stream->getNullableSerializable(Message::class), // new_message
            $stream->getNullableSerializable(Message::class), // old_message
            $uid
        );
    }
}