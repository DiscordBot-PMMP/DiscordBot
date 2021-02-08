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

namespace JaxkDev\DiscordBot\Communication\Packets\Discord;

use JaxkDev\DiscordBot\Communication\Models\Channel;
use JaxkDev\DiscordBot\Communication\Models\Member;
use JaxkDev\DiscordBot\Communication\Models\Role;
use JaxkDev\DiscordBot\Communication\Models\Server;
use JaxkDev\DiscordBot\Communication\Packets\Packet;

class DiscordEventServerJoin extends Packet{

	/** @var Server */
	private $server;

	/** @var Channel[] */
	private $channels;

	/** @var Member[] */
	private $members;

	/** @var Role[] */
	private $roles;

	public function getServer(): Server{
		return $this->server;
	}

	public function setServer(Server $server): void{
		$this->server = $server;
	}

	/**
	 * @param Channel[] $channels
	 */
	public function setChannels(array $channels): void{
		$this->channels = $channels;
	}

	/**
	 * @return Channel[]
	 */
	public function getChannels(): array{
		return $this->channels;
	}

	/**
	 * @param Role[] $roles
	 */
	public function setRoles(array $roles): void{
		$this->roles = $roles;
	}

	/**
	 * @return Role[]
	 */
	public function getRoles(): array{
		return $this->roles;
	}

	/**
	 * @param Member[] $members
	 */
	public function setMembers(array $members): void{
		$this->members = $members;
	}

	/**
	 * @return Member[]
	 */
	public function getMembers(): array{
		return $this->members;
	}

	public function serialize(): ?string{
		return serialize([$this->UID, $this->server, $this->roles, $this->channels, $this->members]);
	}

	public function unserialize($serialized): void{
		[$this->UID, $this->server, $this->roles, $this->channels, $this->members] = unserialize($serialized);
	}
}