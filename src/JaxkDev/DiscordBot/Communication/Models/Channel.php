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

namespace JaxkDev\DiscordBot\Communication\Models;

class Channel implements \Serializable {

	/**
	 * @inheritDoc
	 */
	public function serialize(): ?string{
		// TODO: Implement serialize() method.
		return "Serialized";
	}

	/**
	 * @inheritDoc
	 */
	public function unserialize($serialized): void{
		// TODO: Implement unserialize() method.
	}
}