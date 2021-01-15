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

namespace JaxkDev\DiscordBot\Communication\Packets;

use JaxkDev\DiscordBot\Communication\Models\Channel;
use JaxkDev\DiscordBot\Communication\Models\Member;
use JaxkDev\DiscordBot\Communication\Models\Role;
use JaxkDev\DiscordBot\Communication\Models\Server;
use JaxkDev\DiscordBot\Communication\Models\User;

class DiscordEventAllData extends Packet{

	/** @var Server[] */
	private $servers = [];

	/** @var Channel[] */
	private $channels = [];

	/** @var Role[] */
	private $roles = [];

	/** @var Member[] */
	private $members = [];

	/** @var User[] */
	private $users = [];

	/** @var null|User */
	private $botUser = null;

	/** @var int */
	private $timestamp;

	public function addServer(Server $server): DiscordEventAllData{
		$this->servers[] = $server;
		return $this;
	}

	/**
	 * @return Server[]
	 */
	public function getServers(): array{
		return $this->servers;
	}

	public function addChannel(Channel $channel): DiscordEventAllData{
		$this->channels[] = $channel;
		return $this;
	}

	/**
	 * @return Channel[]
	 */
	public function getChannels(): array{
		return $this->channels;
	}

	public function addRole(Role $role): DiscordEventAllData{
		$this->roles[] = $role;
		return $this;
	}

	/**
	 * @return Role[]
	 */
	public function getRoles(): array{
		return $this->roles;
	}

	public function addMember(Member $member): DiscordEventAllData{
		$this->members[] = $member;
		return $this;
	}

	/**
	 * @return Member[]
	 */
	public function getMembers(): array{
		return $this->members;
	}

	public function addUser(User $user): DiscordEventAllData{
		$this->users[] = $user;
		return $this;
	}

	/**
	 * @return User[]
	 */
	public function getUsers(): array{
		return $this->users;
	}

	public function setBotUser(User $bot): void{
		$this->botUser = $bot;
	}

	public function getBotUser(): ?User{
		return $this->botUser;
	}

	public function setTimestamp(int $timestamp): DiscordEventAllData{
		$this->timestamp = $timestamp;
		return $this;
	}

	public function getTimestamp(): int{
		return $this->timestamp;
	}

	public function getSize(): int{
		return sizeof($this->servers)+sizeof($this->channels)
			+sizeof($this->roles)+sizeof($this->members)+sizeof($this->users);
	}

	public function serialize(): ?string{
		return serialize([
			$this->UID,
			$this->servers,
			$this->channels,
			$this->roles,
			$this->members,
			$this->users,
			$this->botUser,
			$this->timestamp
		]);
	}

	public function unserialize($serialized): void{
		[
			$this->UID,
			$this->servers,
			$this->channels,
			$this->roles,
			$this->members,
			$this->users,
			$this->botUser,
			$this->timestamp
		] = unserialize($serialized);
	}
}