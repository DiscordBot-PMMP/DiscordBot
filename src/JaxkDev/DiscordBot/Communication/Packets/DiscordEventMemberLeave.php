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

class DiscordEventMemberLeave extends Packet{

	/** @var string */
	private $member_id;

	public function getMemberID(): string{
		return $this->member_id;
	}

	public function setMemberID(string $id): void{
		$this->member_id = $id;
	}

	public function serialize(): ?string{
		return serialize([$this->UID, $this->member_id]);
	}

	public function unserialize($serialized): void{
		[$this->UID, $this->member_id] = unserialize($serialized);
	}
}