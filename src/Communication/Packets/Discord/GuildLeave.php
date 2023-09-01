<?php

/*
 * DiscordBot, PocketMine-MP Plugin.
 *
 * Licensed under the Open Software License version 3.0 (OSL-3.0)
 * Copyright (C) 2020-present JaxkDev
 *
 * Discord :: JaxkDev
 * Email   :: JaxkDev@gmail.com
 */

namespace JaxkDev\DiscordBot\Communication\Packets\Discord;

use JaxkDev\DiscordBot\Communication\BinaryStream;
use JaxkDev\DiscordBot\Communication\Packets\Packet;
use JaxkDev\DiscordBot\Models\Guild\Guild;

final class GuildLeave extends Packet{

    public const SERIALIZE_ID = 209;

    private string $guild_id;

    /** Guild if cached. */
    private ?Guild $cached_guild;

    public function __construct(string $guild_id, ?Guild $cached_guild, ?int $uid = null){
        parent::__construct($uid);
        $this->guild_id = $guild_id;
        $this->cached_guild = $cached_guild;
    }

    public function getGuildId(): string{
        return $this->guild_id;
    }

    public function getCachedGuild(): ?Guild{
        return $this->cached_guild;
    }

    public function binarySerialize(): BinaryStream{
        $stream = new BinaryStream();
        $stream->putInt($this->getUID());
        $stream->putString($this->guild_id);
        $stream->putNullableSerializable($this->cached_guild);
        return $stream;
    }

    public static function fromBinary(BinaryStream $stream): self{
        $uid = $stream->getInt();
        return new self(
            $stream->getString(),                           // guild_id
            $stream->getNullableSerializable(Guild::class), // cached_guild
            $uid
        );
    }
}