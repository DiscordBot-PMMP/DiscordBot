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

use JaxkDev\DiscordBot\Communication\Models\Activity;

class PluginRequestUpdateActivity extends Packet{

	/** @var Activity */
	private $activity;

	public function getActivity(): Activity{
		return $this->activity;
	}

	public function setActivity(Activity $activity): void{
		$this->activity = $activity;
	}

	public function serialize(): ?string{
		return serialize([$this->UID, $this->activity]);
	}

	public function unserialize($serialized): void{
		[$this->UID, $this->activity] = unserialize($serialized);
	}
}