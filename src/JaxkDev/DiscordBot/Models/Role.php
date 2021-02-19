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

class Role implements \Serializable{

	/** @var string */
	private $id;

	/** @var string */
	private $name;

	/** @var RolePermissions */
	private $permissions;

	/** @var int */
	private $colour;

	/** @var int */
	private $hoistedPosition;

	/** @var bool */
	private $mentionable;

	/** @var string */
	private $server_id;

	public function getId(): string{
		return $this->id;
	}

	public function setId(string $id): void{
		$this->id = $id;
	}

	public function getName(): string{
		return $this->name;
	}

	public function setName(string $name): void{
		$this->name = $name;
	}

	public function getPermissions(): RolePermissions{
		return $this->permissions;
	}

	public function setPermissions(RolePermissions $permissions): void{
		$this->permissions = $permissions;
	}

	public function getColour(): int{
		return $this->colour;
	}

	/**
	 * @param int $colour [0x000000 - 0xFFFFFF]
	 */
	public function setColour(int $colour): void{
		$this->colour = $colour;
	}

	/**
	 * @return int [-1 if not hoisted.]
	 */
	public function getHoistedPosition(): int{
		return $this->hoistedPosition;
	}

	public function setHoistedPosition(int $hoistedPosition): void{
		$this->hoistedPosition = $hoistedPosition;
	}

	public function isMentionable(): bool{
		return $this->mentionable;
	}

	public function setMentionable(bool $mentionable): void{
		$this->mentionable = $mentionable;
	}

	public function getServerId(): string{
		return $this->server_id;
	}

	public function setServerId(string $server_id): void{
		$this->server_id = $server_id;
	}

	//----- Serialization -----//

	public function serialize(): ?string{
		return serialize([
			$this->id,
			$this->name,
			$this->colour,
			$this->permissions,
			$this->mentionable,
			$this->hoistedPosition,
			$this->server_id
		]);
	}

	public function unserialize($serialized): void{
		[
			$this->id,
			$this->name,
			$this->colour,
			$this->permissions,
			$this->mentionable,
			$this->hoistedPosition,
			$this->server_id
		] = unserialize($serialized);
	}
}