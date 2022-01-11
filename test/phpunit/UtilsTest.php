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

use JaxkDev\DiscordBot\Plugin\Utils as Utils;
use PHPUnit\Framework\TestCase;

final class UtilsTest extends TestCase{

    public function testInvalidDiscordSnowflakes(): void{
        $invalid = [
            "2828198861980303",
            "2828198861980303369",
            "000000001108030036"
        ];
        foreach($invalid as $id){
            $this->assertFalse(Utils::validDiscordSnowflake($id), "Invalid Discord Snowflake: $id, incorrectly identified as valid.");
        }
    }

    /**
     * @depends testInvalidDiscordSnowflakes
     */
    public function testValidDiscordSnowflakes(): void{
        $valid = [
            "282819886198030336",
            "601679160582078475",
            "554059221847638040",
            "372022813839851520",
            "782264612866359338",
            "542705689198460934",
            "568704671754092566",
            "554059521916665856",
            "613425648685547541",
            "545364944258990091",
            "90339695967350784",
            "921167299845709896"
        ];
        foreach($valid as $id){
            $this->assertTrue(Utils::validDiscordSnowflake($id), "Valid Discord Snowflake: $id, incorrectly identified as invalid.");
        }
    }
}