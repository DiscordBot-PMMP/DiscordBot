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

namespace JaxkDev\DiscordBot\Plugin\Events;

use JaxkDev\DiscordBot\Models\VoiceState;
use pocketmine\plugin\Plugin;

/**
 * Emitted when a members voice state is updated, including but not limiting to:
 * - Joining/Leaving a VC
 * - Muting/Deafening locally
 * - Suppressed/muted/deafened by server
 * - Camera enabled/disabled
 */
class VoiceStateUpdated extends DiscordBotEvent{

    /** New voice state. */
    private VoiceState $voice_state;

    public function __construct(Plugin $plugin, VoiceState $voice_state){
        parent::__construct($plugin);
        $this->voice_state = $voice_state;
    }

    public function getVoiceState(): VoiceState{
        return $this->voice_state;
    }
}