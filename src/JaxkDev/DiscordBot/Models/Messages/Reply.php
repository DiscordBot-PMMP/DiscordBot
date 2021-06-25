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

namespace JaxkDev\DiscordBot\Models\Messages;

class Reply extends Message{

	/** @var ?string ID of message replying to. */
	private $referenced_message_id;

	public function getReferencedMessageId(): ?string{
		return $this->referenced_message_id;
	}

	public function setReferencedMessageId(?string $referenced_message_id): void{
		$this->referenced_message_id = $referenced_message_id;
	}

	//----- Serialization -----//

	public function serialize(): ?string{
		return serialize([
			$this->id,
			$this->content,
			$this->embed,
			$this->author_id,
			$this->channel_id,
			$this->server_id,
			$this->timestamp,
			$this->everyone_mentioned,
			$this->users_mentioned,
			$this->roles_mentioned,
			$this->channels_mentioned,
			$this->referenced_message_id
		]);
	}

	public function unserialize($data): void{
		[
			$this->id,
			$this->content,
			$this->embed,
			$this->author_id,
			$this->channel_id,
			$this->server_id,
			$this->timestamp,
			$this->everyone_mentioned,
			$this->users_mentioned,
			$this->roles_mentioned,
			$this->channels_mentioned,
			$this->referenced_message_id
		] = unserialize($data);
	}
}