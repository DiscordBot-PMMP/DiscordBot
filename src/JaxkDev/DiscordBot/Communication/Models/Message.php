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

namespace JaxkDev\DiscordBot\Communication\Models;

class Message implements \Serializable{

	const TYPE_NORMAL = 0;

	/** @var ?string */
	private $id;

	/** @var int */
	private $type = self::TYPE_NORMAL; //Not supporting others right now.

	/** @var string (<=2000) */
	private $content;

	/** @var ?string MemberID */
	private $author_id;

	/** @var string */
	private $channel_id;

	/** @var string */
	private $server_id;  //This is needed for faster handling discord side.

	/** @var ?float */
	private $timestamp;

	/** @var bool */
	private $everyone_mentioned = false;

	/** @var string[] */
	private $users_mentioned = [];

	/** @var string[] */
	private $roles_mentioned = [];

	/** @var string[] */
	private $channels_mentioned = [];

	public function getId(): ?string{
		return $this->id;
	}

	public function setId(string $id): Message{
		$this->id = $id;
		return $this;
	}

	public function getType(): int{
		return $this->type;
	}

	// No setType, intentional.

	public function getContent(): string{
		return $this->content;
	}

	public function setContent(string $content): Message{
		$this->content = $content;
		return $this;
	}

	public function getAuthorId(): ?string{
		return $this->author_id;
	}

	public function setAuthorId(?string $author_id): Message{
		$this->author_id = $author_id;
		return $this;
	}

	public function getChannelId(): string{
		return $this->channel_id;
	}

	public function setChannelId(string $channel_id): Message{
		$this->channel_id = $channel_id;
		return $this;
	}

	public function getServerId(): string{
		return $this->server_id;
	}

	public function setServerId(string $server_id): Message{
		$this->server_id = $server_id;
		return $this;
	}

	public function getTimestamp(): ?float{
		return $this->timestamp;
	}

	public function setTimestamp(?float $timestamp): Message{
		$this->timestamp = $timestamp;
		return $this;
	}

	public function isEveryoneMentioned(): bool{
		return $this->everyone_mentioned;
	}

	public function setEveryoneMentioned(bool $everyone_mentioned): Message{
		$this->everyone_mentioned = $everyone_mentioned;
		return $this;
	}

	/**
	 * @return string[]
	 */
	public function getUsersMentioned(): array{
		return $this->users_mentioned;
	}

	/**
	 * @param string[] $users_mentioned
	 * @return Message
	 */
	public function setUsersMentioned(array $users_mentioned): Message{
		$this->users_mentioned = $users_mentioned;
		return $this;
	}

	/**
	 * @return string[]
	 */
	public function getRolesMentioned(): array{
		return $this->roles_mentioned;
	}

	/**
	 * @param string[] $roles_mentioned
	 * @return Message
	 */
	public function setRolesMentioned(array $roles_mentioned): Message{
		$this->roles_mentioned = $roles_mentioned;
		return $this;
	}

	/**
	 * @return string[]
	 */
	public function getChannelsMentioned(): array{
		return $this->channels_mentioned;
	}

	/**
	 * @param string[] $channels_mentioned
	 * @return Message
	 */
	public function setChannelsMentioned(array $channels_mentioned): Message{
		$this->channels_mentioned = $channels_mentioned;
		return $this;
	}

	//----- Serialization -----//

	public function serialize(): ?string{
		return serialize([
			$this->id,
			$this->type,
			$this->content,
			$this->author_id,
			$this->channel_id,
			$this->server_id,
			$this->timestamp,
			$this->everyone_mentioned,
			$this->users_mentioned,
			$this->roles_mentioned,
			$this->channels_mentioned
		]);
	}

	public function unserialize($serialized): void{
		[
			$this->id,
			$this->type,
			$this->content,
			$this->author_id,
			$this->channel_id,
			$this->server_id,
			$this->timestamp,
			$this->everyone_mentioned,
			$this->users_mentioned,
			$this->roles_mentioned,
			$this->channels_mentioned
		] = unserialize($serialized);
	}
}