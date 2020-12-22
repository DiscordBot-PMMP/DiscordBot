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

	/** @var string */
	private $username;

	/** @var string */
	private $discriminator;

	/** @var string */
	private $avatar_url;

	/** @var string */
	private $status;

	/** @var Activity */
	private $activity;

	/** @var null|string */
	private $nickname;

	/** @var int */
	private $join_timestamp;

	/** @var null|int */
	private $boost_timestamp;

	/** @var string[] */
	private $roles_id;

	/** @var string */
	private $guild_id;

	//Email, Bot, Verified, Locale etc not inc.

	public function getId(): string{
		return $this->id;
	}

	public function setId(string $id): Member{
		$this->id = $id;
		return $this;
	}

	public function getUsername(): string{
		return $this->username;
	}

	public function setUsername(string $username): Member{
		$this->username = $username;
		return $this;
	}

	public function getDiscriminator(): string{
		return $this->discriminator;
	}

	public function setDiscriminator(string $discriminator): Member{
		$this->discriminator = $discriminator;
		return $this;
	}

	public function getAvatarUrl(): string{
		return $this->avatar_url;
	}

	public function setAvatarUrl(string $avatar_url): Member{
		$this->avatar_url = $avatar_url;
		return $this;
	}

	public function getStatus(): string{
		return $this->status;
	}

	public function setStatus(string $status): Member{
		$this->status = $status;
		return $this;
	}

	public function getActivity(): Activity{
		return $this->activity;
	}

	public function setActivity(Activity $activity): Member{
		$this->activity = $activity;
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
	 * @return string[]
	 */
	public function getRolesId(): array{
		return $this->roles_id;
	}

	/**
	 * @param string[] $roles_id
	 * @return self
	 */
	public function setRolesId(array $roles_id): Member{
		$this->roles_id = $roles_id;
		return $this;
	}

	public function getGuildId(): string{
		return $this->guild_id;
	}

	public function setGuildId(string $guild_id): Member{
		$this->guild_id = $guild_id;
		return $this;
	}

	//----- Serialization -----//

	public function serialize(): ?string{
		return serialize([
			$this->id,
			$this->username,
			$this->discriminator,
			$this->avatar_url,
			$this->status,
			$this->activity,
			$this->nickname,
			$this->join_timestamp,
			$this->boost_timestamp,
			$this->roles_id,
			$this->guild_id
		]);
	}

	public function unserialize($serialized): void{
		[
			$this->id,
			$this->username,
			$this->discriminator,
			$this->avatar_url,
			$this->status,
			$this->activity,
			$this->nickname,
			$this->join_timestamp,
			$this->boost_timestamp,
			$this->roles_id,
			$this->guild_id
		] = unserialize($serialized);
	}
}