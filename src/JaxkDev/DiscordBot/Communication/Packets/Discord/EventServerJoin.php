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

use JaxkDev\DiscordBot\Models\Channels\ServerChannel;
use JaxkDev\DiscordBot\Models\Member;
use JaxkDev\DiscordBot\Models\Role;
use JaxkDev\DiscordBot\Models\Server;
use JaxkDev\DiscordBot\Communication\Packets\Packet;

class EventServerJoin extends Packet{

	/** @var Server */
	private $server;

	/** @var ServerChannel[] */
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
	 * @return ServerChannel[]
	 */
	public function getChannels(): array{
		return $this->channels;
	}

	/**
	 * @param ServerChannel[] $channels
	 */
	public function setChannels(array $channels): void{
		$this->channels = $channels;
	}

	/**
	 * @return Role[]
	 */
	public function getRoles(): array{
		return $this->roles;
	}

	/**
	 * @param Role[] $roles
	 */
	public function setRoles(array $roles): void{
		$this->roles = $roles;
	}

	/**
	 * @return Member[]
	 */
	public function getMembers(): array{
		return $this->members;
	}

	/**
	 * @param Member[] $members
	 */
	public function setMembers(array $members): void{
		$this->members = $members;
	}

	public function serialize(): ?string{
		return serialize([$this->UID, $this->server, $this->roles, $this->channels, $this->members]);
	}

	public function unserialize($data): void{
		[$this->UID, $this->server, $this->roles, $this->channels, $this->members] = unserialize($data);
	}
}