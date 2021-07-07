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

	/** @var string */
	private $user_id;

	/** @var null|string */
	private $nickname;

	/** @var int */
	private $join_timestamp;

	/** @var null|int */
	private $boost_timestamp;

	/** @var RolePermissions */
	private $permissions;

	/** @var string[] */
	private $roles_id;

	/** @var string */
	private $server_id;

	///** @var Activity */
	//private $activity; Activities are actually from member :/

	/**
	 * Member constructor.
	 *
	 * @param string               $user_id
	 * @param int                  $join_timestamp
	 * @param string               $server_id
	 * @param string[]             $roles_id
	 * @param string|null          $nickname
	 * @param int|null             $boost_timestamp
	 * @param RolePermissions|null $permissions
	 */
	public function __construct(string $user_id, int $join_timestamp, string $server_id, array $roles_id = [],
								   ?string $nickname = null, ?int $boost_timestamp = null, RolePermissions $permissions = null){
		$this->setUserId($user_id);
		$this->setJoinTimestamp($join_timestamp);
		$this->setServerId($server_id);
		$this->setRolesId($roles_id);
		$this->setNickname($nickname);
		$this->setBoostTimestamp($boost_timestamp);
		$this->setPermissions($permissions ?? new RolePermissions());
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
	public function getRolesId(): array{
		return $this->roles_id;
	}

	/**
	 * @param string[] $roles_id
	 */
	public function setRolesId(array $roles_id): void{
		$this->roles_id = $roles_id;
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

	//----- Serialization -----//

	public function serialize(): ?string{
		return serialize([
			$this->user_id,
			$this->nickname,
			$this->join_timestamp,
			$this->boost_timestamp,
			$this->permissions,
			$this->roles_id,
			$this->server_id
		]);
	}

	public function unserialize($data): void{
		[
			$this->user_id,
			$this->nickname,
			$this->join_timestamp,
			$this->boost_timestamp,
			$this->permissions,
			$this->roles_id,
			$this->server_id
		] = unserialize($data);
	}
}