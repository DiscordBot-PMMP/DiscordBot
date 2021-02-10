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

// TODO Event data validation.
use pocketmine\utils\MainLogger;

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

	static function update(array &$config): void{
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
	 * @description Verifies the config's keys and values, returning any keys and a relevant message.
	 * @param array $config
	 * @return array
	 */
	static function verify(array $config): array{
		$result = [];

		if(!array_key_exists("version", $config)){
			$result["version"] = "No 'version' key found.";
		}else{
			if(!is_int($config["version"]) or $config["version"] <= 0 or $config["version"] > self::VERSION){
				$result["version"] = "Invalid 'version' ({$config["version"]}), you were warned not to touch it...";
			}
		}

		if(!array_key_exists("discord", $config)){
			$result["discord"] = "No 'discord' key found.";
		}else{
			if(!array_key_exists("token", $config['discord'])){
				$result["discord.token"] = "No 'discord.token' key found.";
			}else{
				if(!is_string($config["discord"]["token"]) or strlen($config["discord"]["token"]) < 59){
					$result["discord.token"] = "Invalid 'discord.token' ({$config["discord"]["token"]}), did you follow the wiki ?";
				}
			}
			if(!array_key_exists("usePluginCacert", $config["discord"])){
				$result["discord.usePluginCacert"] = "No 'discord.usePluginCacert' key found.";
			}else{
				if(!is_bool($config["discord"]["usePluginCacert"])){
					$result["discord.usePluginCacert"] = "Invalid 'discord.usePluginCacert' ({$config["discord"]["usePluginCacert"]}), must be true or false";
				}
			}
		}

		if(!array_key_exists("logging", $config)){
			$result["logging"] = "No 'logging' key found.";
		}else{
			if(!array_key_exists("debug", $config["logging"])){
				$result["logging.debug"] = "No 'logging.debug' value found.";
			}else{
				if(!is_bool($config["logging"]["debug"])){
					$result["logging.debug"] = "Invalid 'logging.debug' ({$config["logging"]["debug"]}), should be true or false.";
				}
			}

			if(!array_key_exists("maxFiles", $config["logging"])){
				$result["logging.maxFiles"] = "No 'logging.maxFiles' key found.";
			}else{
				if(!is_int($config["logging"]["maxFiles"]) or $config["logging"]["maxFiles"] <= 0){
					$result["logging.maxFiles"] = "Invalid 'logging.maxFiles' ({$config["logging"]["maxFiles"]}), should be an int > 0.";
				}
			}

			if(!array_key_exists("directory", $config["logging"])){
				$result["logging.directory"] = "No 'logging.directory' key found.";
			}else{
				if(!is_string($config["logging"]["directory"]) or strlen($config["logging"]["directory"]) === 0){
					$result["logging.directory"] = "Invalid 'logging.directory' ({$config["logging"]["directory"]}).";
				}
			}
		}

		return $result;
	}
}