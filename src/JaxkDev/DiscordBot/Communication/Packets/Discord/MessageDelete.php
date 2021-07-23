<?php
/*
 * DiscordBot, PocketMine-MP Plugin.
 *
 * Licensed under the Open Software License version 3.0 (OSL-3.0)
 * Copyright (C) 2020-2021 JaxkDev
 *
 * Twitter :: @JaxkDev
 * Discord :: JaxkDev#2698
 * Email   :: JaxkDev@gmail.com
 */

namespace JaxkDev\DiscordBot\Communication\Packets\Discord;

use JaxkDev\DiscordBot\Communication\Packets\Packet;
use JaxkDev\DiscordBot\Models\Messages\Message;

class MessageDelete extends Packet{

    /**
     * @var Message|array{"message_id": string, "channel_id": string, "server_id": string}
     */
    private $message;

    /**
     * @param Message|array{"message_id": string, "channel_id": string, "server_id": string} $message
     */
    public function __construct($message){
        parent::__construct();
        $this->message = $message;
    }

    /**
     * @return Message|array{"message_id": string, "channel_id": string, "server_id": string}
     */
    public function getMessage(){
        return $this->message;
    }

    public function serialize(): ?string{
        return serialize([
            $this->UID,
            $this->message
        ]);
    }

    public function unserialize($data): void{
        [
            $this->UID,
            $this->message
        ] = unserialize($data);
    }
}