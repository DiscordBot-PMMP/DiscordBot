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

namespace JaxkDev\DiscordBot\Communication;

use JaxkDev\DiscordBot\Bot\Client;
use JaxkDev\DiscordBot\Communication\Packets\Packet;
use Thread;
use Volatile;

class BotThread extends Thread{

    const
        STATUS_STARTING = 0,
        STATUS_STARTED  = 1,
        STATUS_READY    = 2,
        STATUS_CLOSING  = 8,
        STATUS_CLOSED   = 9;

    /**  @var array */
    private $initialConfig;

    /** @var Volatile */
    private $inboundData;
    /** @var Volatile */
    private $outboundData; //@phpstan-ignore-line Write only.

    /** @var int */
    private $status = self::STATUS_STARTING;

    public function __construct(array $initialConfig, Volatile $inboundData, Volatile $outboundData){
        $this->initialConfig = $initialConfig;
        $this->inboundData = $inboundData;
        $this->outboundData = $outboundData;
    }

    public function run(): void{
        //Ignores everything outside our own files.
        require_once(\JaxkDev\DiscordBot\COMPOSER);

        /* @phpstan-ignore-next-line phpstan-strict-rules initialConfig is volatile not array */
        new Client($this, (array)$this->initialConfig);
    }

    public function readInboundData(int $count = 1): array{
        return array_map(function($data){
            $packet = unserialize($data);
            if(!$packet instanceof Packet){
                throw new \AssertionError("Data did not unserialize to a Packet.");
            }
            return $packet;
        }, $this->inboundData->chunk($count, false));
    }

    public function writeOutboundData(Packet $packet): void{
        $this->outboundData[] = serialize($packet);
    }

    public function setStatus(int $status): void{
        if(!in_array($status, [self::STATUS_STARTING, self::STATUS_STARTED, self::STATUS_READY, self::STATUS_CLOSING, self::STATUS_CLOSED], true)){
            throw new \AssertionError("Invalid thread status.");
        }
        $this->status = $status;
    }

    public function getStatus(): int{
        return $this->status;
    }
}