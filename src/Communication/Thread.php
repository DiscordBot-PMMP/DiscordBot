<?php

/*
 * DiscordBot, PocketMine-MP Plugin.
 *
 * Licensed under the Open Software License version 3.0 (OSL-3.0)
 * Copyright (C) 2020-present JaxkDev
 *
 * Discord :: JaxkDev
 * Email   :: JaxkDev@gmail.com
 */

namespace JaxkDev\DiscordBot\Communication;

use JaxkDev\DiscordBot\Communication\Packets\Packet;
use JaxkDev\DiscordBot\ExternalBot\Client as ExternalClient;
use JaxkDev\DiscordBot\InternalBot\Client as InternalClient;
use pmmp\thread\Thread as PMMPThread;
use pmmp\thread\ThreadSafeArray;
use function array_map;
use function bin2hex;
use function ini_set;

/**
 * This class is used to represent a thread that is used for network communication.
 * There are two options, internal (hosting the bot) and external (hosting the bot outside the server).
 */
 final class Thread extends PMMPThread{

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

        if($this->config["type"] === "internal"){
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
        $this->config["protocol"]["internal"]["token"] = "**** Redacted Token ****";
    }

     /**
      * @return Packet[]
      */
    public function readInboundData(int $count = 1): array{
        return array_map(function($raw_data){
            $stream = new BinaryStream($raw_data);
            trY{
                $pid = $stream->getShort();
            }catch(\Exception){
                throw new \AssertionError("Invalid packet received - " . bin2hex($raw_data));
            }
            /** @var class-string<Packet>|null $packet */
            $packet = NetworkApi::getPacketClass($pid);
            if($packet === null){
                throw new \AssertionError("Invalid packet ID $pid - " . bin2hex($raw_data));
            }
            try{
                /** @var Packet $x */
                $x = $packet::fromBinary($stream);
                return $x;
            }catch(\Exception $e){
                throw new \AssertionError("Failed to parse packet($pid) - " . $e->getMessage(), 0, $e);
            }
        }, $this->inboundData->chunk($count));
    }

    public function writeOutboundData(Packet $data): void{
        $stream = new BinaryStream();
        $stream->putShort($data::SERIALIZE_ID);
        $stream->putSerializable($data);
        $this->outboundData[] = $stream->getBuffer();
    }
}