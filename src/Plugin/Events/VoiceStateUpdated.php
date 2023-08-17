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

use JaxkDev\DiscordBot\Models\Member;
use JaxkDev\DiscordBot\Models\VoiceState;
use pocketmine\plugin\Plugin;

/**
 * Emitted when a member is deafened/muted or self deafened/muted.
 *
 * @see VoiceChannelMemberJoined
 * @see VoiceChannelMemberMoved
 * @see VoiceChannelMemberLeft
 */
class VoiceStateUpdated extends DiscordBotEvent{

    /** Note member contains old voice state. */
    private Member $member;

    /** New voice state. */
    private VoiceState $voice_state;

    public function __construct(Plugin $plugin, Member $member, VoiceState $voice_state){
        parent::__construct($plugin);
        $this->member = $member;
        $this->voice_state = $voice_state;
    }

    public function getMember(): Member{
        return $this->member;
    }

    public function getVoiceState(): VoiceState{
        return $this->voice_state;
    }
}