<?php
/*
 * DiscordBot, PocketMine-MP Plugin.
 *
 * Licensed under the Open Software License version 3.0 (OSL-3.0)
 * Copyright (C) 2020-present JaxkDev
 *
 * Twitter :: @JaxkDev
 * Discord :: JaxkDev#2698
 * Email   :: JaxkDev@gmail.com
 */

namespace JaxkDev\DiscordBot\Communication\Packets\Plugin;

use JaxkDev\DiscordBot\Communication\Packets\Packet;

class RequestSendFile extends Packet{

    /** @var string */
    private $channel_id;

    /** @var string */
    private $file_name;

    /** @var string */
    private $file_path;

    /** @var string */
    private $message;

    public function __construct(string $channel_id, string $file_name, string $file_path, string $message){
        parent::__construct();
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

    public function __serialize(): array{
        return [
            $this->UID,
            $this->channel_id,
            $this->file_name,
            $this->file_path,
            $this->message
        ];
    }

    public function __unserialize($data): void{
        try{
            [
                $this->UID,
                $this->channel_id,
                $this->file_name,
                $this->file_path,
                $this->message
            ] = $data;
        }catch(\Throwable $e){
            throw new \AssertionError("Failed to unserialize '".get_parent_class($this)."'", 0, $e);
        }
    }
}