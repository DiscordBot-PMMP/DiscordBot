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

use JaxkDev\DiscordBot\Plugin\ApiResolution;
use PHPUnit\Framework\TestCase;

class ApiResolutionTest extends TestCase{
    private $message = "zero";
    private $data = [1, 2, 3];

    public function testEmptyConstructor(): void{
        $this->expectError();
        $this->expectErrorMessageMatches("/.*Expected data for ApiResolution to contain at least a message.*/i");
        new ApiResolution([]);
    }

    public function testNonStringConstructor(): void{
        $this->expectError();
        $this->expectErrorMessageMatches("/.*Expected data for ApiResolution to contain at least a message.*/i");
        new ApiResolution([123]);
    }

    /**
     * @depends testEmptyConstructor
     * @depends testNonStringConstructor
     */
    public function testValidConstructor(): ApiResolution{
        $data = new ApiResolution([$this->message, ...$this->data]);
        $this->assertInstanceOf(ApiResolution::class, $data);
        return $data;
    }

    /**
     * @depends testValidConstructor
     */
    public function testGetMessage(ApiResolution $data): void{
        $this->assertSame($this->message, $data->getMessage());
    }

    /**
     * @depends testValidConstructor
     */
    public function testGetData(ApiResolution $data): void{
        $this->assertSame($this->data, $data->getData());
    }
}
