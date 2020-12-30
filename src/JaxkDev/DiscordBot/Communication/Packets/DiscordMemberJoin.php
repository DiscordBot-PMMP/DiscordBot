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

use JaxkDev\DiscordBot\Communication\Models\Member;
use JaxkDev\DiscordBot\Communication\Models\User;

class DiscordMemberJoin extends Packet{

	/** @var Member */
	private $member;

	/** @var User */
	private $user;

	public function getMember(): Member{
		return $this->member;
	}

	public function setMember(Member $member): void{
		$this->member = $member;
	}

	public function getUser(): User{
		return $this->user;
	}

	public function setUser(User $user): DiscordMemberJoin{
		$this->user = $user;
		return $this;
	}

	public function serialize(): ?string{
		return serialize([$this->UID, $this->member, $this->user]);
	}

	public function unserialize($serialized): void{
		[$this->UID, $this->member, $this->user] = unserialize($serialized);
	}
}