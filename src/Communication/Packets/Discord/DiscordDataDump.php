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

use JaxkDev\DiscordBot\Models\Ban;
use JaxkDev\DiscordBot\Models\Channels\GuildChannel;
use JaxkDev\DiscordBot\Models\Invite;
use JaxkDev\DiscordBot\Models\Member;
use JaxkDev\DiscordBot\Models\Role;
use JaxkDev\DiscordBot\Models\Guild;
use JaxkDev\DiscordBot\Models\User;
use JaxkDev\DiscordBot\Communication\Packets\Packet;

class DiscordDataDump extends Packet{

    /** @var Guild[] */
    private array $guilds = [];

    /** @var GuildChannel[] */
    private array $channels = [];

    /** @var Role[] */
    private array $roles = [];

    /** @var Invite[] */
    private array $invites = [];

    /** @var Ban[] */
    private array $bans = [];

    /** @var Member[] */
    private array $members = [];

    /** @var User[] */
    private array $users = [];

    private ?User $bot_user = null;

    private int $timestamp;

    /**
     * @return Guild[]
     */
    public function getGuilds(): array{
        return $this->guilds;
    }

    public function addGuild(Guild $guild): void{
        $this->guilds[] = $guild;
    }

    /**
     * @return GuildChannel[]
     */
    public function getChannels(): array{
        return $this->channels;
    }

    public function addChannel(GuildChannel $channel): void{
        $this->channels[] = $channel;
    }

    /**
     * @return Role[]
     */
    public function getRoles(): array{
        return $this->roles;
    }

    public function addRole(Role $role): void{
        $this->roles[] = $role;
    }

    /**
     * @return Invite[]
     */
    public function getInvites(): array{
        return $this->invites;
    }

    public function addInvite(Invite $invite): void{
        $this->invites[] = $invite;
    }

    /**
     * @return Ban[]
     */
    public function getBans(): array{
        return $this->bans;
    }

    public function addBan(Ban $ban): void{
        $this->bans[] = $ban;
    }

    /**
     * @return Member[]
     */
    public function getMembers(): array{
        return $this->members;
    }

    public function addMember(Member $member): void{
        $this->members[] = $member;
    }

    /**
     * @return User[]
     */
    public function getUsers(): array{
        return $this->users;
    }

    public function addUser(User $user): void{
        $this->users[] = $user;
    }

    public function getBotUser(): ?User{
        return $this->bot_user;
    }

    public function setBotUser(User $bot): void{
        $this->bot_user = $bot;
    }

    public function getTimestamp(): int{
        return $this->timestamp;
    }

    public function setTimestamp(int $timestamp): void{
        $this->timestamp = $timestamp;
    }

    public function getSize(): int{
        return sizeof($this->guilds)+sizeof($this->channels)+sizeof($this->roles)+sizeof($this->members)
            +sizeof($this->users)+sizeof($this->bans)+sizeof($this->invites);
    }

    public function __serialize(): array{
        return [
            $this->UID,
            $this->guilds,
            $this->channels,
            $this->roles,
            $this->invites,
            $this->bans,
            $this->members,
            $this->users,
            $this->bot_user,
            $this->timestamp
        ];
    }

    public function __unserialize(array $data): void{
        [
            $this->UID,
            $this->guilds,
            $this->channels,
            $this->roles,
            $this->invites,
            $this->bans,
            $this->members,
            $this->users,
            $this->bot_user,
            $this->timestamp
        ] = $data;
    }
}