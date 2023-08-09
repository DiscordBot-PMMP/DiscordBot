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

    private string $guild_id;

    private string $user_id;

    private string $channel_id;

    public function __construct(string $message_id, string $emoji, string $guild_id, string $user_id, string $channel_id, ?int $uid = null){
        parent::__construct($uid);
        $this->message_id = $message_id;
        $this->emoji = $emoji;
        $this->guild_id = $guild_id;
        $this->user_id = $user_id;
        $this->channel_id = $channel_id;
    }

    public function getMessageId(): string{
        return $this->message_id;
    }

    public function getEmoji(): string{
        return $this->emoji;
    }

    public function getGuildId(): string{
        return $this->guild_id;
    }

    public function getUserId(): string{
        return $this->user_id;
    }

    public function getChannelId(): string{
        return $this->channel_id;
    }
}