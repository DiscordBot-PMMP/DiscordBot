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

use JaxkDev\DiscordBot\Models\Messages\Message;
use JaxkDev\DiscordBot\Communication\Packets\Packet;

class MessageSent extends Packet{

    private Message $message;

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

    public function __unserialize(array $data): void{
        [
            $this->UID,
            $this->message
        ] = $data;
    }
}