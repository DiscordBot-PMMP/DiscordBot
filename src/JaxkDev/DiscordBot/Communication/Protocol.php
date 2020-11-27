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

namespace JaxkDev\DiscordBot\Communication;

abstract class Protocol {

	const HEARTBEAT_ALLOWANCE = 2.5;// How long until declared dead. (seconds)

	const 							// Emitted by, Plugin|Bot
		ID_HEARTBEAT 		= 0,	// P|B
		ID_UPDATE_ACTIVITY	= 1;	// P|

	const
		ACTIVITY_TYPE_PLAYING = 0,
		ACTIVITY_TYPE_STREAMING = 1,
		ACTIVITY_TYPE_LISTENING = 2;
}