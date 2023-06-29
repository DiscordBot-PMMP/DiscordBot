<?php
/*
 * DiscordBot, PocketMine-MP Plugin.
 *
 * Licensed under the Open Software License version 3.0 (OSL-3.0)
 * Copyright (C) 2020-present JaxkDev
 *
 * Twitter :: @JaxkDev
 * Discord :: JaxkDev
 * Email   :: JaxkDev@gmail.com
 */

namespace JaxkDev\DiscordBot\Communication;

use JaxkDev\DiscordBot\Bot\Client;
use JaxkDev\DiscordBot\Communication\Packets\Packet;
use pmmp\thread\Thread;
use pmmp\thread\ThreadSafeArray;

class BotThread extends Thread{

    const
        STATUS_STARTING = 0,
        STATUS_STARTED  = 1,
        STATUS_READY    = 2,
        STATUS_CLOSING  = 8,
        STATUS_CLOSED   = 9;

    private ThreadSafeArray $initialConfig;

    private ThreadSafeArray $inboundData;
    private ThreadSafeArray $outboundData; //@phpstan-ignore-line Write only.

    private int $status = self::STATUS_STARTING;

    public function __construct(ThreadSafeArray $initialConfig, ThreadSafeArray $inboundData, ThreadSafeArray $outboundData){
        $this->initialConfig = $initialConfig;
        $this->inboundData = $inboundData;
        $this->outboundData = $outboundData;
    }

    public function run(): void{
        //Ignores everything outside our own files.
        require_once(\JaxkDev\DiscordBot\COMPOSER);

        new Client($this, (array)$this->initialConfig);
    }

    public function readInboundData(int $count = 1): array{
        return array_map(function($raw_data){
            $data = (array)json_decode($raw_data, true);
            if(sizeof($data) !== 2){
                throw new \AssertionError("Invalid packet size - " . $raw_data);
            }
            if(!is_int($data[0])){
                throw new \AssertionError("Invalid packet ID - " . $raw_data);
            }
            if(!is_array($data[1])){
                throw new \AssertionError("Invalid packet data - " . $raw_data);
            }
            /** @var Packet $packet */
            $packet = NetworkApi::getPacketClass($data[0]);
            return $packet::fromJson($data[1]);
        }, $this->inboundData->chunk($count));
    }

    public function writeOutboundData(Packet $packet): void{
        try{
            $this->outboundData[] = json_encode([$packet::ID, $packet], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }catch(\JsonException $e){
            throw new \AssertionError("Failed to encode packet to JSON, " . $e->getMessage());
        }
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