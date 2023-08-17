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

use JaxkDev\DiscordBot\Communication\BinaryStream;
use JaxkDev\DiscordBot\Communication\Packets\Packet;

class RequestSendFile extends Packet{

    public const SERIALIZE_ID = 76;

    private string $guild_id;

    private string $channel_id;

    private string $file_name;

    private string $file_path;

    private string $message;

    public function __construct(string $guild_id, string $channel_id, string $file_name, string $file_path,
                                string $message, ?int $uid = null){
        parent::__construct($uid);
        $this->guild_id = $guild_id;
        $this->channel_id = $channel_id;
        $this->file_name = $file_name;
        $this->file_path = $file_path;
        $this->message = $message;
    }

    public function getGuildId(): string{
        return $this->guild_id;
    }

    public function getChannelId(): string{
        return $this->channel_id;
    }

    public function getFileName(): string{
        return $this->file_name;
    }

    public function getFilePath(): string{
        return $this->file_path;
    }

    public function getMessage(): string{
        return $this->message;
    }

    public function binarySerialize(): BinaryStream{
        $stream = new BinaryStream();
        $stream->putString($this->guild_id);
        $stream->putString($this->channel_id);
        $stream->putString($this->file_name);
        $stream->putString($this->file_path);
        $stream->putString($this->message);
        return $stream;
    }

    public static function fromBinary(BinaryStream $stream): self{
        return new self(
            $stream->getString(), // guild_id
            $stream->getString(), // channel_id
            $stream->getString(), // file_name
            $stream->getString(), // file_path
            $stream->getString()  // message
        );
    }
}