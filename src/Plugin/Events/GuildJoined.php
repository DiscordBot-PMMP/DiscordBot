<?php
/*
 * DiscordBot, PocketMine-MP Plugin.
 *
 * Licensed under the Open Software License version 3.0 (OSL-3.0)
 * Copyright (C) 2020-present JaxkDev
 *
 * Twitter :: @JaxkDev
 * Discord :: JaxkDev#2698
 * Email   :: JaxkDev@gmail.com
 */

namespace JaxkDev\DiscordBot\Plugin\Events;

use JaxkDev\DiscordBot\Models\Channels\Channel;
use JaxkDev\DiscordBot\Models\Member;
use JaxkDev\DiscordBot\Models\Role;
use JaxkDev\DiscordBot\Models\Guild;
use pocketmine\plugin\Plugin;

/**
 * Emitted when the bot joins a discord guild.
 * 
 * @see GuildDeleted Emitted when the bot leaves a guild
 * @see GuildUpdated Emitted when a guild the bot is in has been updated.
 */
class GuildJoined extends DiscordBotEvent{

    private Guild $guild;

    /** @var Role[] */
    private array $roles;

    /** @var Channel[] */
    private array $channels;

    /** @var Member[] */
    private array $members;

    /**
     * @param Role[]    $roles
     * @param Channel[] $channels
     * @param Member[]  $members
     */
    public function __construct(Plugin $plugin, Guild $guild, array $roles, array $channels, array $members){
        parent::__construct($plugin);
        $this->guild = $guild;
        $this->roles = $roles;
        $this->channels = $channels;
        $this->members = $members;
    }

    public function getGuild(): Guild{
        return $this->guild;
    }

    /**
     * @return Role[]
     */
    public function getRoles(): array{
        return $this->roles;
    }

    /**
     * @return Channel[]
     */
    public function getChannels(): array{
        return $this->channels;
    }

    /**
     * @return Member[]
     */
    public function getMembers(): array{
        return $this->members;
    }
}