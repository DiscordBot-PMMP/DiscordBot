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

namespace JaxkDev\DiscordBot;

use AssertionError;
use Throwable;

abstract class Utils {

	/**
	 * @param bool $assertion
	 * @param Throwable|string $message
	 * @throws AssertionError
	 */
	static function assert(bool $assertion, $message = "Assertion failed."): void{
		if(!$assertion){
			throw new AssertionError(($message instanceof Throwable ? "" : $message), 0,
				$message instanceof Throwable ? $message : null);
		}
	}
}