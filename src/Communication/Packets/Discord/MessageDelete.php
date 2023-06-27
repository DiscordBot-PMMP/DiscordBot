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

use JaxkDev\DiscordBot\Communication\Packets\Packet;
use JaxkDev\DiscordBot\Models\Messages\Message;

class MessageDelete extends Packet{

    /**
     * @var Message|array{"message_id": string, "channel_id": string, "guild_id": string}
     */
    private Message|array $message;

    /**
     * @param Message|array{"message_id": string, "channel_id": string, "guild_id": string} $message
     */
    public function __construct(Message|array $message){
        parent::__construct();
        $this->message = $message;
    }

    /**
     * @return Message|array{"message_id": string, "channel_id": string, "guild_id": string}
     */
    public function getMessage(): Message|array{
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