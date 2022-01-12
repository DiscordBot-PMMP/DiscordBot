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

use JaxkDev\DiscordBot\Communication\Packets\Packet;
use PHPUnit\Framework\TestCase;

final class PacketTest extends TestCase{

    public function testConstructor(): Packet{
        $packet = new class extends Packet{
            public function serialize(): ?string{ return ""; }
            public function unserialize($data): void{}
        };
        $this->assertInstanceOf(Packet::class, $packet);
        return $packet;
    }

    /**
     * @depends testConstructor
     */
    public function testGetUID(Packet $packet): void{
        $this->assertEquals(Packet::$UID_COUNT, $packet->getUID());
    }
}