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
use JaxkDev\DiscordBot\Models\VoiceState;

class VoiceStateUpdate extends Packet{

    public const SERIALIZE_ID = 31;

    private string $guild_id;
    private string $user_id;

    private VoiceState $voice_state;

    public function __construct(string $guild_id, string $user_id, VoiceState $voice_state, ?int $uid = null){
        parent::__construct($uid);
        $this->guild_id = $guild_id;
        $this->user_id = $user_id;
        $this->voice_state = $voice_state;
    }

    public function getGuildId(): string{
        return $this->guild_id;
    }

    public function getUserId(): string{
        return $this->user_id;
    }

    public function getVoiceState(): VoiceState{
        return $this->voice_state;
    }

    public function binarySerialize(): BinaryStream{
        $stream = new BinaryStream();
        $stream->putString($this->guild_id);
        $stream->putString($this->user_id);
        $stream->putSerializable($this->voice_state);
        return $stream;
    }

    public static function fromBinary(BinaryStream $stream): self{
        return new self(
            $stream->getString(), // guild_id
            $stream->getString(), // user_id
            $stream->getSerializable(VoiceState::class) // voice_state
        );
    }
}