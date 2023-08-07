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

use JaxkDev\DiscordBot\Plugin\Utils;
use pocketmine\plugin\Plugin;

/**
 * Emitted when a member leaves a discord guild.
 * 
 * @see MemberJoined
 * @see MemberUpdated
 */
class MemberLeft extends DiscordBotEvent{

    private string $member_id;

    public function __construct(Plugin $plugin, string $member_id){
        parent::__construct($plugin);
        if(Utils::validDiscordSnowflake($member_id)){
            $this->member_id = $member_id;
        }else{
            throw new \AssertionError("Invalid member_id given.");
        }
    }

    public function getMemberId(): string{
        return $this->member_id;
    }
}