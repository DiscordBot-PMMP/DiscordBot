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

use JaxkDev\DiscordBot\Models\Activity\Activity;
use JaxkDev\DiscordBot\Models\Permissions\RolePermissions;
use JaxkDev\DiscordBot\Plugin\Utils;

class Member{

    const STATUS_ONLINE = "online",
        STATUS_IDLE = "idle",
        STATUS_DND = "dnd",
        STATUS_OFFLINE = "offline";

    private string $user_id;

    private ?string $nickname;

    /** null until a presence update is sent. */
    private ?string $status;

    /** @var null|array{"mobile": string|null, "desktop": string|null, "web": string|null} null until a present update is sent. */
    private ?array $client_status;

    private int $join_timestamp;

    private ?int $boost_timestamp;

    private RolePermissions $permissions;

    /** @var string[] */
    private array $roles;

    private string $guild_id;

    /** @var null|Activity[] */
    private ?array $activities;

    private ?VoiceState $voice_state;

    /**
     * Member constructor.
     *
     * @param string[]             $roles
     * @param Activity[]|null      $activities
     */
    public function __construct(string $user_id, int $join_timestamp, string $guild_id, array $roles = [],
                                ?string $nickname = null, ?int $boost_timestamp = null, RolePermissions $permissions = null,
                                ?array $activities = null, ?VoiceState $voice_state = null){
        $this->setUserId($user_id);
        $this->setJoinTimestamp($join_timestamp);
        $this->setGuildId($guild_id);
        $this->setRoles($roles);
        $this->setNickname($nickname);
        $this->setBoostTimestamp($boost_timestamp);
        $this->setPermissions($permissions ?? new RolePermissions());
        $this->setActivities($activities);
        $this->setVoiceState($voice_state);
    }

    /**
     * @description Composite key guild_id.user_id
     * @see Member::getGuildId()
     * @see Member::getUserId()
     */
    public function getId(): string{
        return $this->guild_id.".".$this->user_id;
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

    public function getJoinTimestamp(): int{
        return $this->join_timestamp;
    }

    public function setJoinTimestamp(int $join_timestamp): void{
        $this->join_timestamp = $join_timestamp;
    }

    public function getBoostTimestamp(): ?int{
        return $this->boost_timestamp;
    }

    public function setBoostTimestamp(?int $boost_timestamp): void{
        $this->boost_timestamp = $boost_timestamp;
    }

    public function getPermissions(): RolePermissions{
        return $this->permissions;
    }

    public function setPermissions(RolePermissions $permissions): void{
        $this->permissions = $permissions;
    }

    /**
     * @return string[]
     */
    public function getRoles(): array{
        return $this->roles;
    }

    /**
     * @param string[] $roles
     */
    public function setRoles(array $roles): void{
        foreach($roles as $id){
            if(!Utils::validDiscordSnowflake($id)){
                throw new \AssertionError("Role ID '$id' is invalid.");
            }
        }
        $this->roles = $roles;
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

    public function getStatus(): ?string{
        return $this->status;
    }

    public function setStatus(?string $status): void{
        $this->status = $status;
    }

    /** @return null|array{"mobile": string|null, "desktop": string|null, "web": string|null} */
    public function getClientStatus(): ?array{
        return $this->client_status;
    }

    /** @param null|array{"mobile": string|null, "desktop": string|null, "web": string|null} $client_status*/
    public function setClientStatus(?array $client_status): void{
        //TODO Validate.
        $this->client_status = $client_status;
    }

    /** @return null|Activity[] */
    public function getActivities(): ?array{
        return $this->activities;
    }

    /** @param null|Activity[] $activities */
    public function setActivities(?array $activities): void{
        foreach($activities??[] as $activity){
            if(!$activity instanceof Activity){
                throw new \AssertionError("Activity not valid.");
            }
        }
        $this->activities = $activities;
    }

    public function getVoiceState(): ?VoiceState{
        return $this->voice_state;
    }

    public function setVoiceState(?VoiceState $voice_state): void{
        $this->voice_state = $voice_state;
    }
    //----- Serialization -----//

    public function __serialize(): array{
        return [
            $this->user_id,
            $this->nickname,
            $this->join_timestamp,
            $this->boost_timestamp,
            $this->permissions,
            $this->roles,
            $this->guild_id,
            $this->status,
            $this->client_status,
            $this->activities,
            $this->voice_state
        ];
    }

    public function __unserialize(array $data): void{
        [
            $this->user_id,
            $this->nickname,
            $this->join_timestamp,
            $this->boost_timestamp,
            $this->permissions,
            $this->roles,
            $this->guild_id,
            $this->status,
            $this->client_status,
            $this->activities,
            $this->voice_state
        ] = $data;
    }
}