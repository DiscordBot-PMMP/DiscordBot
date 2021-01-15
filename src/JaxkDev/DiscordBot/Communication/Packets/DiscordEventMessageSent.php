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

use JaxkDev\DiscordBot\Communication\Models\Message;

class DiscordEventMessageSent extends Packet{

	/** @var Message */
	private $message;

	public function getMessage(): Message{
		return $this->message;
	}

	public function setMessage(Message $message): void{
		$this->message = $message;
	}

	public function serialize(): ?string{
		return serialize([$this->UID, $this->message]);
	}

	public function unserialize($serialized): void{
		[$this->UID, $this->message] = unserialize($serialized);
	}
}