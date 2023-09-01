<?php

/*
 * DiscordBot, PocketMine-MP Plugin.
 *
 * Licensed under the Open Software License version 3.0 (OSL-3.0)
 * Copyright (C) 2020-present JaxkDev
 *
 * Discord :: JaxkDev
 * Email   :: JaxkDev@gmail.com
 */

namespace JaxkDev\DiscordBot\Plugin\Events;

use JaxkDev\DiscordBot\Models\Role;
use JaxkDev\DiscordBot\Plugin\Utils;
use pocketmine\plugin\Plugin;

/**
 * Emitted when a role gets Deleted.
 *
 * @see RoleCreated
 * @see RoleUpdated
 */
final class RoleDeleted extends DiscordBotEvent{

    private string $guild_id;

    private string $role_id;

    private ?Role $cached_role;

    public function __construct(Plugin $plugin, string $guild_id, string $role_id, ?Role $cached_role){
        parent::__construct($plugin);
        if(!Utils::validDiscordSnowflake($guild_id)){
            throw new \AssertionError("Invalid guild_id given.");
        }
        if(!Utils::validDiscordSnowflake($role_id)){
            throw new \AssertionError("Invalid role_id given.");
        }
        $this->guild_id = $guild_id;
        $this->role_id = $role_id;
        $this->cached_role = $cached_role;
    }

    public function getGuildId(): string{
        return $this->guild_id;
    }

    public function getRoleId(): string{
        return $this->role_id;
    }

    public function getCachedRole(): ?Role{
        return $this->cached_role;
    }
}