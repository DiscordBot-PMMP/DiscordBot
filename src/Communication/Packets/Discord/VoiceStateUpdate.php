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
use JaxkDev\DiscordBot\Models\VoiceState;

class VoiceStateUpdate extends Packet{

    public const ID = 62;

    private string $member_id;

    private VoiceState $voice_state;

    public function __construct(string $member_id, VoiceState $voice_state, ?int $uid = null){
        parent::__construct($uid);
        $this->member_id = $member_id;
        $this->voice_state = $voice_state;
    }

    public function getMemberId(): string{
        return $this->member_id;
    }

    public function getVoiceState(): VoiceState{
        return $this->voice_state;
    }

    public function jsonSerialize(): array{
        return [
            "uid" => $this->UID,
            "member_id" => $this->member_id,
            "voice_state" => $this->voice_state->jsonSerialize()
        ];
    }

    public static function fromJson(array $data): self{
        return new self(
            $data["member_id"],
            VoiceState::fromJson($data["voice_state"]),
            $data["uid"]
        );
    }
}