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

namespace JaxkDev\DiscordBot\Communication\Packets\Discord;

use JaxkDev\DiscordBot\Models\Channels\GuildChannel;
use JaxkDev\DiscordBot\Models\Member;
use JaxkDev\DiscordBot\Models\Role;
use JaxkDev\DiscordBot\Models\Guild;
use JaxkDev\DiscordBot\Communication\Packets\Packet;

class GuildJoin extends Packet{

    /** @var Guild */
    private $guild;

    /** @var GuildChannel[] */
    private $channels;

    /** @var Member[] */
    private $members;

    /** @var Role[] */
    private $roles;

    /**
     * GuildJoin constructor.
     *
     * @param Guild           $guild
     * @param GuildChannel[] $channels
     * @param Member[]        $members
     * @param Role[]          $roles
     */
    public function __construct(Guild $guild, array $channels, array $members, array $roles){
        parent::__construct();
        $this->guild = $guild;
        $this->channels = $channels;
        $this->members = $members;
        $this->roles = $roles;
    }

    public function getGuild(): Guild{
        return $this->guild;
    }

    /** @return GuildChannel[] */
    public function getChannels(): array{
        return $this->channels;
    }

    /** @return Role[] */
    public function getRoles(): array{
        return $this->roles;
    }

    /** @return Member[] */
    public function getMembers(): array{
        return $this->members;
    }

    public function __serialize(): array{
        return [
            $this->UID,
            $this->guild,
            $this->roles,
            $this->channels,
            $this->members
        ];
    }

    public function __unserialize(array $data): void{
        [
            $this->UID,
            $this->guild,
            $this->roles,
            $this->channels,
            $this->members
        ] = $data;
    }
}