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

class Invite implements \Serializable{

	/** @var string Also used as ID internally. */
	private $code;

	/** @var string */
	private $server_id;

	/** @var string */
	private $channel_id;

	/** @var int How long in seconds from creation time to expire, 0 for never. */
	private $max_age;

	/** @var int Timestamp */
	private $created_at;

	/** @var bool */
	private $temporary;

	/** @var int How many times has this invite been used | NOTICE: This does not get updated when used */
	private $uses;

	/** @var int 0 for unlimited uses */
	private $max_uses;

	/** @var string Member ID */
	private $creator;

	public function getCode(): string{
		return $this->code;
	}

	public function setCode(string $code): void{
		$this->code = $code;
	}

	public function getServerId(): string{
		return $this->server_id;
	}

	public function setServerId(string $server_id): void{
		$this->server_id = $server_id;
	}

	public function getChannelId(): string{
		return $this->channel_id;
	}

	public function setChannelId(string $channel_id): void{
		$this->channel_id = $channel_id;
	}

	public function getMaxAge(): int{
		return $this->max_age;
	}

	public function setMaxAge(int $max_age): void{
		$this->max_age = $max_age;
	}

	public function getCreatedAt(): int{
		return $this->created_at;
	}

	public function setCreatedAt(int $created_at): void{
		$this->created_at = $created_at;
	}

	public function isTemporary(): bool{
		return $this->temporary;
	}

	public function setTemporary(bool $temporary): void{
		$this->temporary = $temporary;
	}

	public function getUses(): int{
		return $this->uses;
	}

	public function setUses(int $uses): void{
		$this->uses = $uses;
	}

	public function getMaxUses(): int{
		return $this->max_uses;
	}

	public function setMaxUses(int $max_uses): void{
		$this->max_uses = $max_uses;
	}

	public function getCreator(): string{
		return $this->creator;
	}

	public function setCreator(string $creator): void{
		$this->creator = $creator;
	}

	//----- Serialization -----//

	public function serialize(): ?string{
		return serialize([
			$this->code,
			$this->server_id,
			$this->channel_id,
			$this->max_age,
			$this->created_at,
			$this->temporary,
			$this->uses,
			$this->max_uses,
			$this->creator
		]);
	}

	public function unserialize($data): void{
		[
			$this->code,
			$this->server_id,
			$this->channel_id,
			$this->max_age,
			$this->created_at,
			$this->temporary,
			$this->uses,
			$this->max_uses,
			$this->creator
		] = unserialize($data);
	}
}