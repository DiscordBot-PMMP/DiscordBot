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

class DiscordEventMessageDelete extends Packet{

	/** @var string */
	private $messageId;

	public function getMessageId(): string{
		return $this->messageId;
	}

	public function setMessageId(string $messageId): void{
		$this->messageId = $messageId;
	}

	public function serialize(): ?string{
		return serialize([$this->UID, $this->messageId]);
	}

	public function unserialize($serialized): void{
		[$this->UID, $this->messageId] = unserialize($serialized);
	}
}