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

class MessageReactionAdd extends Packet{

    public const ID = 52;

    private string $message_id;

    private string $emoji;

    private string $member_id;

    private string $channel_id;

    public function __construct(string $message_id, string $emoji, string $member_id, string $channel_id, ?int $uid = null){
        parent::__construct($uid);
        $this->message_id = $message_id;
        $this->emoji = $emoji;
        $this->member_id = $member_id;
        $this->channel_id = $channel_id;
    }

    public function getMessageId(): string{
        return $this->message_id;
    }

    public function getEmoji(): string{
        return $this->emoji;
    }

    public function getMemberId(): string{
        return $this->member_id;
    }

    public function getChannelId(): string{
        return $this->channel_id;
    }

    public function jsonSerialize(): array{
        return [
            "uid" => $this->UID,
            "message_id" => $this->message_id,
            "emoji" => $this->emoji,
            "member_id" => $this->member_id,
            "channel_id" => $this->channel_id
        ];
    }

    public static function fromJson(array $data): self{
        return new self(
            $data["message_id"],
            $data["emoji"],
            $data["member_id"],
            $data["channel_id"],
            $data["uid"]
        );
    }
}