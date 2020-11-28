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

	const
		PPT = 20, 					// How much data should be processed per tick ?
		HEARTBEAT_ALLOWANCE = 2.5;	// How long until declared dead. (seconds)

	const 							// Emitted by, Plugin|Bot
		ID_HEARTBEAT 		= 0,	// P|B
		ID_UPDATE_ACTIVITY	= 1,	// P|
		ID_SEND_MESSAGE		= 2;	// P|

	const
		THREAD_STATUS_STARTING = 0,
		THREAD_STATUS_STARTED = 1,
		THREAD_STATUS_READY = 2,
		THREAD_STATUS_CLOSED = 9;

	const
		ACTIVITY_TYPE_PLAYING = 0,
		ACTIVITY_TYPE_STREAMING = 1,
		ACTIVITY_TYPE_LISTENING = 2;
}