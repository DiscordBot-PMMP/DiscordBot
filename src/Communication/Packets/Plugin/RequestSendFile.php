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

class RequestSendFile extends Packet{

    public const ID = 27;

    private string $channel_id;

    private string $file_name;

    private string $file_path;

    private string $message;

    public function __construct(string $channel_id, string $file_name, string $file_path, string $message, ?int $uid = null){
        parent::__construct($uid);
        $this->channel_id = $channel_id;
        $this->file_name = $file_name;
        $this->file_path = $file_path;
        $this->message = $message;
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

    public function jsonSerialize(): array{
        return [
            "uid" => $this->UID,
            "channel_id" => $this->channel_id,
            "file_name" => $this->file_name,
            "file_path" => $this->file_path,
            "message" => $this->message
        ];
    }

    public static function fromJson(array $data): self{
        return new self(
            $data["channel_id"],
            $data["file_name"],
            $data["file_path"],
            $data["message"],
            $data["uid"]
        );
    }
}