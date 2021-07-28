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
use pocketmine\plugin\Plugin;

/**
 * Emitted when a member leaves a voice channel.
 *
 * @see VoiceStateUpdated
 * @see VoiceChannelMemberJoined
 * @see VoiceChannelMemberMoved
 */
class VoiceChannelMemberLeft extends DiscordBotEvent{

    /** @var Member Member, note it contains old voice state. */
    private $member;

    /** @var VoiceChannel */
    private $channel;

    public function __construct(Plugin $plugin, Member $member, VoiceChannel $channel){
        parent::__construct($plugin);
        $this->member = $member;
        $this->channel = $channel;
    }

    public function getMember(): Member{
        return $this->member;
    }

    public function getChannel(): VoiceChannel{
        return $this->channel;
    }
}