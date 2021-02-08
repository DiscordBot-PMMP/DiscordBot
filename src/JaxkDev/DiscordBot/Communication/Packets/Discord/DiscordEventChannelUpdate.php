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

use JaxkDev\DiscordBot\Communication\Models\Channel;
use JaxkDev\DiscordBot\Communication\Packets\Packet;

class DiscordEventChannelUpdate extends Packet{

	/** @var Channel */
	private $channel;

	public function getChannel(): Channel{
		return $this->channel;
	}

	public function setChannel(Channel $channel): void{
		$this->channel = $channel;
	}

	public function serialize(): ?string{
		return serialize([$this->UID, $this->channel]);
	}

	public function unserialize($serialized): void{
		[$this->UID, $this->channel] = unserialize($serialized);
	}
}