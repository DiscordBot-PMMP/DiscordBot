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

use JaxkDev\DiscordBot\Models\Channels\VoiceChannel;
use JaxkDev\DiscordBot\Models\Member;
use JaxkDev\DiscordBot\Models\VoiceState;
use pocketmine\plugin\Plugin;

/**
 * Emitted when a member is moved from one voice channel to another.
 *
 * @see VoiceStateUpdated
 * @see VoiceChannelMemberJoined
 * @see VoiceChannelMemberLeft
 */
class VoiceChannelMemberMoved extends DiscordBotEvent{

    /** @var Member Member, note it contains old voice state. */
    private $member;

    /** @var VoiceChannel */
    private $previous_channel;

    /** @var VoiceChannel */
    private $new_channel;

    /** @var VoiceState */
    private $voice_state;

    public function __construct(Plugin $plugin, Member $member, VoiceChannel $previous_channel, VoiceChannel $new_channel,
                                VoiceState $voice_state){
        parent::__construct($plugin);
        $this->member = $member;
        $this->previous_channel = $previous_channel;
        $this->new_channel = $new_channel;
        $this->voice_state = $voice_state;
    }

    public function getMember(): Member{
        return $this->member;
    }

    public function getPreviousChannel(): VoiceChannel{
        return $this->previous_channel;
    }

    public function getNewChannel(): VoiceChannel{
        return $this->new_channel;
    }

    public function getVoiceState(): VoiceState{
        return $this->voice_state;
    }
}