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

namespace JaxkDev\DiscordBot\Communication\Packets;

class Heartbeat extends Packet{

	/** @var float */
	private $heartbeat;

	public function getHeartbeat(): float{
		return $this->heartbeat;
	}

	public function setHeartbeat(float $heartbeat): void{
		$this->heartbeat = $heartbeat;
	}

	public function serialize(): ?string{
		return serialize([$this->UID, $this->heartbeat]);
	}

	public function unserialize($serialized): void{
		[$this->UID, $this->heartbeat] = unserialize($serialized);
	}
}