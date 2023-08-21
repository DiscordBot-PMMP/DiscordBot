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

use JaxkDev\DiscordBot\Communication\BinaryStream;
use JaxkDev\DiscordBot\Communication\Packets\Packet;
use JaxkDev\DiscordBot\Models\User;

/**
 * Sent when the bot connected has its user properties changed.
 * eg, owner changes its username via discord developer dashboard while bot is running.
 */
class BotUserUpdate extends Packet{

    public const SERIALIZE_ID = 34;

    private User $bot;

    public function __construct(User $bot, ?int $uid = null){
        parent::__construct($uid);
        $this->bot = $bot;
    }

    public function getBot(): User{
        return $this->bot;
    }

    public function binarySerialize(): BinaryStream{
        $stream = new BinaryStream();
        $stream->putInt($this->getUID());
        $stream->putSerializable($this->bot);
        return $stream;
    }

    public static function fromBinary(BinaryStream $stream): self{
        $uid = $stream->getInt();
        return new self(
            $stream->getSerializable(User::class), // bot
            $uid
        );
    }
}