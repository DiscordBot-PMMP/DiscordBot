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

class Heartbeat extends Packet {

	/* PacketID */
	const ID = 1;

	/** @var float */
	private $heartbeat;

	public function getHeartbeat(): float{
		return $this->heartbeat;
	}

	public function setHeartbeat(float $heartbeat): void {
		$this->heartbeat = $heartbeat;
	}

	public function serialize(): ?string{
		return strval($this->heartbeat);
	}

	public function unserialize($serialized): void{
		$this->heartbeat = floatval($serialized);
	}
}