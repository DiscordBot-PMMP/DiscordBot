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
		TYPE_HEARTBEAT 		= 0,	// P|B
		TYPE_BOT_READY 		= 1,	//  |B
		TYPE_STATS_REQUEST 	= 2,	// P|B
		TYPE_STATS_RESPONSE = 3;	// P|B
}