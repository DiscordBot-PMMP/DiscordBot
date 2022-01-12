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

use JaxkDev\DiscordBot\Models\Ban;
use PHPUnit\Framework\TestCase;

class testBan extends Ban{
    public function serialize(): ?string{
        return serialize("Hello");
    }
}

final class BanTest extends TestCase{

    private $server_id = "554059221847638040";
    private $user_id = "554059221847638040";
    private $reason = "Reason.";
    private $days = 1;

    /**
     * @depends UtilsTest::testValidDiscordSnowflakes
     */
    public function testMinimalConstructor(): Ban{
        $data = new Ban($this->server_id, $this->user_id);
        $this->assertInstanceOf(Ban::class, $data);
        return $data;
    }

    /**
     * @depends testMinimalConstructor
     */
    public function testConstructor(): Ban{
        $data = new Ban($this->server_id, $this->user_id, $this->reason, $this->days);
        $this->assertInstanceOf(Ban::class, $data);
        return $data;
    }

    /**
     * @depends testConstructor
     * @depends testMinimalConstructor
     */
    public function testGetId(Ban $ban, Ban $ban2): void{
        $this->assertSame($this->server_id.".".$this->user_id, $ban->getId());
        $this->assertSame($this->server_id.".".$this->user_id, $ban->getId());
    }

    /**
     * @depends testConstructor
     * @depends testMinimalConstructor
     */
    public function testGetServerId(Ban $ban, Ban $ban2): void{
        $this->assertSame($this->server_id, $ban->getServerId());
        $this->assertSame($this->server_id, $ban2->getServerId());
    }

    /**
     * @depends testConstructor
     * @depends testMinimalConstructor
     */
    public function testGetUserId(Ban $ban, Ban $ban2): void{
        $this->assertSame($this->user_id, $ban->getUserId());
        $this->assertSame($this->user_id, $ban2->getUserId());
    }

    /**
     * @depends testConstructor
     * @depends testMinimalConstructor
     */
    public function testGetReason(Ban $ban, Ban $ban2): void{
        $this->assertSame($this->reason, $ban->getReason());
        $this->assertNull($ban2->getReason());
    }

    /**
     * @depends testConstructor
     * @depends testMinimalConstructor
     */
    public function testGetDaysToDelete(Ban $ban, Ban $ban2): void{
        $this->assertSame($this->days, $ban->getDaysToDelete());
        $this->assertNull($ban2->getDaysToDelete());
    }

    /**
     * @depends testConstructor
     */
    public function testInvalidSetDaysToDelete(Ban $ban): void{
        $this->expectError();
        $this->expectErrorMessageMatches("/.*Days to delete .* is invalid.*/i");
        $ban->setDaysToDelete(8);
    }

    /**
     * @depends testConstructor
     * @depends UtilsTest::testInvalidDiscordSnowflakes
     */
    public function testInvalidSetServerId(Ban $ban): void{
        $this->expectError();
        $this->expectErrorMessageMatches("/.*Server ID .* is invalid.*/i");
        $ban->setServerId("MyServerID");
    }

    /**
     * @depends testConstructor
     * @depends UtilsTest::testInvalidDiscordSnowflakes
     */
    public function testInvalidSetUserId(Ban $ban): void{
        $this->expectError();
        $this->expectErrorMessageMatches("/.*User ID .* is invalid.*/i");
        $ban->setUserId("MyUserID");
    }

    /**
     * @depends testConstructor
     */
    public function testSerialize(Ban $ban): string{
        $data = serialize($ban);
        $this->assertIsString($data);
        return $data;
    }

    /**
     * @depends testSerialize
     */
    public function testUnserialize(string $data): void{
        $data = unserialize($data);
        $this->assertInstanceOf(Ban::class, $data);
        $this->assertSame($this->server_id, $data->getServerId());
        $this->assertSame($this->user_id, $data->getUserId());
        $this->assertSame($this->reason, $data->getReason());
        $this->assertSame($this->days, $data->getDaysToDelete());
    }

    /**
     * @depends testConstructor
     */
    public function testInvalidUnserialize(): void{
        $data = serialize(new testBan($this->server_id, $this->user_id));
        $this->expectError();
        $this->expectErrorMessageMatches("/.*Failed to unserialize data.*/i");
        unserialize($data);
    }
}