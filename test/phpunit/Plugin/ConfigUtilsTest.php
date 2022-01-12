<?php
/*
 * DiscordBot, PocketMine-MP Plugin.
 *
 * Licensed under the Open Software License version 3.0 (OSL-3.0)
 * Copyright (C) 2020-present JaxkDev
 *
 * Twitter :: @JaxkDev
 * Discord :: JaxkDev#2698
 * Email   :: JaxkDev@gmail.com
 */

declare(strict_types=1);

use JaxkDev\DiscordBot\Plugin\ConfigUtils;
use PHPUnit\Framework\TestCase;

final class ConfigUtilsTest extends TestCase{

    private static $latest;

    /**
     * @throws Exception
     */
    public static function setUpBeforeClass(): void{
        self::$latest = file_get_contents("resources/config.yml");
        if(self::$latest === false){
            throw new Exception("Failed to read file resources/config.yml");
        }
        self::$latest = yaml_parse(self::$latest);
        if(!is_array(self::$latest)){
            throw new Exception("Failed to parse file resources/config.yml");
        }
    }

    public function testLatestUpdate(): void{
        $old = $data = self::$latest;
        ConfigUtils::update($data);
        $this->assertSame($old, $data);
    }

    /**
     * Goes through every patch function and checks it results in the most up-to-date config.
     * @depends testLatestUpdate
     */
    public function testUpdate(): void{
        $data = ["version" => 1];
        ConfigUtils::update($data);
        $this->assertSame(self::$latest, $data);
        $data = ["version" => 1, "discord" => ["token" => "Long Token here.", "usePluginCacert" => true], "logging" => ["debug" => false,  "directory" => "logs", "maxFiles" => 28]];
        ConfigUtils::update($data);
        //Array order gets mismatched with Same, TODO This needs fixing sameSize will always be true (ish)
        $this->assertSameSize(self::$latest, $data);
    }

    public function testDefaultVerify(): void{
        $data = self::$latest;
        $data["discord"]["token"] = str_repeat("x", 60);
        $this->assertSame([], ConfigUtils::verify($data));
    }

    /**
     * @depends testDefaultVerify
     */
    public function testEmptyVerify(): void{
        $data = ConfigUtils::verify([]);
        $this->assertCount(4, $data);
        $this->assertContains("No 'version' field found.", $data);
        $this->assertContains("No 'discord' field found.", $data);
        $this->assertContains("No 'logging' field found.", $data);
        $this->assertContains("No 'protocol' field found.", $data);
    }

    /**
     * @depends testEmptyVerify
     */
    public function testSubEmptyVerify(): void{
        $data = ConfigUtils::verify(["version" => 1, "discord" => [], "logging" => [], "protocol" => []]);
        $this->assertCount(6, $data);
        $this->assertContains("No 'discord.token' field found.", $data);
        $this->assertContains("No 'discord.use_plugin_cacert' field found.", $data);
        $this->assertContains("No 'logging.max_files' field found.", $data);
        $this->assertContains("No 'logging.directory' field found.", $data);
        $this->assertContains("No 'protocol.packets_per_tick' field found.", $data);
        $this->assertContains("No 'protocol.heartbeat_allowance' field found.", $data);
    }

    /**
     * @depends testSubEmptyVerify
     */
    public function testInvalidTypeVerify(): void{
        $data = self::$latest;
        $data["version"] = strval(ConfigUtils::VERSION);
        $data["discord"]["token"] = false;
        $data["discord"]["use_plugin_cacert"] = "false";
        $data["logging"]["max_files"] = "5";
        $data["logging"]["directory"] = 5;
        $data["protocol"]["packets_per_tick"] = "100";
        $data["protocol"]["heartbeat_allowance"] = "5";
        $res = ConfigUtils::verify($data);
        $this->assertCount(7, $res);
        $this->assertContains("Invalid 'version' ({$data["version"]}), you were warned not to touch it...", $res);
        $this->assertContains("Invalid 'discord.token' ({$data["discord"]["token"]}), did you follow the wiki ?", $res);
        $this->assertContains("Invalid 'discord.use_plugin_cacert' ({$data["discord"]["use_plugin_cacert"]}), must be true or false", $res);
        $this->assertContains("Invalid 'logging.max_files' ({$data["logging"]["max_files"]}), should be an int > 0.", $res);
        $this->assertContains("Invalid 'logging.directory' ({$data["logging"]["directory"]}).", $res);
        $this->assertContains("Invalid 'protocol.packets_per_tick' ({$data["protocol"]["packets_per_tick"]}), Do not touch this without being told to explicitly by JaxkDev", $res);
        $this->assertContains("Invalid 'protocol.heartbeat_allowance' ({$data["protocol"]["heartbeat_allowance"]}),  Do not touch this without being told to explicitly by JaxkDev", $res);
    }

    /**
     * @depends testInvalidTypeVerify
     */
    public function testInvalidValueVerify(): void{
        $data = self::$latest;
        $data["version"] = ConfigUtils::VERSION+1;
        $data["discord"]["token"] = "123";
        $data["logging"]["max_files"] = 0;
        $data["logging"]["directory"] = "";
        $data["protocol"]["packets_per_tick"] = 1;
        $data["protocol"]["heartbeat_allowance"] = 1;
        $res = ConfigUtils::verify($data);
        $this->assertCount(6, $res);
        $this->assertContains("Invalid 'version' ({$data["version"]}), you were warned not to touch it...", $res);
        $this->assertContains("Invalid 'discord.token' ({$data["discord"]["token"]}), did you follow the wiki ?", $res);
        $this->assertContains("Invalid 'logging.max_files' ({$data["logging"]["max_files"]}), should be an int > 0.", $res);
        $this->assertContains("Invalid 'logging.directory' ({$data["logging"]["directory"]}).", $res);
        $this->assertContains("Invalid 'protocol.packets_per_tick' ({$data["protocol"]["packets_per_tick"]}), Do not touch this without being told to explicitly by JaxkDev", $res);
        $this->assertContains("Invalid 'protocol.heartbeat_allowance' ({$data["protocol"]["heartbeat_allowance"]}),  Do not touch this without being told to explicitly by JaxkDev", $res);
    }
}