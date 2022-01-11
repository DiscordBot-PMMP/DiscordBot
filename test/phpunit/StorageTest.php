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

use JaxkDev\DiscordBot\Models\Server;
use JaxkDev\DiscordBot\Plugin\Storage;
use PHPUnit\Framework\TestCase;

//TODO Add model constructor dependencies where applicable.
//TODO Better model usage.
final class StorageTest extends TestCase{

    public function testGetServers(): void{
        $this->assertEmpty(Storage::getServers(), "If this triggers, the plugin has died :)");
    }

    /**
     * //TODO Depends on Server Creation.
     * @depends testGetServers
     */
    public function testAddServer(): void{
        $s = new Server("554059221847638040", "Test Server", "UK", "282819886198030336", false, 10);
        Storage::addServer($s);
        $this->assertCount(1, Storage::getServers(), "Failed to add server to storage.");
        //Duplicated.
        Storage::addServer($s);
        $this->assertCount(1, Storage::getServers(), "Failed to detect duplicated server being added to storage.");
    }

    /**
     * //TODO Depends on Server Creation & Server::setRegion
     * @depends testAddServer
     */
    public function testUpdateServer(): void{
        $s = new Server("554059221847638040", "Test Server", "UK", "282819886198030336", false, 10);
        $s->setRegion("US");
        Storage::updateServer($s);
        $this->assertCount(1, Storage::getServers());
        //Different ID, should be added not updated.
        $s = new Server("554059221847638041", "Test Server 2", "UK", "282819886198030336", false, 20);
        Storage::updateServer($s);
        $this->assertCount(2, Storage::getServers());
    }

    /**
     * @depends testGetServers
     * @depends testUpdateServer
     */
    public function testGetServer(): void{
        $this->assertInstanceOf(Server::class, Storage::getServer("554059221847638040"));
        $this->assertInstanceOf(Server::class, Storage::getServer("554059221847638041"));
        $this->assertNull(Storage::getServer("111111111111111111"));
        $this->assertNull(Storage::getServer("Invalid ID"));
    }

    public function testGetTimestamp(): void{
        $this->assertGreaterThanOrEqual(0, Storage::getTimestamp());
    }

    public function testSetTimestamp(): void{
        Storage::setTimestamp(0);
        Storage::setTimestamp(time());
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Timestamp must be greater than or equal to 0.");
        Storage::setTimestamp(-1);
    }
}