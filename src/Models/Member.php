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

namespace JaxkDev\DiscordBot\Models;

use JaxkDev\DiscordBot\Models\Permissions\RolePermissions;
use JaxkDev\DiscordBot\Models\Presence\Presence;
use JaxkDev\DiscordBot\Plugin\Utils;

/** @link https://discord.com/developers/docs/resources/guild#guild-member-object */
class Member{

    /**
     * @link https://discord.com/developers/docs/resources/guild#guild-member-object-guild-member-flags
     * @var array<string, int>
     */
    public const FLAGS = [
        "DID_REJOIN" => (1 << 0),           // Member has left and rejoined the guild
        "COMPLETED_ONBOARDING" => (1 << 1), // Member has completed onboarding
        "BYPASSES_VERIFICATION" => (1 << 2),// Member is exempt from guild verification requirements
        "STARTED_ONBOARDING" => (1 << 3),   // Member has started onboarding
    ];

    /** The guild of this member. */
    private string $guild_id;

    /** The user this guild member represents */
    private string $user_id;

    /** The user's guild nickname */
    private ?string $nickname;

    /** The user's guild avatar  */
    private ?string $avatar;

    /**
     * array of role object IDs
     * @var string[]
     */
    private array $roles;

    /** When the user joined the guild (UNIX Timestamp) */
    private ?int $join_timestamp;

    /** When the user started boosting the guild (UNIX Timestamp) */
    private ?int $premium_since;

    /** Whether the user is deafened in voice channels */
    private bool $deaf;

    /** Whether the user is muted in voice channels */
    private bool $mute;

    /**
     * guild member flags represented as a bit set, defaults to 0
     * @see Member::FLAGS
     */
    private int $flag_bitwise;

    /**
     * All the flags possible and their current state.
     * @var array<string, bool>
     */
    private array $flags = [];

    /** Whether the user has not yet passed the guild's Membership Screening requirements */
    private ?bool $pending;

    /** Total permissions of the member in the channel, including overwrites, returned when in the interaction object */
    private RolePermissions $permissions;

    /**
     * When the user's timeout will expire and user will be able to communicate in the guild again,
     * null or a time in the past if the user is not timed out
     */
    private ?int $communications_disabled_until;

    /** null until a presence update is sent. */
    private ?Presence $presence;

    //No create method as this is not sent to the API first, always received pre-populated.

    /** @param string[] $roles */
    public function __construct(string $guild_id, string $user_id, ?string $nickname, ?string $avatar, array $roles,
                                ?int $join_timestamp, ?int $premium_since, bool $deaf, bool $mute, int $flags,
                                ?bool $pending, RolePermissions $permissions, ?int $communications_disabled_until,
                                ?Presence $presence){
        $this->setGuildId($guild_id);
        $this->setUserId($user_id);
        $this->setNickname($nickname);
        $this->setAvatar($avatar);
        $this->setRoles($roles);
        $this->setJoinTimestamp($join_timestamp);
        $this->setPremiumSince($premium_since);
        $this->setDeaf($deaf);
        $this->setMute($mute);
        $this->setFlagBitwise($flags, false);
        $this->setPending($pending);
        $this->setPermissions($permissions);
        $this->setCommunicationsDisabledUntil($communications_disabled_until);
        $this->setPresence($presence);
    }

    /**
     * Composite key guild_id.user_id
     * @see Member::getGuildId()
     * @see Member::getUserId()
     */
    public function getId(): string{
        return $this->guild_id.".".$this->user_id;
    }

    public function getGuildId(): string{
        return $this->guild_id;
    }

    public function setGuildId(string $guild_id): void{
        if(!Utils::validDiscordSnowflake($guild_id)){
            throw new \AssertionError("Guild ID '$guild_id' is invalid.");
        }
        $this->guild_id = $guild_id;
    }

    public function getUserId(): string{
        return $this->user_id;
    }

    public function setUserId(string $id): void{
        if(!Utils::validDiscordSnowflake($id)){
            throw new \AssertionError("User ID '$id' is invalid.");
        }
        $this->user_id = $id;
    }

    public function getNickname(): ?string{
        return $this->nickname;
    }

    public function setNickname(?string $nickname): void{
        $this->nickname = $nickname;
    }

    public function getAvatar(): ?string{
        return $this->avatar;
    }

