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

class Ban implements \Serializable{

	/** @var string */
	private $server_id;

	/** @var string */
	private $user_id;

	/** @var string|null */
	private $reason = null;

	/** @var int|null Only present on banRequest. */
	private $daysToDelete = null;

	public function getId(): string{
		return $this->server_id.".".$this->user_id;
	}

	public function getServerId(): string{
		return $this->server_id;
	}

	public function setServerId(string $server_id): void{
		$this->server_id = $server_id;
	}

	public function getUserId(): string{
		return $this->user_id;
	}

	public function setUserId(string $user_id): void{
		$this->user_id = $user_id;
	}

	public function getReason(): ?string{
		return $this->reason;
	}

	public function setReason(?string $reason): void{
		$this->reason = $reason;
	}

	public function getDaysToDelete(): ?int{
		return $this->daysToDelete;
	}

	public function setDaysToDelete(?int $daysToDelete): void{
		$this->daysToDelete = $daysToDelete;
	}

	//----- Serialization -----//

	public function serialize(): ?string{
		return serialize([
			$this->server_id,
			$this->user_id,
			$this->reason,
			$this->daysToDelete
		]);
	}

	public function unserialize($data): void{
		[
			$this->server_id,
			$this->user_id,
			$this->reason,
			$this->daysToDelete
		] = unserialize($data);
	}
}