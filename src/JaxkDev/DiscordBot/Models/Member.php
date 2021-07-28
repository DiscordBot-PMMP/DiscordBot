<?php
/*
 * DiscordBot, PocketMine-MP Plugin.
 *
 * Licensed under the Open Software License version 3.0 (OSL-3.0)
 * Copyright (C) 2020-2021 JaxkDev
 *
 * Twitter :: @JaxkDev
 * Discord :: JaxkDev#2698
 * Email   :: JaxkDev@gmail.com
 */

namespace JaxkDev\DiscordBot\Models;

use JaxkDev\DiscordBot\Models\Permissions\RolePermissions;
use JaxkDev\DiscordBot\Plugin\Utils;

class Member implements \Serializable{

    const STATUS_ONLINE = "online",
        STATUS_IDLE = "idle",
        STATUS_DND = "dnd",
        STATUS_OFFLINE = "offline";

    /** @var string */
    private $user_id;

    /** @var null|string */
    private $nickname;

    /** @var null|string null until a presence update is sent. */
    private $status;

    /** @var null|array{"mobile": string|null, "desktop": string|null, "web": string|null} null until a present update is sent. */
    private $client_status;

    /** @var int */
    private $join_timestamp;

    /** @var null|int */
    private $boost_timestamp;

    /** @var RolePermissions */
    private $permissions;

    /** @var string[] */
    private $roles;

    /** @var string */
    private $server_id;

    /** @var null|Activity[] */
    private $activities;

    /** @var null|VoiceState */
    private $voice_state;

    /**
     * Member constructor.
     *
     * @param string               $user_id
     * @param int                  $join_timestamp
     * @param string               $server_id
     * @param string[]             $roles
     * @param string|null          $nickname
     * @param int|null             $boost_timestamp
     * @param RolePermissions|null $permissions
     * @param Activity[]|null      $activities
     * @param VoiceState|null      $voice_state
     */
    public function __construct(string $user_id, int $join_timestamp, string $server_id, array $roles = [],
                                ?string $nickname = null, ?int $boost_timestamp = null, RolePermissions $permissions = null,
                                ?array $activities = null, ?VoiceState $voice_state = null){
        $this->setUserId($user_id);
        $this->setJoinTimestamp($join_timestamp);
        $this->setServerId($server_id);
        $this->setRoles($roles);
        $this->setNickname($nickname);
        $this->setBoostTimestamp($boost_timestamp);
        $this->setPermissions($permissions ?? new RolePermissions());
        $this->setActivities($activities);
        $this->setVoiceState($voice_state);
    }

    /**
     * @description Composite key guild_id.user_id
     * @see Member::getServerId()
     * @see Member::getUserId()
     */
    public function getId(): string{
        return $this->server_id.".".$this->user_id;
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

    public function getServerId(): string{
        return $this->server_id;
    }

    public function setServerId(string $server_id): void{
        if(!Utils::validDiscordSnowflake($server_id)){
            throw new \AssertionError("Server ID '$server_id' is invalid.");
        }
        $this->server_id = $server_id;
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

    public function serialize(): ?string{
        return serialize([
            $this->user_id,
            $this->nickname,
            $this->join_timestamp,
            $this->boost_timestamp,
            $this->permissions,
            $this->roles,
            $this->server_id,
            $this->status,
            $this->client_status,
            $this->activities,
            $this->voice_state
        ]);
    }

    public function unserialize($data): void{
        [
            $this->user_id,
            $this->nickname,
            $this->join_timestamp,
            $this->boost_timestamp,
            $this->permissions,
            $this->roles,
            $this->server_id,
            $this->status,
            $this->client_status,
            $this->activities,
            $this->voice_state
        ] = unserialize($data);
    }
}