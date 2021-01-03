<?php /** @noinspection PhpUnusedPrivateMethodInspection */

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

namespace JaxkDev\DiscordBot\Plugin;

abstract class ConfigUtils{
	const EVENT_VERSION = 1;
	const VERSION = 2;

	// Map all versions to a static function.
	private const PATCH_MAP = [
		1 => 'patch_1'
	];
	private const EVENT_PATCH_MAP = [];

	static function update(array &$config): void{
		for($i = $config['version']; $i < self::VERSION; $i += 1){
			$f = self::PATCH_MAP[$i];
			$config = forward_static_call([self::class, $f], $config);
		}
	}

	static function update_event(array &$config): void{
		for($i = $config['version']; $i < self::EVENT_VERSION; $i += 1){
			$f = self::EVENT_PATCH_MAP[$i];
			$config = forward_static_call([self::class, $f], $config);
		}
	}

	static private function patch_1(array $config): array{
		$config['version'] = 2;
		$config['security'] = [
			'disable_ssl' => false
		];

		return $config;
	}


	/**
	 * @description Verifies the config's keys and values, returning any keys and a relevant message.
	 * @param array $config
	 * @return array
	 *
	static function verify(array $config): array{
		return [];
	}*/
}