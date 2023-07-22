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

namespace JaxkDev\DiscordBot\Communication\Packets\Discord;

use JaxkDev\DiscordBot\Communication\Packets\Packet;
use JaxkDev\DiscordBot\Models\Channels\GuildChannel;
use JaxkDev\DiscordBot\Models\Guild\Guild;
use JaxkDev\DiscordBot\Models\Member;
use JaxkDev\DiscordBot\Models\Role;

/** @extends Packet<GuildJoin> */
class GuildJoin extends Packet{

    public const ID = 43;

    private Guild $guild;

    /** @var GuildChannel[] */
    private array $channels;

    /** @var Member[] */
    private array $members;

    /** @var Role[] */
    private array $roles;

    /**
     * @param GuildChannel[] $channels
     * @param Member[]        $members
     * @param Role[]          $roles
     */
    public function __construct(Guild $guild, array $channels, array $members, array $roles, ?int $uid = null){
        parent::__construct($uid);
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

    public function jsonSerialize(): array{
        return [
            "uid" => $this->UID,
            "guild" => $this->guild->jsonSerialize(),
            "channels" => array_map(fn(GuildChannel $channel) => $channel->jsonSerialize(), $this->channels),
            "members" => array_map(fn(Member $member) => $member->jsonSerialize(), $this->members),
            "roles" => array_map(fn(Role $role) => $role->jsonSerialize(), $this->roles)
        ];
    }

    public static function fromJson(array $data): self{
        return new self(
            Guild::fromJson($data["guild"]),
            array_map(fn(array $channel) => GuildChannel::fromJson($channel), $data["channels"]),
            array_map(fn(array $member) => Member::fromJson($member), $data["members"]),
            array_map(fn(array $role) => Role::fromJson($role), $data["roles"]),
            $data["uid"]
        );
    }
}