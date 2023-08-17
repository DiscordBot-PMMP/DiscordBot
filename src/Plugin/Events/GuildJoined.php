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

use JaxkDev\DiscordBot\Models\Guild\Guild;
use pocketmine\plugin\Plugin;

/**
 * Emitted when the bot joins a discord guild.
 *
 * @see GuildDeleted Emitted when the bot leaves a guild
 * @see GuildUpdated Emitted when a guild the bot is in has been updated.
 */
class GuildJoined extends DiscordBotEvent{

    private Guild $guild;

    public function __construct(Plugin $plugin, Guild $guild){
        parent::__construct($plugin);
        $this->guild = $guild;
    }

    public function getGuild(): Guild{
        return $this->guild;
    }
}