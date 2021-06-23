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

namespace JaxkDev\DiscordBot\Models;

use JaxkDev\DiscordBot\Models\Embed\Embed;

class Message implements \Serializable{

	const TYPE_NORMAL = 0;

	/** @var ?string Null when sending message. */
	private $id;

	/** @var int */
	private $type = self::TYPE_NORMAL; //Not supporting others right now.

	/** @var string (<=2000) */
	private $content;

	/** @var Embed[] Max 10 in webhook message, one in normal message. */
	private $embeds = [];

	/** @var ?string MemberID Null when sending or receiving webhook messages.*/
	private $author_id;

	/** @var string */
	private $channel_id;

	/** @var ?string Null if DM Channel. */
	private $server_id;  //This is needed for faster handling discord side.

	/** @var ?float Null when sending message. */
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

	public function setId(string $id): void{
		$this->id = $id;
	}

	public function getType(): int{
		return $this->type;
	}

	/**
	 * @see Message::TYPE_NORMAL
	 * @param int $type Message::TYPE_X Constant.
	 */
	public function setType(int $type): void{
		if($type < self::TYPE_NORMAL or $type > self::TYPE_NORMAL){
			throw new \AssertionError("Invalid type '{$type}'");
		}
		$this->type = $type;
	}

	public function getContent(): string{
		return $this->content;
	}

	public function setContent(string $content): void{
		if(strlen($content) > 2000){
			throw new \AssertionError("Message content cannot exceed 2000 characters.");
		}
		$this->content = $content;
	}

	/** @return Embed[] */
	public function getEmbeds(): array{
		return $this->embeds;
	}

	/** @param Embed[] $embeds */
	public function setEmbeds(array $embeds): void{
		if($this->type === self::TYPE_NORMAL and sizeof($embeds) > 1){
			throw new \AssertionError("A 'normal' message can only have one embed.");
		}
		$this->embeds = $embeds;
	}

	public function getAuthorId(): ?string{
		return $this->author_id;
	}

	public function setAuthorId(?string $author_id): void{
		$this->author_id = $author_id;
	}

	public function getChannelId(): string{
		return $this->channel_id;
	}

	public function setChannelId(string $channel_id): void{
		$this->channel_id = $channel_id;
	}

	public function getServerId(): ?string{
		return $this->server_id;
	}

	public function setServerId(?string $server_id): void{
		$this->server_id = $server_id;
	}

	public function getTimestamp(): ?float{
		return $this->timestamp;
	}

	public function setTimestamp(?float $timestamp): void{
		$this->timestamp = $timestamp;
	}

	public function isEveryoneMentioned(): bool{
		return $this->everyone_mentioned;
	}

	public function setEveryoneMentioned(bool $everyone_mentioned): void{
		$this->everyone_mentioned = $everyone_mentioned;
	}

	/**
	 * @return string[]
	 */
	public function getUsersMentioned(): array{
		return $this->users_mentioned;
	}

	/**
	 * @param string[] $users_mentioned
	 */
	public function setUsersMentioned(array $users_mentioned): void{
		$this->users_mentioned = $users_mentioned;
	}

	/**
	 * @return string[]
	 */
	public function getRolesMentioned(): array{
		return $this->roles_mentioned;
	}

	/**
	 * @param string[] $roles_mentioned
	 */
	public function setRolesMentioned(array $roles_mentioned): void{
		$this->roles_mentioned = $roles_mentioned;
	}

	/**
	 * @return string[]
	 */
	public function getChannelsMentioned(): array{
		return $this->channels_mentioned;
	}

	/**
	 * @param string[] $channels_mentioned
	 */
	public function setChannelsMentioned(array $channels_mentioned): void{
		$this->channels_mentioned = $channels_mentioned;
	}

	//----- Serialization -----//

	public function serialize(): ?string{
		return serialize([
			$this->id,
			$this->type,
			$this->content,
			$this->embeds,
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

	public function unserialize($data): void{
		[
			$this->id,
			$this->type,
			$this->content,
			$this->embed,
			$this->author_id,
			$this->channel_id,
			$this->server_id,
			$this->timestamp,
			$this->everyone_mentioned,
			$this->users_mentioned,
			$this->roles_mentioned,
			$this->channels_mentioned
		] = unserialize($data);
	}
}