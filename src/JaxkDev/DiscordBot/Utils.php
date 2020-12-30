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

	/**
	 * @param int|string $id
	 * @return int
	 */
	static function convertIdToTime($id): int{
		return (int)(((string)$id / 4194304) + 1420070400000);
	}

	/**
	 * Used to distinguish which thread you are in, at runtime.
	 * @var bool
	 */
	public static $BOT_THREAD = false;
}