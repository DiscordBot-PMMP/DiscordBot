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
            $this->setStatus(self::STATUS_CLOSED);
            throw new \AssertionError("No MainLogger passed to constructor ?  (Are you using an unofficial pmmp release....)");
        }
        $this->initialConfig = $initialConfig;
        $this->inboundData = $inboundData;
        $this->outboundData = $outboundData;
    }

    public function run(){
        //$this->logger->setLogDebug(true);
        $this->logger->registerStatic();

        //Check for conflicts between pocketmines vendor and mine.
        $this->checkDependencyConflicts();

        //Ignores all third party plugins essentially making this thread COMPLETELY independent of other plugins
        require_once(\pocketmine\COMPOSER_AUTOLOADER_PATH);
        require_once(\JaxkDev\DiscordBot\COMPOSER);

        new Client($this, (array)$this->initialConfig);
    }

    protected function checkDependencyConflicts(): void{
        $file = dirname(\pocketmine\COMPOSER_AUTOLOADER_PATH)."/composer/installed.json";
        if(!file_exists($file)){
            $this->setStatus(self::STATUS_CLOSED);
            throw new \RuntimeException("Failed to check dependency conflicts with pocketmine, cannot find file at '$file'.");
        }
        $data = file_get_contents($file);
        if($data === false){
            $this->setStatus(self::STATUS_CLOSED);
            throw new \RuntimeException("Failed to check dependency conflicts with pocketmine, failed to get file contents from '$file'.");
        }
        $installed = json_decode($data, true);
        if($installed === null){
            $this->setStatus(self::STATUS_CLOSED);
            throw new \RuntimeException("Failed to check dependency conflicts with pocketmine, unable to parse composer installed.json at '$file'.");
        }
        $names = array_map(function($data){
            return strtolower($data["name"]);
        }, $installed["packages"]);

        $file = dirname(\JaxkDev\DiscordBot\COMPOSER)."/composer/installed.json";
        if(!file_exists($file)){
            $this->setStatus(self::STATUS_CLOSED);
            throw new \RuntimeException("Failed to check dependency conflicts with pocketmine, cannot find file at '$file'.");
        }
        $data = file_get_contents($file);
        if($data === false){
            $this->setStatus(self::STATUS_CLOSED);
            throw new \RuntimeException("Failed to check dependency conflicts with pocketmine, failed to get file contents from '$file'.");
        }
        $installed = json_decode($data, true);
        if($installed === null){
            $this->setStatus(self::STATUS_CLOSED);
            throw new \RuntimeException("Failed to check dependency conflicts with pocketmine, unable to parse composer installed.json at '$file'.");
        }
        $pluginNames = array_map(function($data){
            return strtolower($data["name"]);
        }, $installed["packages"]);

        if(sizeof($diff = array_diff($pluginNames, $names)) !== sizeof($pluginNames)){
            $conflicts = array_diff($pluginNames, $diff);
            $this->setStatus(self::STATUS_CLOSED);
            throw new \RuntimeException("Composer dependency conflicts found, unable to run plugin. (Conflicts: ".join(", ", $conflicts).")");
        }
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