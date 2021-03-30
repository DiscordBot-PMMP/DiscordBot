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

class Activity implements \Serializable{

	const
		TYPE_PLAYING = 0,
		TYPE_STREAMING = 1,
		TYPE_LISTENING = 2,
		TYPE_WATCHING = 3,
		TYPE_CUSTOM = 4,
		TYPE_COMPETING = 5,

		STATUS_ONLINE = "online",
		STATUS_IDLE = "idle",
		STATUS_DND = "dnd",
		STATUS_INVISIBLE = "invisible",
		STATUS_OFFLINE = "offline";


	/** @var ?string */
	private $message;

	/** @var ?int */
	private $type;

	/** @var string */
	private $status;

	public function getMessage(): ?string{
		return $this->message;
	}

	public function setMessage(?string $message): void{
		$this->message = $message;
	}

	public function getType(): ?int{
		return $this->type;
	}

	public function setType(?int $type): void{
		if($type !== null and ($type < self::TYPE_PLAYING or $type > self::TYPE_COMPETING)){
			throw new \AssertionError("Invalid type '{$type}'");
		}
		$this->type = $type;
	}

	public function getStatus(): string{
		return $this->status;
	}

	public function setStatus(string $status): void{
		$this->status = $status;
	}

	//----- Serialization -----//

	public function serialize(): ?string{
		return serialize([
			$this->message,
			$this->type,
			$this->status
		]);
	}

	public function unserialize($data): void{
		[
			$this->message,
			$this->type,
			$this->status
		] = unserialize($data);
	}
}