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

namespace JaxkDev\DiscordBot\Models\Channels;

class TextChannel extends ServerChannel{

	/** @var string AKA Description. */
	private $topic;

	/** @var bool */
	private $nsfw = false;

	/** @var ?int In seconds | null when disabled. */
	private $rate_limit = null;

	/** @var ?string Category ID | null when not categorised. */
	private $category_id = null;

	/** @var string[] Message ID's */
	private $pins = [];

	//TODO Webhooks/integrations.

	public function getTopic(): string{
		return $this->topic;
	}

	public function setTopic(string $topic): void{
		$this->topic = $topic;
	}

	public function isNsfw(): bool{
		return $this->nsfw;
	}

	public function setNsfw(bool $nsfw): void{
		$this->nsfw = $nsfw;
	}

	public function getRateLimit(): ?int{
		return $this->rate_limit;
	}

	public function setRateLimit(?int $rate_limit): void{
		$this->rate_limit = $rate_limit;
	}

	public function getCategoryId(): ?string{
		return $this->category_id;
	}

	public function setCategoryId(?string $category_id): void{
		$this->category_id = $category_id;
	}

	/** @return string[] Message ID's */
	public function getPins(): array{
		return $this->pins;
	}

	/** @param string[] $pins Message ID's */
	public function setPins(array $pins): void{
		$this->pins = $pins;
	}

	//----- Serialization -----//

	public function serialize(): ?string{
		return serialize([
			$this->id,
			$this->name,
			$this->position,
			$this->member_permissions,
			$this->role_permissions,
			$this->server_id,
			$this->topic,
			$this->nsfw,
			$this->rate_limit,
			$this->category_id,
			$this->pins
		]);
	}

	public function unserialize($serialized): void{
		[
			$this->id,
			$this->name,
			$this->position,
			$this->member_permissions,
			$this->role_permissions,
			$this->server_id,
			$this->topic,
			$this->nsfw,
			$this->rate_limit,
			$this->category_id,
			$this->pins
		] = unserialize($serialized);
	}
}