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

use pocketmine\plugin\Plugin;
use function JaxkDev\DiscordBot\Plugin\Utils\validDiscordSnowflake;

/**
 * Emitted when a role gets Deleted.
 *
 * @see RoleCreated
 * @see RoleUpdated
 */
class RoleDeleted extends DiscordBotEvent{

    private string $guild_id;

    private string $role_id;

    public function __construct(Plugin $plugin, string $guild_id, string $role_id){
        parent::__construct($plugin);
        if(validDiscordSnowflake($guild_id)){
            $this->guild_id = $guild_id;
        }else{
            throw new \AssertionError("Invalid guild_id given.");
        }
        if(validDiscordSnowflake($role_id)){
            $this->role_id = $role_id;
        }else{
            throw new \AssertionError("Invalid role_id given.");
        }
    }

    public function getGuildId(): string{
        return $this->guild_id;
    }

    public function getRoleId(): string{
        return $this->role_id;
    }
}