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

namespace JaxkDev\DiscordBot\Communication\Packets;

use JaxkDev\DiscordBot\Communication\Models\Member;
use JaxkDev\DiscordBot\Communication\Models\Server;

class MemberJoin extends Packet {

	const ID = 4;

	/** @var Member */
	private $member;

	/** @var Server */
	private $server;

	public function getHeartbeat(): float{
		return $this->heartbeat;
	}

	/**
	 * @inheritDoc
	 */
	public function serialize(): ?string{
		return strval($this->heartbeat);
	}

	/**
	 * @inheritDoc
	 */
	public function unserialize($serialized): void{
		// TODO Load server from cache & add member to cache.
		$this->heartbeat = floatval($serialized);
	}
}