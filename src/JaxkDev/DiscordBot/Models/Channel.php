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

//TODO, abstract class and make parents, TextChannel, VoiceChannel, CategoryChannel, DmChannel...
class Channel implements \Serializable{

	/** @var string */
	private $id;

	/** @var string */
	private $name;

	/** @var string|null */
	private $category;

	/** @var string|null AKA Topic. */
	private $description;

	/** @var string */
	private $server_id;

	public function getId(): string{
		return $this->id;
	}

	public function setId(string $id): void{
		$this->id = $id;
	}

	public function getName(): string{
		return $this->name;
	}

	public function setName(string $name): void{
		$this->name = $name;
	}

	public function getCategory(): ?string{
		return $this->category;
	}

	public function setCategory(?string $category): void{
		$this->category = $category;
	}

	public function getDescription(): ?string{
		return $this->description;
	}

	public function setDescription(?string $description): void{
		$this->description = $description;
	}

	public function getServerId(): string{
		return $this->server_id;
	}

	public function setServerId(string $server_id): void{
		$this->server_id = $server_id;
	}

	//----- Serialization -----//

	public function serialize(): ?string{
		return serialize([
			$this->id,
			$this->name,
			$this->category,
			$this->description,
			$this->server_id
		]);
	}

	public function unserialize($serialized): void{
		[
			$this->id,
			$this->name,
			$this->category,
			$this->description,
			$this->server_id
		] = unserialize($serialized);
	}
}