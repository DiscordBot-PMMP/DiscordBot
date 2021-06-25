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

namespace JaxkDev\DiscordBot\Communication\Packets\Plugin;

use JaxkDev\DiscordBot\Communication\Packets\Packet;

class RequestRevokeInvite extends Packet{

	/** @var string */
	private $server_id;

	/** @var string */
	private $invite_code;

	public function getServerId(): string{
		return $this->server_id;
	}

	public function setServerId(string $server_id): void{
		$this->server_id = $server_id;
	}

	public function getInviteCode(): string{
		return $this->invite_code;
	}

	public function setInviteCode(string $invite_code): void{
		$this->invite_code = $invite_code;
	}

	public function serialize(): ?string{
		return serialize([
			$this->UID,
			$this->server_id,
			$this->invite_code
		]);
	}

	public function unserialize($data): void{
		[
			$this->UID,
			$this->server_id,
			$this->invite_code
		] = unserialize($data);
	}
}