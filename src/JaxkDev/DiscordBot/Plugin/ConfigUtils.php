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

abstract class ConfigUtils{

    const VERSION = 2;

    // Map all versions to a static function.
    private const _PATCH_MAP = [
        1 => "patch_1"
    ];

    static public function update(array &$config): void{
        for($i = (int)$config["version"]; $i < self::VERSION; $i += 1){
            $f = self::_PATCH_MAP[$i];
            $config = forward_static_call([self::class, $f], $config);
        }
    }

    static private function patch_1(array $config): array{
        $config["version"] = 2;
        if(!isset($config["discord"])){
            $config["discord"] = [
                "token" => "Long token here",
                "use_plugin_cacert" => true
            ];
        }else{
            $config["discord"]["use_plugin_cacert"] = true;
            unset($config["discord"]["usePluginCacert"]);
        }
        if(!isset($config["logging"])){
            $config["logging"] = [
                "debug" => false,
                "max_files" => 28,
                "directory" => "logs"
            ];
        }else{
            $config["logging"]["max_files"] = $config["logging"]["max_files"]??28;
            unset($config["logging"]["maxFiles"]);
        }
        if(!isset($config["protocol"])){
            $config["protocol"] = [
                "packets_per_tick" => 50,
                "heartbeat_allowance" => 60
            ];
        }
        return $config;
    }

    /**
     * Verifies the config's keys and values, returning any keys and a relevant message.
     * @param array $config
     * @return string[]
     */
    static public function verify(array $config): array{
        $result = [];

        if(!array_key_exists("version", $config) or $config["version"] === null){
            $result[] = "No 'version' field found.";
        }else{
            if(!is_int($config["version"]) or $config["version"] <= 0 or $config["version"] > self::VERSION){
                $result[] = "Invalid 'version' ({$config["version"]}), you were warned not to touch it...";
            }
        }

        if(!array_key_exists("discord", $config) or $config["discord"] === null){
            $result[] = "No 'discord' field found.";
        }else{
            if(!array_key_exists("token", $config["discord"]) or $config["discord"]["token"] === null){
                $result[] = "No 'discord.token' field found.";
            }else{
                if(!is_string($config["discord"]["token"]) or strlen($config["discord"]["token"]) < 59){
                    $result[] = "Invalid 'discord.token' ({$config["discord"]["token"]}), did you follow the wiki ?";
                }
            }
            if(!array_key_exists("use_plugin_cacert", $config["discord"]) or $config["discord"]["use_plugin_cacert"] === null){
                $result[] = "No 'discord.use_plugin_cacert' field found.";
            }else{
                if(!is_bool($config["discord"]["use_plugin_cacert"])){
                    $result[] = "Invalid 'discord.use_plugin_cacert' ({$config["discord"]["use_plugin_cacert"]}), must be true or false";
                }
            }
        }

        if(!array_key_exists("logging", $config) or $config["logging"] === null){
            $result[] = "No 'logging' field found.";
        }else{
            if(!array_key_exists("debug", $config["logging"])  or $config["logging"]["debug"] === null){
                $result[] = "No 'logging.debug' value found.";
            }else{
                if(!is_bool($config["logging"]["debug"])){
                    $result[] = "Invalid 'logging.debug' ({$config["logging"]["debug"]}), should be true or false.";
                }
            }

            if(!array_key_exists("max_files", $config["logging"]) or $config["logging"]["max_files"] === null){
                $result[] = "No 'logging.max_files' field found.";
            }else{
                if(!is_int($config["logging"]["max_files"]) or $config["logging"]["max_files"] <= 0){
                    $result[] = "Invalid 'logging.max_files' ({$config["logging"]["max_files"]}), should be an int > 0.";
                }
            }

            if(!array_key_exists("directory", $config["logging"]) or $config["logging"]["directory"] === null){
                $result[] = "No 'logging.directory' field found.";
            }else{
                if(!is_string($config["logging"]["directory"]) or strlen($config["logging"]["directory"]) === 0){
                    $result[] = "Invalid 'logging.directory' ({$config["logging"]["directory"]}).";
                }
            }
        }

        if(!array_key_exists("protocol", $config) or $config["protocol"] === null){
            $result[] = "No 'protocol' field found.";
        }else{
            if(!array_key_exists("packets_per_tick", $config["protocol"]) or $config["protocol"]["packets_per_tick"] === null){
                $result[] = "No 'protocol.packets_per_tick' field found.";
            }else{
                if(!is_int($config["protocol"]["packets_per_tick"]) or $config["protocol"]["packets_per_tick"] < 5){
                    $result[] = "Invalid 'protocol.packets_per_tick' ({$config["protocol"]["packets_per_tick"]}), Do not touch this without being told to explicitly by JaxkDev";
                }
            }
            if(!array_key_exists("heartbeat_allowance", $config["protocol"]) or $config["protocol"]["heartbeat_allowance"] === null){
                $result[] = "No 'protocol.heartbeat_allowance' field found.";
            }else{
                if(!is_int($config["protocol"]["heartbeat_allowance"]) or $config["protocol"]["heartbeat_allowance"] < 2){
                    $result[] = "Invalid 'protocol.heartbeat_allowance' ({$config["protocol"]["use_plugin_cacert"]}),  Do not touch this without being told to explicitly by JaxkDev";
                }
            }
        }

        return $result;
    }
}