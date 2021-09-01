<?php
/*
 * DiscordBot, PocketMine-MP Plugin.
 *
 * Licensed under the Open Software License version 3.0 (OSL-3.0)
 * Copyright (C) 2020-2021 JaxkDev
 *
 * Twitter :: @JaxkDev
 * Discord :: JaxkDev#2698
 * Email   :: JaxkDev@gmail.com
 */

namespace JaxkDev\DiscordBot\Communication;

use AttachableThreadedLogger;
use JaxkDev\DiscordBot\Bot\Client;
use JaxkDev\DiscordBot\Communication\Packets\Packet;
use pocketmine\Thread;
use pocketmine\utils\MainLogger;
use Volatile;

class BotThread extends Thread{

    const
        STATUS_STARTING = 0,
        STATUS_STARTED  = 1,
        STATUS_READY    = 2,
        STATUS_CLOSING  = 8,
        STATUS_CLOSED   = 9;

    /** @var MainLogger */
    private $logger;

    /**  @var array */
    private $initialConfig;

    /** @var Volatile */
    private $inboundData;
    /** @var Volatile */
    private $outboundData;

    /** @var int */
    private $status = self::STATUS_STARTING;

    public function __construct(AttachableThreadedLogger $logger, array $initialConfig, Volatile $inboundData, Volatile $outboundData){
        if($logger instanceof MainLogger){
            $this->logger = $logger;
        }else{
            throw new \AssertionError("No MainLogger passed to constructor ?  (Are you using an unofficial pmmp release....)");
        }
        $this->initialConfig = $initialConfig;
        $this->inboundData = $inboundData;
        $this->outboundData = $outboundData;
    }

    public function run(){
        $this->logger->setLogDebug(true);
        $this->logger->registerStatic();

        //Ignores all third party plugins essentially making this thread COMPLETELY independent of other plugins
        require_once(\pocketmine\COMPOSER_AUTOLOADER_PATH);
        require_once(\JaxkDev\DiscordBot\COMPOSER);

        new Client($this, (array)$this->initialConfig);
    }

    public function registerClassLoader(){}

    public function readInboundData(int $count = 1): array{
        return array_map(function($data){
            /** @var Packet $packet */
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
        if(!in_array($status, [self::STATUS_STARTING, self::STATUS_STARTED, self::STATUS_READY, self::STATUS_CLOSING, self::STATUS_CLOSED])){
            throw new \AssertionError("Invalid thread status.");
        }
        $this->status = $status;
    }

    public function getStatus(): int{
        return $this->status;
    }

    public function getLogger(): MainLogger{
        return $this->logger;
    }

    public function getThreadName() : string{
        return "Discord";
    }
}