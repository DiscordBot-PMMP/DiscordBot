<?php
/*
 * DiscordBot, PocketMine-MP Plugin.
 *
 * Licensed under the Open Software License version 3.0 (OSL-3.0)
 * Copyright (C) 2020-2021 JaxkDev
 *
 * Twitter :: @JaxkDev
 * Discord :: JaxkDev#2698
 * Email   :: JaxkDev@gmail.com
 */

namespace JaxkDev\DiscordBot\Communication\Packets\Discord;

use JaxkDev\DiscordBot\Communication\Packets\Packet;
use JaxkDev\DiscordBot\Models\VoiceState;

class VoiceStateUpdate extends Packet{

    /** @var string */
    private $member_id;

    /** @var VoiceState */
    private $voice_state;

    public function __construct(string $member_id, VoiceState $voice_state){
        parent::__construct();
        $this->member_id = $member_id;
        $this->voice_state = $voice_state;
    }

    public function getMemberId(): string{
        return $this->member_id;
    }

    public function getVoiceState(): VoiceState{
        return $this->voice_state;
    }

    public function serialize(): ?string{
        return serialize([
            $this->UID,
            $this->member_id,
            $this->voice_state
        ]);
    }

    public function unserialize($data): void{
        [
            $this->UID,
            $this->member_id,
            $this->voice_state
        ] = unserialize($data);
    }
}