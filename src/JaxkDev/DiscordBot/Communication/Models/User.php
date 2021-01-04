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

class User implements \Serializable {

	/** @var string */
	private $id;

	/** @var string */
	private $username;

	/** @var string */
	private $discriminator;

	/** @var string */
	private $avatar_url;

	/** @var int */
	private $creation_timestamp;

	///** @var Activity */
	//private $activity;

	//Email, Bot, Verified, Locale etc not included yet.

	public function getId(): string{
		return $this->id;
	}

	public function setId(string $id): User{
		$this->id = $id;
		return $this;
	}

	public function getUsername(): string{
		return $this->username;
	}

	public function setUsername(string $username): User{
		$this->username = $username;
		return $this;
	}

	public function getDiscriminator(): string{
		return $this->discriminator;
	}

	public function setDiscriminator(string $discriminator): User{
		$this->discriminator = $discriminator;
		return $this;
	}

	public function getAvatarUrl(): string{
		return $this->avatar_url;
	}

	public function setAvatarUrl(string $avatar_url): User{
		$this->avatar_url = $avatar_url;
		return $this;
	}

	public function getCreationTimestamp(): int{
		return $this->creation_timestamp;
	}

	public function setCreationTimestamp(int $creation_timestamp): User{
		$this->creation_timestamp = $creation_timestamp;
		return $this;
	}

	/*
	public function getActivity(): Activity{
		return $this->activity;
	}

	public function setActivity(Activity $activity): User{
		$this->activity = $activity;
		return $this;
	}
	*/

	//----- Serialization -----//

	public function serialize(): ?string{
		return serialize([
			$this->id,
			$this->username,
			$this->discriminator,
			$this->avatar_url,
			$this->creation_timestamp
			//$this->activity
		]);
	}

	public function unserialize($serialized): void{
		[
			$this->id,
			$this->username,
			$this->discriminator,
			$this->avatar_url,
			$this->creation_timestamp
			//$this->activity
		] = unserialize($serialized);
	}
}