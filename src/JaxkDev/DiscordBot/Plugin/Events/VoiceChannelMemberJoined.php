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
 * Emitted when a member joins a voice channel.
 *
 * @see VoiceStateUpdated
 * @see VoiceChannelMemberLeft
 * @see VoiceChannelMemberMoved
 */
class VoiceChannelMemberJoined extends DiscordBotEvent{

    /** @var Member */
    private $member;

    /** @var VoiceChannel */
    private $channel;

    /** @var VoiceState New voice state. */
    private $voice_state;

    public function __construct(Plugin $plugin, Member $member, VoiceChannel $channel, VoiceState $voice_state){
        parent::__construct($plugin);
        $this->member = $member;
        $this->channel = $channel;
        $this->voice_state = $voice_state;
    }

    public function getMember(): Member{
        return $this->member;
    }

    public function getChannel(): VoiceChannel{
        return $this->channel;
    }

    public function getVoiceState(): VoiceState{
        return $this->voice_state;
    }
}