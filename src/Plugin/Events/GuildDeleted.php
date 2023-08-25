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
use JaxkDev\DiscordBot\Plugin\Utils;
use pocketmine\plugin\Plugin;

/**
 * Emitted when a guild the bot is in has been deleted, or the bot left/was kicked.
 *
 * @see GuildUpdated Emitted when a guild the bot is in has been updated.
 * @see GuildJoined Emitted when the bot joins a guild.
 */
final class GuildDeleted extends DiscordBotEvent{

    private string $guild_id;

    /** Guild if cached. */
    private ?Guild $cached_guild;

    public function __construct(Plugin $plugin, string $guild_id, ?Guild $cached_guild){
        parent::__construct($plugin);
        if(!Utils::validDiscordSnowflake($guild_id)){
            throw new \AssertionError("Invalid guild_id given.");
        }
        $this->guild_id = $guild_id;
        $this->cached_guild = $cached_guild;
    }

    public function getGuildId(): string{
        return $this->guild_id;
    }

    public function getCachedGuild(): ?Guild{
        return $this->cached_guild;
    }
}