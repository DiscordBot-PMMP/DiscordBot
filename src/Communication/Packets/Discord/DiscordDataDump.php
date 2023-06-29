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
use JaxkDev\DiscordBot\Models\Ban;
use JaxkDev\DiscordBot\Models\Channels\GuildChannel;
use JaxkDev\DiscordBot\Models\Guild\Guild;
use JaxkDev\DiscordBot\Models\Invite;
use JaxkDev\DiscordBot\Models\Member;
use JaxkDev\DiscordBot\Models\Role;
use JaxkDev\DiscordBot\Models\User;

class DiscordDataDump extends Packet{

    public const ID = 41;

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

    public function jsonSerialize(): array{
        return [
            "uid" => $this->UID,
            "guilds" => array_map(fn(Guild $guild) => $guild->jsonSerialize(), $this->guilds),
            "channels" => [],//array_map(fn(GuildChannel $channel) => $channel->jsonSerialize(), $this->channels), TODO
            "roles" => array_map(fn(Role $role) => $role->jsonSerialize(), $this->roles),
            "invites" => array_map(fn(Invite $invite) => $invite->jsonSerialize(), $this->invites),
            "bans" => array_map(fn(Ban $ban) => $ban->jsonSerialize(), $this->bans),
            "members" => array_map(fn(Member $member) => $member->jsonSerialize(), $this->members),
            "users" => array_map(fn(User $user) => $user->jsonSerialize(), $this->users),
            "bot_user" => ($this->bot_user ?? null) !== null ? $this->bot_user->jsonSerialize() : null,
            "timestamp" => $this->timestamp
        ];
    }

    public static function fromJson(array $data): self{
        $packet = new self($data["uid"]);
        $packet->guilds = array_map(fn(array $guild) => Guild::fromJson($guild), $data["guilds"]);
        $packet->channels = [];//array_map(fn(array $channel) => GuildChannel::fromJson($channel), $data["channels"]); TODO
        $packet->roles = array_map(fn(array $role) => Role::fromJson($role), $data["roles"]);
        $packet->invites = array_map(fn(array $invite) => Invite::fromJson($invite), $data["invites"]);
        $packet->bans = array_map(fn(array $ban) => Ban::fromJson($ban), $data["bans"]);
        $packet->members = array_map(fn(array $member) => Member::fromJson($member), $data["members"]);
        $packet->users = array_map(fn(array $user) => User::fromJson($user), $data["users"]);
        $packet->bot_user = ($data["bot_user"] ?? null) !== null ? User::fromJson($data["bot_user"]) : null;
        $packet->timestamp = $data["timestamp"];
        return $packet;
    }
}