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

use JaxkDev\DiscordBot\Communication\Models\Member;

class DiscordEventMemberUpdate extends Packet{

	/** @var Member */
	private $member;

	public function getMember(): Member{
		return $this->member;
	}

	public function setMember(Member $member): void{
		$this->member = $member;
	}

	public function serialize(): ?string{
		return serialize([$this->UID, $this->member]);
	}

	public function unserialize($serialized): void{
		[$this->UID, $this->member] = unserialize($serialized);
	}
}