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

use JaxkDev\DiscordBot\Communication\BinaryStream;
use JaxkDev\DiscordBot\Communication\Packets\Packet;

class RequestBanMember extends Packet{

    public const SERIALIZE_ID = 66;

    private string $guild_id;

    private string $user_id;

    /** @var int number of seconds to delete messages for, between 0 and 604800 (7 days) */
    private int $delete_message_seconds;

    private ?string $reason;

    public function __construct(string $guild_id, string $user_id, int $delete_message_seconds = 0, ?string $reason = null,
                                ?int $uid = null){
        parent::__construct($uid);
        $this->guild_id = $guild_id;
        $this->user_id = $user_id;
        $this->delete_message_seconds = $delete_message_seconds;
        $this->reason = $reason;
    }

    public function getGuildId(): string{
        return $this->guild_id;
    }

    public function getUserId(): string{
        return $this->user_id;
    }

    public function getDeleteMessageSeconds(): int{
        return $this->delete_message_seconds;
    }

    public function getReason(): ?string{
        return $this->reason;
    }

    public function binarySerialize(): BinaryStream{
        $stream = new BinaryStream();
        $stream->putString($this->guild_id);
        $stream->putString($this->user_id);
        $stream->putInt($this->delete_message_seconds);
        $stream->putNullableString($this->reason);
        return $stream;
    }

    public static function fromBinary(BinaryStream $stream): self{
        return new self(
            $stream->getString(),        // guild_id
            $stream->getString(),        // user_id
            $stream->getInt(),           // delete_message_seconds
            $stream->getNullableString() // reason
        );
    }
}