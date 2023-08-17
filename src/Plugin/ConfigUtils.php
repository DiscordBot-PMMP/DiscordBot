<?php

/*
 * DiscordBot, PocketMine-MP Plugin.
 *
 * Licensed under the Open Software License version 3.0 (OSL-3.0)
 * Copyright (C) 2020-present JaxkDev
 *
 * Twitter :: @JaxkDev
 * Discord :: JaxkDev
 * Email   :: JaxkDev@gmail.com
 */

/*
 * DiscordBot, PocketMine-MP Plugin.
 *
 * Licensed under the Open Software License version 3.0 (OSL-3.0)
 * Copyright (C) 2020-present JaxkDev
 *
 * Twitter :: @JaxkDev
 * Discord :: JaxkDev
 * Email   :: JaxkDev@gmail.com
 */

namespace JaxkDev\DiscordBot\Plugin;

use function array_key_exists;
use function filter_var;
use function forward_static_call;
use function in_array;
use function is_int;
use function is_string;
use function strlen;
use const FILTER_FLAG_IPV4;
use const FILTER_VALIDATE_IP;

abstract class ConfigUtils{

    const VERSION = 4;

    // Map all versions to a static function.
    private const _PATCH_MAP = [
        1 => "patch_1",
        2 => "patch_2",
        3 => "patch_3"
    ];

    static public function update(array &$config): void{
        for($i = (int)$config["version"]; $i < self::VERSION; $i += 1){
            $config = forward_static_call([self::class, self::_PATCH_MAP[$i]], $config);
        }
    }

    static private function patch_1(array $config): array{
        $config["version"] = 2;
        if(!isset($config["discord"])){
            $config["discord"] = [
                "token" => "Long Token here.",
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
            $config["logging"]["max_files"] = $config["logging"]["max_files"] ?? 28;
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

    static private function patch_2(array $config): array{
        $config["version"] = 3;
        unset($config["logging"]["debug"]);
        return $config;
    }

    static private function patch_3(array $config): array{
        $config["version"] = 4;
        $config["type"] = "internal";
        $old = $config["protocol"];
        $config["protocol"] = [
            "general" => [
                "packets_per_tick" => $old["packets_per_tick"] ?? 50,
                "heartbeat_allowance" => $old["heartbeat_allowance"] ?? 60
            ],
            "internal" => [
                "token" => $config["discord"]["token"] ?? "Long Discord Token here."
            ],
            "external" => [
                "host" => "0.0.0.0",
                "port" => 22222
            ]
        ];
        unset($config["discord"]);
        return $config;
    }

    /**
     * Verifies the config's keys and values, returning any keys and a relevant message.
     * @return string[]
     */
    static public function verify(array $config): array{
        $result = [];

        if(!array_key_exists("version", $config) || $config["version"] === null){
            $result[] = "No 'version' field found.";
        }else{
            if(!is_int($config["version"]) || $config["version"] <= 0 || $config["version"] > self::VERSION){
                $result[] = "Invalid 'version' ({$config["version"]}), you were warned not to touch it...";
            }
        }

        if(!array_key_exists("type", $config) || $config["type"] === null){
            $result[] = "No 'type' field found.";
        }else{
            if(!is_string($config["type"]) || !in_array($config["type"], ["internal", "external"], true)){
                $result[] = "Invalid 'type' ({$config["type"]}), must be 'internal' or 'external'.";
            }
        }

        if(!array_key_exists("logging", $config) || $config["logging"] === null){
            $result[] = "No 'logging' field found.";
        }else{
            if(!array_key_exists("max_files", $config["logging"]) || $config["logging"]["max_files"] === null){
                $result[] = "No 'logging.max_files' field found.";
            }else{
                if(!is_int($config["logging"]["max_files"]) || $config["logging"]["max_files"] <= 0){
                    $result[] = "Invalid 'logging.max_files' ({$config["logging"]["max_files"]}), should be an int > 0.";
                }
            }

            if(!array_key_exists("directory", $config["logging"]) || $config["logging"]["directory"] === null){
                $result[] = "No 'logging.directory' field found.";
            }else{
                if(!is_string($config["logging"]["directory"]) || strlen($config["logging"]["directory"]) === 0){
                    $result[] = "Invalid 'logging.directory' ({$config["logging"]["directory"]}).";
                }
            }
        }

        if(!array_key_exists("protocol", $config) || $config["protocol"] === null){
            $result[] = "No 'protocol' field found.";
        }else{
            if(!array_key_exists("general", $config["protocol"]) || $config["protocol"]["general"] === null){
                $result[] = "No 'protocol.general' field found.";
            }else{
                if(!array_key_exists("packets_per_tick", $config["protocol"]["general"]) || $config["protocol"]["general"]["packets_per_tick"] === null){
                    $result[] = "No 'protocol.general.packets_per_tick' field found.";
                }else{
                    if(!is_int($config["protocol"]["general"]["packets_per_tick"]) || $config["protocol"]["general"]["packets_per_tick"] < 5){
                        $result[] = "Invalid 'protocol.general.packets_per_tick' ({$config["protocol"]["general"]["packets_per_tick"]}), Do not touch this without being told to explicitly by JaxkDev";
                    }
                }
                if(!array_key_exists("heartbeat_allowance", $config["protocol"]["general"]) || $config["protocol"]["general"]["heartbeat_allowance"] === null){
                    $result[] = "No 'protocol.general.heartbeat_allowance' field found.";
                }else{
                    if(!is_int($config["protocol"]["general"]["heartbeat_allowance"]) || $config["protocol"]["general"]["heartbeat_allowance"] < 2){
                        $result[] = "Invalid 'protocol.general.heartbeat_allowance' ({$config["protocol"]["general"]["heartbeat_allowance"]}),  Do not touch this without being told to explicitly by JaxkDev";
                    }
                }
            }

            if(!array_key_exists("internal", $config["protocol"]) || $config["protocol"]["internal"] === null){
                $result[] = "No 'protocol.internal' field found.";
            }else{
                if(!array_key_exists("token", $config["protocol"]["internal"]) || $config["protocol"]["internal"]["token"] === null){
                    $result[] = "No 'protocol.internal.token' field found.";
                }else{
                    if(!is_string($config["protocol"]["internal"]["token"]) || strlen($config["protocol"]["internal"]["token"]) < 59){
                        $result[] = "Invalid 'protocol.internal.token' ({$config["protocol"]["internal"]["token"]}), did you follow the wiki ?";
                    }
                }
            }

            if(!array_key_exists("external", $config["protocol"]) || $config["protocol"]["external"] === null){
                $result[] = "No 'protocol.external' field found.";
            }else{
                if(!array_key_exists("host", $config["protocol"]["external"]) || $config["protocol"]["external"]["host"] === null){
                    $result[] = "No 'protocol.external.host' field found.";
                }else{
                    if(filter_var($config["protocol"]["external"]["host"], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) === false){
                        $result[] = "Invalid 'protocol.external.host' ({$config["protocol"]["external"]["host"]}) must be a valid IPv4 address.";
                    }
                }

                if(!array_key_exists("port", $config["protocol"]["external"]) || $config["protocol"]["external"]["port"] === null){
                    $result[] = "No 'protocol.external.port' field found.";
                }else{
                    if(!is_int($config["protocol"]["external"]["port"]) || $config["protocol"]["external"]["port"] < 1 || $config["protocol"]["external"]["port"] > 65535){
                        $result[] = "Invalid 'protocol.external.port' ({$config["protocol"]["external"]["port"]}) must be a valid port (1-65535).";
                    }
                }
            }
        }

        return $result;
    }
}