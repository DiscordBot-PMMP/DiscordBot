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

use JaxkDev\DiscordBot\Models\Embed\Embed;

class Webhook extends Message{

	/** @var Embed[] Max 10 in webhook message. */
	private $embeds = [];

	/** @var String */
	private $webhook_id;

	//Hmm...
	public function getEmbed(): ?Embed{
		throw new \AssertionError("Webhook messages must use getEmbeds()");
	}

	public function setEmbed(Embed $embed): void{
		throw new \AssertionError("Webhook messages must use setEmbeds()");
	}

	/** @return Embed[] */
	public function getEmbeds(): array{
		return $this->embeds;
	}

	/** @param Embed[] $embeds */
	public function setEmbeds(array $embeds): void{
		if(sizeof($embeds) > 10){
			throw new \AssertionError("Webhook messages are limited to 10 embeds.");
		}
		$this->embeds = $embeds;
	}

	public function getWebhookId(): string{
		return $this->webhook_id;
	}

	public function setWebhookId(string $webhook_id): void{
		$this->webhook_id = $webhook_id;
	}

	//----- Serialization -----//

	public function serialize(): ?string{
		return serialize([
			$this->id,
			$this->content,
			$this->embeds,
			$this->author_id,
			$this->channel_id,
			$this->server_id,
			$this->timestamp,
			$this->everyone_mentioned,
			$this->users_mentioned,
			$this->roles_mentioned,
			$this->channels_mentioned,
			$this->webhook_id
		]);
	}

	public function unserialize($data): void{
		[
			$this->id,
			$this->content,
			$this->embeds,
			$this->author_id,
			$this->channel_id,
			$this->server_id,
			$this->timestamp,
			$this->everyone_mentioned,
			$this->users_mentioned,
			$this->roles_mentioned,
			$this->channels_mentioned,
			$this->webhook_id
		] = unserialize($data);
	}
}