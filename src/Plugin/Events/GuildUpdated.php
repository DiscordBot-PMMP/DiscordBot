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

use JaxkDev\DiscordBot\Models\Guild\Guild;
use pocketmine\plugin\Plugin;

/**
 * Emitted when a guild the bot is in has been updated, eg Changed icon, name, region etc.
 *
 * @see GuildDeleted Emitted when the bot leaves a guild
 * @see GuildJoined Emitted when the bot joins a guild.
 */
final class GuildUpdated extends DiscordBotEvent{

    private Guild $guild;

    /** Old guild if cached. */
    private ?Guild $old_guild;

    public function __construct(Plugin $plugin, Guild $guild, ?Guild $old_guild){
        parent::__construct($plugin);
        $this->guild = $guild;
        $this->old_guild = $old_guild;
    }

    public function getGuild(): Guild{
        return $this->guild;
    }

    public function getOldGuild(): ?Guild{
        return $this->old_guild;
    }
}