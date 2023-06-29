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

class MessageReactionRemoveEmoji extends Packet{

    public const ID = 55;

    private string $message_id;

    private string $channel_id;

    private string $emoji;

    public function __construct(string $message_id, string $channel_id, string $emoji, ?int $uid = null){
        parent::__construct($uid);
        $this->message_id = $message_id;
        $this->channel_id = $channel_id;
        $this->emoji = $emoji;
    }

    public function getMessageId(): string{
        return $this->message_id;
    }

    public function getChannelId(): string{
        return $this->channel_id;
    }

    public function getEmoji(): string{
        return $this->emoji;
    }

    public function jsonSerialize(): array{
        return [
            "uid" => $this->UID,
            "message_id" => $this->message_id,
            "channel_id" => $this->channel_id,
            "emoji" => $this->emoji
        ];
    }

    public static function fromJson(array $data): self{
        return new self(
            $data["message_id"],
            $data["channel_id"],
            $data["emoji"],
            $data["uid"]
        );
    }
}