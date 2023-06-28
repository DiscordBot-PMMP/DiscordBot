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
 * Emitted when a guild the bot is in has been deleted, or the bot left/was kicked.
 * 
 * @see GuildUpdated Emitted when a guild the bot is in has been updated.
 * @see GuildJoined Emitted when the bot joins a guild.
 */
class GuildDeleted extends DiscordBotEvent{

    private Guild $guild;

    public function __construct(Plugin $plugin, Guild $guild){
        parent::__construct($plugin);
        $this->guild = $guild;
    }

    public function getGuild(): Guild{
        return $this->guild;
    }
}