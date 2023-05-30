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

use JaxkDev\DiscordBot\Models\Messages\Message;
use JaxkDev\DiscordBot\Communication\Packets\Packet;

class RequestSendMessage extends Packet{

    /** @var Message */
    private $message;

    public function __construct(Message $message){
        parent::__construct();
        $this->message = $message;
    }

    public function getMessage(): Message{
        return $this->message;
    }

    public function __serialize(): array{
        return [
            $this->UID,
            $this->message
        ];
    }

    public function __unserialize($data): void{
        try{
            [
                $this->UID,
                $this->message
            ] = $data;
        }catch (\Throwable $e){
            throw new \InvalidArgumentException("Failed to unserialize packet: " . $e->getMessage());
        }
    }
}