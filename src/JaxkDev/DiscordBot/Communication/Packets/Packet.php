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

abstract class Packet implements \Serializable {

	/* PacketID Not used yet, just for auto generating packet map */
	const ID = 0;

	public abstract function serialize(): ?string;

	public abstract function unserialize($serialized): void;
}