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

namespace JaxkDev\DiscordBot\Communication\Packets;

use JaxkDev\DiscordBot\Communication\Models\Channel;
use JaxkDev\DiscordBot\Communication\Models\Member;
use JaxkDev\DiscordBot\Communication\Models\Role;
use JaxkDev\DiscordBot\Communication\Models\Server;
use JaxkDev\DiscordBot\Communication\Models\User;

class DiscordAllData extends Packet{

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

	public function serialize(): ?string{
		return serialize([
			$this->UID,
			$this->servers,
			$this->channels,
			$this->roles,
			$this->members,
			$this->users
		]);
	}

	public function unserialize($serialized): void{
		[
			$this->UID,
			$this->servers,
			$this->channels,
			$this->roles,
			$this->members,
			$this->users
		] = unserialize($serialized);
	}
}