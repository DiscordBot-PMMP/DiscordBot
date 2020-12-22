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

class Message implements \Serializable {

	const TYPE_NORMAL = 0;

	/** @var int */
	private $id;

	/** @var int */
	private $type = self::TYPE_NORMAL; //Not supporting others right now.

	/** @var string (<=2000) */
	private $content;

	/** @var int[] */
	private $author_id;

	/** @var int[] */
	private $channel_id;

	/** @var float */
	private $timestamp;

	/** @var bool */
	private $everyone_mentioned;

	/** @var int[] */
	private $users_mentioned;

	/** @var int[] */
	private $roles_mentioned;

	/** @var int[] */
	private $channels_mentioned;

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

	public function getId(): int{
		return $this->id;
	}

	public function setId(int $id): Message{
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

	/**
	 * @return int[]
	 */
	public function getAuthorId(): array{
		return $this->author_id;
	}

	/**
	 * @param int[] $author_id
	 * @return Message
	 */
	public function setAuthorId(array $author_id): Message{
		$this->author_id = $author_id;
		return $this;
	}

	/**
	 * @return int[]
	 */
	public function getChannelId(): array{
		return $this->channel_id;
	}

	/**
	 * @param int[] $channel_id
	 * @return Message
	 */
	public function setChannelId(array $channel_id): Message{
		$this->channel_id = $channel_id;
		return $this;
	}

	public function getTimestamp(): float{
		return $this->timestamp;
	}

	public function setTimestamp(float $timestamp): Message{
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
	 * @return int[]
	 */
	public function getUsersMentioned(): array{
		return $this->users_mentioned;
	}

	/**
	 * @param int[] $users_mentioned
	 * @return Message
	 */
	public function setUsersMentioned(array $users_mentioned): Message{
		$this->users_mentioned = $users_mentioned;
		return $this;
	}

	/**
	 * @return int[]
	 */
	public function getRolesMentioned(): array{
		return $this->roles_mentioned;
	}

	/**
	 * @param int[] $roles_mentioned
	 * @return Message
	 */
	public function setRolesMentioned(array $roles_mentioned): Message{
		$this->roles_mentioned = $roles_mentioned;
		return $this;
	}

	/**
	 * @return int[]
	 */
	public function getChannelsMentioned(): array{
		return $this->channels_mentioned;
	}

	/**
	 * @param int[] $channels_mentioned
	 * @return Message
	 */
	public function setChannelsMentioned(array $channels_mentioned): Message{
		$this->channels_mentioned = $channels_mentioned;
		return $this;
	}

}