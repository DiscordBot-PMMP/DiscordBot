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

namespace JaxkDev\DiscordBot\Communication\Packets\Discord;

use JaxkDev\DiscordBot\Communication\Packets\Packet;

//TODO, Better to just tell the plugin were ready after data dump for heartbeats etc rather then have the plugin keep
//'guessing' through thread status.
class DiscordEventReady extends Packet{
	public function serialize(): ?string{
		return serialize([$this->UID]);
	}

	public function unserialize($serialized): void{
		[$this->UID] = unserialize($serialized);
	}
}