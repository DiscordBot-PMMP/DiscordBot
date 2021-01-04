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
	const EVENT_VERSION = 2;
	const VERSION = 2;

	// Map all versions to a static function.
	private const PATCH_MAP = [
		1 => 'patch_1'
	];

	private const EVENT_PATCH_MAP = [
		1 => 'event_patch_1'
	];

	static function update(array &$config): void{
		for($i = (int)$config['version']; $i < self::VERSION; $i += 1){
			$f = self::PATCH_MAP[$i];
			$config = forward_static_call([self::class, $f], $config);
		}
	}

	static function update_event(array &$config): void{
		for($i = (int)$config['version']; $i < self::EVENT_VERSION; $i += 1){
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

	static private function event_patch_1(array $data): array{
		//Channel ID's no longer a string or have serverID prefixed.
		//TODO
		return $data;
	}


	/**
	 * @description Verifies the config's keys and values, returning any keys and a relevant message.
	 * @param array $config
	 * @return array
	 */
	static function verify(array $config): array{
		$result = [];

		if(!array_key_exists("version", $config)){
			$result["version"] = "No 'version' key found.";
		} else {
			if(!is_int($config['version']) or $config['version'] <= 0 or $config['version'] > self::VERSION){
				$result["version"] = "Invalid 'version' ({$config["version"]}), you were warned not to touch it...";
			}
		}

		if(!array_key_exists("discord", $config)){
			$result["discord"] = "No 'discord' key found.";
		} else {
			if(!array_key_exists("token", $config['discord'])){
				$result["discord.token"] = "No 'discord.token' key found.";
			} else {
				if(!is_string($config['discord']['token']) or strlen($config['discord']['token']) < 59){
					$result["discord.token"] = "Invalid 'discord.token' ({$config["discord"]["token"]}), did you follow the wiki ?";
				}
			}
		}

		if(!array_key_exists("logging", $config)){
			$result["logging"] = "No 'logging' key found.";
		} else {
			if(!array_key_exists("debug", $config["logging"])){
				$result["logging.debug"] = "No 'logging.debug' value found.";
			} else {
				if(!is_bool($config["logging"]["debug"])){
					$result["logging.debug"] = "Invalid 'logging.debug' ({$config["logging"]["debug"]}), should be true or false.";
				}
			}

			if(!array_key_exists("maxFiles", $config["logging"])){
				$result["logging.maxFiles"] = "No 'logging.maxFiles' key found.";
			} else {
				if(!is_int($config["logging"]["maxFiles"]) or $config["logging"]["maxFiles"] <= 0){
					$result["logging.maxFiles"] = "Invalid 'logging.maxFiles' ({$config["logging"]["maxFiles"]}), should be an int > 0.";
				}
			}

			if(!array_key_exists("directory", $config["logging"])){
				$result["logging.directory"] = "No 'logging.directory' key found.";
			} else {
				if(!is_string($config["logging"]["directory"]) or strlen($config["logging"]["directory"]) === 0){
					$result["logging.directory"] = "Invalid 'logging.directory' ({$config["logging"]["directory"]}).";
				}
			}
		}

		if(!array_key_exists("security", $config)){
			$result["security"] = "No security key found.";
		} else {
			if(!array_key_exists("disable_ssl", $config["security"])){
				$result["security.disable_ssl"] = "No 'security.disable_ssl' key found.";
			} else {
				if(!is_bool($config["security"]["disable_ssl"])){
					$result["security.disable_ssl"] = "Invalid 'security.disable_ssl' ({$config["security"]["disable_ssl"]}), should be true or false.";
				}
			}
		}

		return $result;
	}
}