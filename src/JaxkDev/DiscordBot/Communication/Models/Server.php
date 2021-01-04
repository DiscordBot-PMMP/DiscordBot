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

class Server implements \Serializable {

	/** @var string */
	private $id;

	/** @var string */
	private $name;

	/** @var string */
	private $icon_url;

	/** @var string */
	private $region;

	/** @var string */
	private $owner_id;

	/** @var float */
	private $creation_timestamp;

	/** @var bool */
	private $large;

	/** @var int */
	private $member_count;

	public function getId(): string{
		return $this->id;
	}

	public function setId(string $id): Server{
		$this->id = $id;
		return $this;
	}

	public function getName(): string{
		return $this->name;
	}

	public function setName(string $name): Server{
		$this->name = $name;
		return $this;
	}

	public function getIconUrl(): string{
		return $this->icon_url;
	}

	public function setIconUrl(string $icon_url): Server{
		$this->icon_url = $icon_url;
		return $this;
	}

	public function getRegion(): string{
		return $this->region;
	}

	public function setRegion(string $region): Server{
		$this->region = $region;
		return $this;
	}

	public function getOwnerId(): string{
		return $this->owner_id;
	}

	public function setOwnerId(string $owner_id): Server{
		$this->owner_id = $owner_id;
		return $this;
	}

	public function getCreationTimestamp(): float{
		return $this->creation_timestamp;
	}

	public function setCreationTimestamp(float $creation_timestamp): Server{
		$this->creation_timestamp = $creation_timestamp;
		return $this;
	}

	public function isLarge(): bool{
		return $this->large;
	}

	public function setLarge(bool $large): Server{
		$this->large = $large;
		return $this;
	}

	public function getMemberCount(): int{
		return $this->member_count;
	}

	public function setMemberCount(int $member_count): Server{
		$this->member_count = $member_count;
		return $this;
	}

	//----- Serialization -----//

	public function serialize(): ?string{
		return serialize([
			$this->id,
			$this->name,
			$this->icon_url,
			$this->region,
			$this->owner_id,
			$this->creation_timestamp,
			$this->large,
			$this->member_count
		]);
	}

	public function unserialize($serialized): void{
		[
			$this->id,
			$this->name,
			$this->icon_url,
			$this->region,
			$this->owner_id,
			$this->creation_timestamp,
			$this->large,
			$this->member_count
		] = unserialize($serialized);
	}
}