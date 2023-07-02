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

use JaxkDev\DiscordBot\Communication\Packets\Packet;
use JaxkDev\DiscordBot\ExternalBot\Client as ExternalClient;
use JaxkDev\DiscordBot\InternalBot\Client as InternalClient;
use pmmp\thread\Thread as PMMPThread;
use pmmp\thread\ThreadSafeArray;

/**
 * This class is used to represent a thread that is used for network communication.
 * There are two options, internal (hosting the bot) and external (hosting the bot outside the server).
 */
 class Thread extends PMMPThread{

    private ThreadStatus $status = ThreadStatus::STARTING;

    private ThreadSafeArray $config;
    private ThreadSafeArray $inboundData;
    private ThreadSafeArray $outboundData; //@phpstan-ignore-line Write only.

    public function __construct(ThreadSafeArray $config, ThreadSafeArray $inboundData, ThreadSafeArray $outboundData){
        $this->config = $config;
        $this->inboundData = $inboundData;
        $this->outboundData = $outboundData;
    }

    public function run(): void{
        //Ignores everything outside our own files.
        require_once(\JaxkDev\DiscordBot\COMPOSER);

        // Mono logger can have issues with other timezones, for now use UTC.
        // Must be set globally due to internal methods in the rotating file handler.
        // Note, this does not affect outside thread.
        ini_set("date.timezone", "UTC");

        /** @phpstan-ignore-next-line ThreadSafeArray */
        if($this->config["discord"]["type"] === "internal"){
            new InternalClient($this);
        }else{
            new ExternalClient($this);
        }
    }

    public function getStatus(): ThreadStatus{
        return $this->status;
    }

    public function setStatus(ThreadStatus $status): void{
        $this->status = $status;
    }

    /**
     * @see Thread::secureConfig() Recommended to secure config after getting token.
     */
    public function getConfig(): array{
        return (array)$this->config;
    }

    /**
     * Removes sensitive data from the config.
     * This is recommended once token has been loaded to avoid token leaks on crashes etc.
     */
    public function secureConfig(): void{
        /** @phpstan-ignore-next-line ThreadSafeArray */
        $this->config["discord"]["token"] = "**** Redacted Token ****";
    }

    /** @return Packet[] */
    public function readInboundData(int $count = 1): array{
        return array_map(function($raw_data){
            $data = (array)json_decode($raw_data, true);
            if(sizeof($data) !== 2){
                throw new \AssertionError("Invalid packet size - ".$raw_data);
            }
            if(!is_int($data[0])){
                throw new \AssertionError("Invalid packet ID - ".$raw_data);
            }
            if(!is_array($data[1])){
                throw new \AssertionError("Invalid packet data - ".$raw_data);
            }
            /** @var ?Packet $packet */
            $packet = NetworkApi::getPacketClass($data[0]);
            if($packet === null){
                throw new \AssertionError("Invalid packet ID - ".$raw_data);
            }
            return $packet::fromJson($data[1]);
        }, $this->inboundData->chunk($count));
    }

    /**
     * @param Packet $data
     */
    public function writeOutboundData(Packet $data): void{
        try{
            $this->outboundData[] = json_encode([$data::ID, $data], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }catch(\JsonException $e){
            throw new \AssertionError("Failed to encode packet to JSON, ".$e->getMessage());
        }
    }
}