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

    private VoiceState $voice_state;

    public function __construct(VoiceState $voice_state, ?int $uid = null){
        parent::__construct($uid);
        $this->voice_state = $voice_state;
    }

    public function getVoiceState(): VoiceState{
        return $this->voice_state;
    }

    public function binarySerialize(): BinaryStream{
        $stream = new BinaryStream();
        $stream->putInt($this->getUID());
        $stream->putSerializable($this->voice_state);
        return $stream;
    }

    public static function fromBinary(BinaryStream $stream): self{
        $uid = $stream->getInt();
        return new self(
            $stream->getSerializable(VoiceState::class), // voice_state
            $uid
        );
    }
}