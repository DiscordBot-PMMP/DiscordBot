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

    /** @var Member Member, note it contains old voice state. */
    private $member;

    /** @var VoiceState New voice state. */
    private $voice_state;

    /**
     * @param Plugin     $plugin
     * @param Member     $member
     * @param VoiceState $voice_state
     */
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