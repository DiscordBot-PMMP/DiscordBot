<?php /** @noinspection PhpUnusedPrivateMethodInspection */

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

namespace JaxkDev\DiscordBot\Plugin;

use pocketmine\utils\MainLogger;

//Probably best if you dont look at this.
abstract class ConfigUtils{

	const EVENT_VERSION = 2;
	const VERSION = 2;

	// Map all versions to a static function.
	private const PATCH_MAP = [
		1 => "patch_1"
	];

	private const EVENT_PATCH_MAP = [
		1 => "event_patch_1"
	];

	static public function update(array &$config): void{
		for($i = (int)$config["version"]; $i < self::VERSION; $i += 1){
			$f = self::PATCH_MAP[$i];
			$config = forward_static_call([self::class, $f], $config);
		}
	}

	static function update_event(array &$config): void{
		for($i = (int)$config["version"]; $i < self::EVENT_VERSION; $i += 1){
			$f = self::EVENT_PATCH_MAP[$i];
			$config = forward_static_call([self::class, $f], $config);
		}
	}

	static private function patch_1(array $config): array{
		$config["version"] = 2;
		if(!isset($config["discord"])){
			$config["discord"] = [
				"token" => "Long token here",
				"usePluginCacert" => true
			];
		}else{
			$config["discord"]["usePluginCacert"] = true;
		}
		return $config;
	}

	static private function event_patch_1(array $data): array{
		$changeIds = function(array &$data, string $event, string $discord = "toDiscord", string $key = "channels"): void{
			$ids = $data[$event][$discord][$key];
			$data[$event][$discord][$key] = [];
			foreach($ids as $id){
				$d = explode(".", $id);
				if(sizeof($d) < 2){
					//Corrupt, reset.
					MainLogger::getLogger()->warning("[DiscordBot] > event `{$event}.{$discord}.{$key}` ID `{$id}` is " .
						"corrupt and could not be updated, ID has been removed.");
					return;
				}
				$data[$event][$discord][$key][] = $d[1];
			}
		};

		$data["version"] = 2;

		//channels[] no longer has server ID prefixed.
		$changeIds($data, "message");
		$changeIds($data, "message", "fromDiscord");
		$changeIds($data, "command");
		$changeIds($data, "member_join");
		$changeIds($data, "member_leave");

		//Added servers option to join/leave event.
		$data["member_leave"]["fromDiscord"]["servers"] = [];
		$data["member_join"]["fromDiscord"]["servers"] = [];

		//Added member transfer event.
		$data["member_transfer"] = [
			"toDiscord" => [
				"channels" => [],
				"format" => "[{TIME}] **{USERNAME}** Has been transferred to {ADDRESS}:{PORT}."
			]
		];

		return $data;
	}


