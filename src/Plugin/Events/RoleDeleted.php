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
 * Emitted when a role gets Deleted.
 *
 * @see RoleCreated
 * @see RoleUpdated
 */
class RoleDeleted extends DiscordBotEvent{

    private string $role_id;

    public function __construct(Plugin $plugin, string $role_id){
        parent::__construct($plugin);
        if(Utils::validDiscordSnowflake($role_id)){
            $this->role_id = $role_id;
        }else{
            throw new \AssertionError("Invalid role_id given.");
        }
    }

    public function getRoleId(): string{
        return $this->role_id;
    }
}