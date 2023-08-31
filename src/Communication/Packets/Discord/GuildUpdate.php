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

final class GuildUpdate extends Packet{

    public const SERIALIZE_ID = 210;

    private Guild $guild;

    private ?Guild $old_guild;

    public function __construct(Guild $guild, ?Guild $old_guild, ?int $uid = null){
        parent::__construct($uid);
        $this->guild = $guild;
        $this->old_guild = $old_guild;
    }

    public function getGuild(): Guild{
        return $this->guild;
    }

    public function getOldGuild(): ?Guild{
        return $this->old_guild;
    }

    public function binarySerialize(): BinaryStream{
        $stream = new BinaryStream();
        $stream->putInt($this->getUID());
        $stream->putSerializable($this->guild);
        $stream->putNullableSerializable($this->old_guild);
        return $stream;
    }

    public static function fromBinary(BinaryStream $stream): self{
        $uid = $stream->getInt();
        return new self(
            $stream->getSerializable(Guild::class),         // guild
            $stream->getNullableSerializable(Guild::class), // old_guild
            $uid
        );
    }
}