	/**
	 * Verifies the config's keys and values, returning any keys and a relevant message.
	 * @param array $config
	 * @return string[]
	 */
	static public function verify(array $config): array{
		$result = [];

		if(!array_key_exists("version", $config) or $config["version"] === null){
			$result[] = "No 'version' key found.";
		}else{
			if(!is_int($config["version"]) or $config["version"] <= 0 or $config["version"] > self::VERSION){
				$result[] = "Invalid 'version' ({$config["version"]}), you were warned not to touch it...";
			}
		}

		if(!array_key_exists("discord", $config) or $config["discord"] === null){
			$result[] = "No 'discord' key found.";
		}else{
			if(!array_key_exists("token", $config["discord"]) or $config["discord"]["token"] === null){
				$result[] = "No 'discord.token' key found.";
			}else{
				if(!is_string($config["discord"]["token"]) or strlen($config["discord"]["token"]) < 59){
					$result[] = "Invalid 'discord.token' ({$config["discord"]["token"]}), did you follow the wiki ?";
				}
			}
			if(!array_key_exists("usePluginCacert", $config["discord"]) or $config["discord"]["usePluginCacert"] === null){
				$result[] = "No 'discord.usePluginCacert' key found.";
			}else{
				if(!is_bool($config["discord"]["usePluginCacert"])){
					$result[] = "Invalid 'discord.usePluginCacert' ({$config["discord"]["usePluginCacert"]}), must be true or false";
				}
			}
		}

		if(!array_key_exists("logging", $config) or $config["logging"] === null){
			$result[] = "No 'logging' key found.";
		}else{
			if(!array_key_exists("debug", $config["logging"])  or $config["logging"]["debug"] === null){
				$result[] = "No 'logging.debug' value found.";
			}else{
				if(!is_bool($config["logging"]["debug"])){
					$result[] = "Invalid 'logging.debug' ({$config["logging"]["debug"]}), should be true or false.";
				}
			}

			if(!array_key_exists("maxFiles", $config["logging"]) or $config["logging"]["maxFiles"] === null){
				$result[] = "No 'logging.maxFiles' key found.";
			}else{
				if(!is_int($config["logging"]["maxFiles"]) or $config["logging"]["maxFiles"] <= 0){
					$result[] = "Invalid 'logging.maxFiles' ({$config["logging"]["maxFiles"]}), should be an int > 0.";
				}
			}

			if(!array_key_exists("directory", $config["logging"]) or $config["logging"]["directory"] === null){
				$result[] = "No 'logging.directory' key found.";
			}else{
				if(!is_string($config["logging"]["directory"]) or strlen($config["logging"]["directory"]) === 0){
					$result[] = "Invalid 'logging.directory' ({$config["logging"]["directory"]}).";
				}
			}
		}

		return $result;
	}

	/**
	 * Verifies the event config keys and values, returning any keys and a relevant message if invalid.
	 * @param array $config
	 * @return string[]
	 */
	static public function verify_event(array $config): array{
		$result = [];
		$checkGenerics = function(array $data, string $event, array $keys = ["toDiscord", "fromDiscord"],
								  array $types = ["channels", "channels"]): array{
			$r = [];
			if(!array_key_exists($event, $data) or $data[$event] === null){
				$r[] = "No '{$event}' key found.";
				return $r;
			}
			$data = $data[$event];
			for($i = 0; $i < sizeof($keys); $i++){
				[$key, $type] = [$keys[$i], $types[$i]];
				if(!array_key_exists($key, $data) or $data[$key] === null){
					$r[] = "No '{$event}.{$key}' key found.";
				}else{
					if(!array_key_exists("format", $data[$key]) or $data[$key]["format"] === null){
						$r[] = "No '{$event}.{$key}.format' key found.";
					}else{
						if(!is_string($data[$key]["format"])){
							$r[] = "Invalid '{$event}.{$key}.format', value should be a string.";
						}
					}
					if(!array_key_exists($type, $data[$key]) or $data[$key][$type] === null){
						$r[] = "No '{$event}.{$key}.{$type}' key found.";
					}else{
						if(!is_array($data[$key][$type])){
							$r[] = "Invalid '{$event}.{$key}.{$type}', should be an array of string ID's.";
						}else{
							foreach($data[$key][$type] as $v){
								if(!is_string($v) or (strlen($v) < 16 or strlen($v) > 18)){
									$r[] = "Invalid value '".($v??"NULL")."' in '{$event}.{$key}.{$type}', should be a string.";
								}
							}
						}
					}
				}
			}
			return $r;
		};

		if(!array_key_exists("version", $config) or $config["version"] === null){
			$result[] = "No 'version' key found.";
		}else{
			if(!is_int($config["version"]) or $config["version"] <= 0 or $config["version"] > self::EVENT_VERSION){
				$result[] = "Invalid 'version' ({$config["version"]}), you were warned not to touch it...";
			}
		}

		return array_merge(
			$result,
			$checkGenerics($config, "message"),
			$checkGenerics($config, "command", ["toDiscord"], ["channels"]),
			$checkGenerics($config, "member_join", ["toDiscord", "fromDiscord"], ["channels", "servers"]),
			$checkGenerics($config, "member_leave", ["toDiscord", "fromDiscord"], ["channels", "servers"]),
			$checkGenerics($config, "member_transfer", ["toDiscord"], ["channels"])
		);
	}
}