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

use JaxkDev\DiscordBot\Libs\React\Promise\PromiseInterface;
use JaxkDev\DiscordBot\Plugin\ApiResolver;
use PHPUnit\Framework\TestCase;

class ApiResolverTest extends TestCase{
    private $uid = 123;

    public function testCreate(): PromiseInterface{
        $data = ApiResolver::create($this->uid);
        $this->assertInstanceOf(PromiseInterface::class, $data);
        return $data;
    }

    /**
     * @depends testCreate
     */
    public function testDuplicateCreate(): void{
        $this->expectExceptionMessage("Packet {$this->uid} already linked to a promise resolver.");
        ApiResolver::create($this->uid);
        return;
    }

    /**
     * @depends testCreate
     */
    public function testGetPromise(PromiseInterface $data): void{
        $this->assertSame($data, ApiResolver::getPromise($this->uid));
        $this->assertNull(ApiResolver::getPromise(0));
    }

    //TODO better handleResolution, depends on packet tests.

    /**
     * //TODO Depends on Resolution packet.
     * @depends testCreate
     * @depends ApiResolutionTest::testGetMessage
     * @depends ApiResolutionTest::testGetData
     */
    public function testHandleSuccessfulResolution(PromiseInterface $promise): void{
        $data = new \JaxkDev\DiscordBot\Communication\Packets\Resolution($this->uid, true, "Response", ["Data", 1, 2]);
        $promise->done(function(\JaxkDev\DiscordBot\Plugin\ApiResolution $res) use ($data){
            $this->assertSame($data->wasSuccessful(), true);
            $this->assertSame($data->getResponse(), $res->getMessage());
            $this->assertSame($data->getData(), $res->getData());
        }, function(\JaxkDev\DiscordBot\Plugin\ApiRejection $res){
            $this->fail("Promise should not be rejected.");
        });
        ApiResolver::handleResolution($data);
        $this->assertNull(ApiResolver::getPromise($this->uid));
    }

    /**
     * //TODO Depends on Resolution packet.
     * @depends ApiRejectionTest::testGetOriginalMessage
     * @depends ApiRejectionTest::testGetOriginalTrace
     * @depends testHandleSuccessfulResolution
     */
    public function testHandleUnsuccessfulResolution(): void{
        $promise = ApiResolver::create($this->uid);
        $response = "Response";
        $msg = "Message";
        $trace = "Trace";
        $data = new \JaxkDev\DiscordBot\Communication\Packets\Resolution($this->uid, false, $response, [$msg, $trace]);
        $promise->done(function(\JaxkDev\DiscordBot\Plugin\ApiResolution $res){
            $this->fail("Promise should not be fulfilled.");
        }, function(\JaxkDev\DiscordBot\Plugin\ApiRejection $res) use($data, $response, $msg, $trace){
            $this->assertSame($data->wasSuccessful(), false);
            $this->assertSame($response, $res->getMessage());
            $this->assertSame($msg, $res->getOriginalMessage());
            $this->assertSame($trace, $res->getOriginalTrace());
        });
        ApiResolver::handleResolution($data);
        $this->assertNull(ApiResolver::getPromise($this->uid));
    }

    /**
     * //TODO Depends on Resolution packet.
     * @depends ApiRejectionTest::testGetOriginalMessage
     * @depends ApiRejectionTest::testGetOriginalTrace
     * @depends testHandleSuccessfulResolution
     */
    public function testHandleInvalidResolution(): void{
        $data = new \JaxkDev\DiscordBot\Communication\Packets\Resolution($this->uid, true, "");
        $this->expectExceptionMessage("Failed to fetch DiscordBot logger.");
        ApiResolver::handleResolution($data);
    }
}
