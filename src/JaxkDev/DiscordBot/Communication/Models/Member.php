<?php
/*
 * DiscordBot, PocketMine-MP Plugin.
 *
 * Licensed under the Open Software License version 3.0 (OSL-3.0)
 * Copyright (C) 2020 JaxkDev
 *
 * Twitter :: @JaxkDev
 * Discord :: JaxkDev#2698
 * Email   :: JaxkDev@gmail.com
 */

namespace JaxkDev\DiscordBot\Communication\Models;

class Member implements \Serializable {

	/** @var string */
	private $id;

	/** @var int */
	private $user_id;

	/** @var null|string */
	private $nickname;

	/** @var int */
	private $join_timestamp;

	/** @var null|int */
	private $boost_timestamp;

	/** @var int[] */
	private $roles_id;

	/** @var int */
	private $server_id;

	/**
	 * @description Composite key guild_id.user_id
	 * @see Member::getUserId()
	 */
	public function getId(): string{
		return $this->id;
	}

	/** @internal */
	public function setId(): void{
		$this->id = $this->server_id.".".$this->user_id;
	}

	public function getUserId(): int{
		return $this->user_id;
	}

	public function setUserId(int $id): Member{
		$this->user_id = $id;
		return $this;
	}

	public function getNickname(): ?string{
		return $this->nickname;
	}

	public function setNickname(?string $nickname): Member{
		$this->nickname = $nickname;
		return $this;
	}

	public function getJoinTimestamp(): int{
		return $this->join_timestamp;
	}

	public function setJoinTimestamp(int $join_timestamp): Member{
		$this->join_timestamp = $join_timestamp;
		return $this;
	}

	public function getBoostTimestamp(): ?int{
		return $this->boost_timestamp;
	}

	public function setBoostTimestamp(?int $boost_timestamp): Member{
		$this->boost_timestamp = $boost_timestamp;
		return $this;
	}

	/**
	 * @return int[]
	 */
	public function getRolesId(): array{
		return $this->roles_id;
	}

	/**
	 * @param int[] $roles_id
	 * @return Member
	 */
	public function setRolesId(array $roles_id): Member{
		$this->roles_id = $roles_id;
		return $this;
	}

	public function getServerId(): int{
		return $this->server_id;
	}

	public function setServerId(int $server_id): Member{
		$this->server_id = $server_id;
		return $this;
	}

	//----- Serialization -----//

	public function serialize(): ?string{
		return serialize([
			$this->id,
			$this->user_id,
			$this->nickname,
			$this->join_timestamp,
			$this->boost_timestamp,
			$this->roles_id,
			$this->server_id
		]);
	}

	public function unserialize($serialized): void{
		[
			$this->id,
			$this->user_id,
			$this->nickname,
			$this->join_timestamp,
			$this->boost_timestamp,
			$this->roles_id,
			$this->server_id
		] = unserialize($serialized);
	}
}