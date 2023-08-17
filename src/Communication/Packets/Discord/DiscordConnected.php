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

class DiscordConnected extends Packet{

    public const SERIALIZE_ID = 5;

    private User $bot_user;

    public function __construct(User $bot_user, ?int $UID = null){
        parent::__construct($UID);
        $this->bot_user = $bot_user;
    }

    public function getBotUser(): User{
        return $this->bot_user;
    }

    public function binarySerialize(): BinaryStream{
        $stream = new BinaryStream();
        $stream->putSerializable($this->bot_user);
        return $stream;
    }

    public static function fromBinary(BinaryStream $stream): self{
        return new self(
            $stream->getSerializable(User::class)
        );
    }
}