    public function setAvatar(?string $avatar): void{
        $this->avatar = $avatar;
    }

    /** @return string[] Role IDs*/
    public function getRoles(): array{
        return $this->roles;
    }

    /** @param string[] $roles Role IDs */
    public function setRoles(array $roles): void{
        foreach($roles as $id){
            if(!Utils::validDiscordSnowflake($id)){
                throw new \AssertionError("Role ID '$id' is invalid.");
            }
        }
        $this->roles = $roles;
    }

    public function getJoinTimestamp(): ?int{
        return $this->join_timestamp;
    }

    public function setJoinTimestamp(?int $join_timestamp): void{
        $this->join_timestamp = $join_timestamp;
    }

    public function getPremiumSince(): ?int{
        return $this->premium_since;
    }

    public function setPremiumSince(?int $premium_since): void{
        $this->premium_since = $premium_since;
    }

    public function getDeaf(): bool{
        return $this->deaf;
    }

    public function setDeaf(bool $deaf): void{
        $this->deaf = $deaf;
    }

    public function getMute(): bool{
        return $this->mute;
    }

    public function setMute(bool $mute): void{
        $this->mute = $mute;
    }

    public function getFlagBitwise(): int{
        return $this->flag_bitwise;
    }

    public function setFlagBitwise(int $flag_bitwise, bool $recalculate = true): void{
        $this->flag_bitwise = $flag_bitwise;
        if($recalculate){
            $this->recalculateFlags();
        }
    }

    /**
     * Returns all the flags possible and their current state.
     * @return array<string, bool>
     */
    public function getFlags(): array{
        if(sizeof($this->flags) === 0){
            $this->recalculateFlags();
        }
        return $this->flags;
    }

    /** Get the state of a specific flag, null if not set. */
    public function getFlag(int $flags): ?bool{
        if(sizeof($this->flags) === 0){
            $this->recalculateFlags();
        }
        return $this->flags[$flags] ?? null;
    }

    /** Set the state of a specific flag */
    public function setFlag(int $flag, bool $state): void{
        if(sizeof($this->flags) === 0){
            $this->recalculateFlags();
        }
        if(!in_array($flag, array_keys(self::FLAGS), true)){
            throw new \AssertionError("Flag '$flag' is invalid.");
        }
        if($this->flags[$flag] === $state) return;
        $this->flags[$flag] = $state;
        $this->flag_bitwise ^= self::FLAGS[$flag];
    }

    public function getPending(): ?bool{
        return $this->pending;
    }

    public function setPending(?bool $pending): void{
        $this->pending = $pending;
    }

    public function getPermissions(): RolePermissions{
        return $this->permissions;
    }

    public function setPermissions(RolePermissions $permissions): void{
        $this->permissions = $permissions;
    }

    public function getCommunicationsDisabledUntil(): ?int{
        return $this->communications_disabled_until;
    }

    public function setCommunicationsDisabledUntil(?int $communications_disabled_until): void{
        $this->communications_disabled_until = $communications_disabled_until;
    }

    public function getPresence(): ?Presence{
        return $this->presence;
    }

    public function setPresence(?Presence $presence): void{
        $this->presence = $presence;
    }

    /**
     * Recalculate the flags from the bitwise value.
     * @internal
     */
    private function recalculateFlags(): void{
        $this->flags = [];
        foreach(self::FLAGS as $flag => $bitwise){
            $this->flags[$flag] = ($this->flag_bitwise & $bitwise) === $bitwise;
        }
    }

    //----- Serialization -----//

    public function __serialize(): array{
        return [
            $this->guild_id,
            $this->user_id,
            $this->nickname,
            $this->avatar,
            $this->roles,
            $this->join_timestamp,
            $this->premium_since,
            $this->deaf,
            $this->mute,
            $this->flag_bitwise,
            $this->pending,
            $this->permissions,
            $this->communications_disabled_until,
            $this->presence
        ];
    }

    public function __unserialize(array $data): void{
        [
            $this->guild_id,
            $this->user_id,
            $this->nickname,
            $this->avatar,
            $this->roles,
            $this->join_timestamp,
            $this->premium_since,
            $this->deaf,
            $this->mute,
            $this->flag_bitwise,
            $this->pending,
            $this->permissions,
            $this->communications_disabled_until,
            $this->presence
        ] = $data;
    }
}