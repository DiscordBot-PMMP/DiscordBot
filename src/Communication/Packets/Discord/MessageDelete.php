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

    public const ID = 51;

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

    public function jsonSerialize(): array{
        return [
            "uid" => $this->UID,
            "message" => [] //$this->message->jsonSerialize() TODO
        ];
    }

    public static function fromJson(array $data): self{
        return new self(
            [], //Message::fromJson($data["message"]), TODO
            $data["uid"]
        );
    }
}