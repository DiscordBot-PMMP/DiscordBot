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

use JaxkDev\DiscordBot\Communication\Packets\Heartbeat;
use PHPUnit\Framework\TestCase;

class testHeartbeat extends Heartbeat{
    public function serialize(): ?string{
        return serialize("Hello");
    }
}

final class HeartbeatTest extends TestCase{

    private $timestamp = 12345.12345;

    public function testInvalidConstructor(): void{
        $this->expectError();
        $this->expectErrorMessageMatches("/.*Argument #1 \\([^)]*\\) must be of type [a-zA-Z]+, string given/i");
        /** @noinspection PhpStrictTypeCheckingInspection */
        (new Heartbeat("4"));
    }

    /**
     * @depends testInvalidConstructor
     */
    public function testConstructor(): Heartbeat{
        $packet = new Heartbeat($this->timestamp);
        $this->assertInstanceOf(Heartbeat::class, $packet);
        return $packet;
    }

    /**
     * @depends testConstructor
     */
    public function testGetHeartbeat(Heartbeat $packet): void{
        $this->assertSame($this->timestamp, $packet->getHeartbeat());
    }

    /**
     * @depends testConstructor
     */
    public function testSerialize(Heartbeat $packet): string{
        $data = serialize($packet);
        $this->assertIsString($data);
        return $data;
    }

    /**
     * @depends testSerialize
     */
    public function testUnserialize(string $data): void{
        $data = unserialize($data);
        $this->assertInstanceOf(Heartbeat::class, $data);
        $this->assertSame($this->timestamp, $data->getHeartbeat());
    }

    /**
     * @depends testConstructor
     */
    public function testInvalidUnserialize(): void{
        $data = serialize(new testHeartbeat(5.0));
        $this->expectError();
        $this->expectErrorMessageMatches("/.*Failed to unserialize.*/i");
        unserialize($data);
    }
}