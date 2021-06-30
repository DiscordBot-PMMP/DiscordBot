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

class User implements \Serializable{

	//https://github.com/Delitefully/DiscordLists/blob/master/flags.md
	const FLAGS = [
		"STAFF" => 1,
		"PARTNER" => 2,
		"HYPESQUAD" => 4,
		"BUG_HUNTER_LEVEL_1" => 8,
		"PREMIUM_PROMO_DISMISSED" => 32,
		"HYPESQUAD_ONLINE_HOUSE_1" => 64, //Bravery
		"HYPESQUAD_ONLINE_HOUSE_2" => 128, //Brilliance
		"HYPESQUAD_ONLINE_HOUSE_3" => 256, //Balance
		"PREMIUM_EARLY_SUPPORTER" => 512,
		"TEAM_USER" => 1024,
		"SYSTEM" => 4096,
		"BUG_HUNTER_LEVEL_2" => 16384,
		"UNDERAGE_DELETED" => 32768,
		"VERIFIED_BOT" => 65536,
		"VERIFIED_DEVELOPER" => 131072,
		"CERTIFIED_MODERATOR" => 262144
	];

	/** @var string */
	private $id;

	/** @var string */
	private $username;

	/** @var string 0000 when user is webhook/system etc. */
	private $discriminator;

	/** @var string */
	private $avatar_url;

	/** @var int */
	private $creation_timestamp;

	/** @var bool */
	private $bot;

	/** @var int */
	private $flags_bitwise;

	/** @var Array<string, bool> */
	private $flags = [];

	public function __construct(string $id, string $username, string $discriminator, string $avatar_url, int $creation_timestamp,
	bool $bot = false, int $flags_bitwise = 0, bool $recalculate_flags = true){
		$this->id = $id;
		$this->username = $username;
		$this->discriminator = $discriminator;
		$this->avatar_url = $avatar_url;
		$this->creation_timestamp = $creation_timestamp;
		$this->bot = $bot;
		$this->flags_bitwise = $flags_bitwise;
		if($recalculate_flags){
			$this->updateFlags();
		}
	}

	public function getId(): string{
		return $this->id;
	}

	public function setId(string $id): void{
		$this->id = $id;
	}

	public function getUsername(): string{
		return $this->username;
	}

	public function setUsername(string $username): void{
		$this->username = $username;
	}

	public function getDiscriminator(): string{
		return $this->discriminator;
	}

	public function setDiscriminator(string $discriminator): void{
		$this->discriminator = $discriminator;
	}

	public function getAvatarUrl(): string{
		return $this->avatar_url;
	}

	public function setAvatarUrl(string $avatar_url): void{
		$this->avatar_url = $avatar_url;
	}

	public function getCreationTimestamp(): int{
		return $this->creation_timestamp;
	}

	public function setCreationTimestamp(int $creation_timestamp): void{
		$this->creation_timestamp = $creation_timestamp;
	}

	public function isBot(): bool{
		return $this->bot;
	}

	public function setBot(bool $bot): void{
		$this->bot = $bot;
	}

	public function getFlagsBitwise(): int{
		return $this->flags_bitwise;
	}

	public function setFlagsBitwise(int $flags_bitwise, bool $recalculate = true): void{
		$this->flags_bitwise = $flags_bitwise;
		if($recalculate) $this->updateFlags();
	}

	/**
	 * Returns all the flags possible and the current state, or an empty array if not initialised.
	 * @return Array<string, bool>
	 */
	public function getFlags(): array{
		return $this->flags;
	}

	public function getFlag(string $flag): ?bool{
		return $this->flags[$flag] ?? null;
	}

	public function setFlag(string $flag, bool $state = true): void{
		if(!in_array($flag, array_keys(self::FLAGS))){
			throw new \AssertionError("Invalid flag '{$flag}' for a 'user'");
		}

		if($this->flags[$flag] === $state) return;
		$this->flags[$flag] = $state;
		$this->flags_bitwise ^= self::FLAGS[$flag];
		return;
	}

	/**
	 * @internal Using current flags_bitwise update flags to correct state.
	 */
	private function updateFlags(): void{
		$this->flags = [];
		foreach(self::FLAGS as $name => $v){
			$this->flags[$name] = (($this->flags_bitwise & $v) !== 0);
		}
	}

	//----- Serialization -----//

	public function serialize(): ?string{
		return serialize([
			$this->id,
			$this->username,
			$this->discriminator,
			$this->avatar_url,
			$this->creation_timestamp,
			$this->bot,
			$this->flags_bitwise
		]);
	}

	public function unserialize($data): void{
		[
			$this->id,
			$this->username,
			$this->discriminator,
			$this->avatar_url,
			$this->creation_timestamp,
			$this->bot,
			$this->flags_bitwise
		] = unserialize($data);
		$this->updateFlags();
	}
}