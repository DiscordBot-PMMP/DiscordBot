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

class RequestRemoveReaction extends Packet{

    public const ID = 23;

    private string $channel_id;

    private string $message_id;

    private string $user_id;

    private string $emoji;

    public function __construct(string $channel_id, string $message_id, string $user_id, string $emoji, ?int $uid = null){
        parent::__construct($uid);
        $this->channel_id = $channel_id;
        $this->message_id = $message_id;
        $this->user_id = $user_id;
        $this->emoji = $emoji;
    }

    public function getChannelId(): string{
        return $this->channel_id;
    }

    public function getMessageId(): string{
        return $this->message_id;
    }

    public function getUserId(): string{
        return $this->user_id;
    }

    public function getEmoji(): string{
        return $this->emoji;
    }

    public function jsonSerialize(): array{
        return [
            "uid" => $this->UID,
            "channel_id" => $this->channel_id,
            "message_id" => $this->message_id,
            "user_id" => $this->user_id,
            "emoji" => $this->emoji
        ];
    }

    public static function fromJson(array $data): self{
        return new self(
            $data["channel_id"],
            $data["message_id"],
            $data["user_id"],
            $data["emoji"],
            $data["uid"]
        );
    }
}