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

use JaxkDev\DiscordBot\Utils;

class Activity implements \Serializable {

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


	/** @var string */
	private $message;

	/** @var int */
	private $type;

	/** @var string */
	private $status;

	public function getMessage(): string{
		return $this->message;
	}

	public function setMessage(string $message): Activity{
		$this->message = $message;
		return $this;
	}

	public function getType(): int{
		return $this->type;
	}

	public function setType(int $type): Activity{
		Utils::assert($type >= self::TYPE_PLAYING and $type <= self::TYPE_COMPETING);
		$this->type = $type;
		return $this;
	}

	public function getStatus(): string{
		return $this->status;
	}

	public function setStatus(string $status): Activity{
		$this->status = $status;
		return $this;
	}

	//----- Serialization -----//

	public function serialize(): ?string{
		return serialize([
			$this->message,
			$this->type
		]);
	}

	public function unserialize($serialized): void{
		[
			$this->message,
			$this->type
		] = unserialize($serialized);
	}
}