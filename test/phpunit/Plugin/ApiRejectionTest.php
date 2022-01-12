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

use JaxkDev\DiscordBot\Plugin\ApiRejection;
use PHPUnit\Framework\TestCase;

class ApiRejectionTest extends TestCase{

    public function testConstructor(): ApiRejection{
        $msg = "Test API Rejection";
        $data = new ApiRejection($msg);
        $this->assertSame($msg, $data->getMessage());
        return $data;
    }

    /**
     * @depends testConstructor
     */
    public function testGetOriginalMessage(ApiRejection $empty): void{
        $this->assertNull($empty->getOriginalMessage());
        $data = new ApiRejection("", ["Message", "Trace"]);
        $this->assertSame("Message", $data->getOriginalMessage());
    }

    /**
     * @depends testConstructor
     */
    public function testGetOriginalTrace(ApiRejection $empty): void{
        $this->assertNull($empty->getOriginalTrace());
        $data = new ApiRejection("", ["Message", "Trace"]);
        $this->assertSame("Trace", $data->getOriginalTrace());
    }
}
