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

namespace JaxkDev\DiscordBot\Communication\Packets\Discord;

use JaxkDev\DiscordBot\Models\Messages\Message;
use JaxkDev\DiscordBot\Communication\Packets\Packet;

class MessageSent extends Packet{

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
        }catch(\Throwable $e){
            throw new \AssertionError("Failed to unserialize '".get_parent_class($this)."'", 0, $e);
        }
    }
}