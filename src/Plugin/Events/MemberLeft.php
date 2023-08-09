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

    private string $guild_id;

    private string $user_id;

    public function __construct(Plugin $plugin, string $guild_id, string $user_id){
        parent::__construct($plugin);
        if(Utils::validDiscordSnowflake($guild_id)){
            $this->guild_id = $guild_id;
        }else{
            throw new \AssertionError("Invalid guild_id provided.");
        }
        if(Utils::validDiscordSnowflake($user_id)){
            $this->user_id = $user_id;
        }else{
            throw new \AssertionError("Invalid user_id provided.");
        }
    }

    public function getGuildId(): string{
        return $this->guild_id;
    }

    public function getUserId(): string{
        return $this->user_id;
    }
}