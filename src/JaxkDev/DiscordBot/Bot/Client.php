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

namespace JaxkDev\DiscordBot\Bot;

use Discord\Exceptions\IntentException;
use Discord\WebSockets\Intents;
use Discord\WebSockets\Op;
use Error;
use ErrorException;
use JaxkDev\DiscordBot\Bot\Handlers\DiscordEventHandler;
use JaxkDev\DiscordBot\Bot\Handlers\CommunicationHandler;
use JaxkDev\DiscordBot\Bot\Handlers\LogStreamHandler;
use JaxkDev\DiscordBot\Communication\BotThread;
use JaxkDev\DiscordBot\Communication\Packets\Packet;
use Monolog\Logger;
use Monolog\Handler\RotatingFileHandler;
use pocketmine\utils\Utils;
use React\EventLoop\TimerInterface;
use Throwable;

class Client{

    /** @var BotThread */
    private $thread;

    /** @var Discord */
    private $client;

    /** @var Logger */
    private $logger;

    /** @var CommunicationHandler */
    private $communicationHandler;

    /** @var DiscordEventHandler */
    private $discordEventHandler;

    /** @var TimerInterface|null */
    private $readyTimer;
    /** @var TimerInterface|null */
    private $tickTimer;

    /** @var int */
    private $tickCount;
    /** @var int */
    private $lastGCCollection = 0;

    /** @var array */
    private $config;

    public function __construct(BotThread $thread, array $config){
        $this->thread = $thread;
        $this->config = $config;

        gc_enable();

        error_reporting(E_ALL & ~E_NOTICE);
        set_error_handler([$this, 'sysErrorHandler']);
        register_shutdown_function([$this, 'close']);

        // Mono logger can have issues with other timezones, for now use UTC.
        // Must be set globally due to internal methods in the rotating file handler.
        // Note, this does not effect outside thread config.
        ini_set("date.timezone", "UTC");

        Packet::$UID_COUNT = 1;

        $this->logger = new Logger('DiscordThread');
        $handler = new RotatingFileHandler(\JaxkDev\DiscordBot\DATA_PATH.$config['logging']['directory'].DIRECTORY_SEPARATOR."DiscordBot.log", $config['logging']['max_files'], Logger::DEBUG);
        $handler->setFilenameFormat('{filename}-{date}', 'Y-m-d');
        $this->logger->setHandlers(array($handler));
        $this->logger->pushHandler(new LogStreamHandler($this->thread->getLogger(), $config["logging"]["debug"]));

        $socket_opts = [];
        if($config["discord"]["use_plugin_cacert"]){
            $this->logger->debug("TLS cafile set to '".\JaxkDev\DiscordBot\DATA_PATH."cacert.pem"."'");
            $socket_opts["tls"] = [
                "cafile" => \JaxkDev\DiscordBot\DATA_PATH."cacert.pem"
            ];
        }

        try{
            $this->client = new Discord([
                'token' => $config['discord']['token'],
                'logger' => $this->logger,
                'socket_options' => $socket_opts,
                'loadAllMembers' => true,
                'storeMessages' => true,
                'intents' => Intents::getAllIntents()
            ]);
        }catch(IntentException $e){
            $this->close($e);
        }

        $this->config['discord']['token'] = "REDACTED";

        $this->communicationHandler = new CommunicationHandler($this);
        $this->discordEventHandler = new DiscordEventHandler($this);

        $this->registerHandlers();
        $this->registerTimers();

        if($this->thread->getStatus() === BotThread::STATUS_STARTING){
            $this->thread->setStatus(BotThread::STATUS_STARTED);
            $this->client->run();
        }else{
            $this->logger->warning("Closing thread, unexpected state change.");
            $this->close();
        }
    }

    private function registerTimers(): void{
        // Handles shutdown, rather than a SHUTDOWN const to send through internal communication, set flag to closed.
        // Saves time & will guarantee closure ASAP rather then waiting in line through ^
        $this->client->getLoop()->addPeriodicTimer(1, function(){
            if($this->thread->getStatus() === BotThread::STATUS_CLOSING){
                $this->close();
            }
        });

        // Handles any problems pre-ready.
        $this->readyTimer = $this->client->getLoop()->addTimer(30, function(){
            if($this->client->id !== null){
                $this->logger->warning("Client has taken >30s to get ready, How large is your discord server !?  [Create an issue on github is this persists]");
                $this->client->getLoop()->addTimer(30, function(){
                    if($this->thread->getStatus() !== BotThread::STATUS_READY){
                        $this->logger->critical("Client has taken too long to become ready, shutting down.");
                        $this->close();
                    }
                });
            }else{
                //Should never happen unless your internet speed is like <10kb/s
                $this->logger->critical("Client failed to login/connect within 30 seconds, See log file for details.");
                $this->close();
            }
        });

        $this->tickTimer = $this->client->getLoop()->addPeriodicTimer(1/20, function(){
            // Note this is not accurate/fixed dynamically to 1/20th of a second.
            $this->tick();
        });
    }

    /** @noinspection PhpUnusedParameterInspection */
    private function registerHandlers(): void{
        // https://github.com/teamreflex/DiscordPHP/issues/433
        // Note ready is emitted after successful connection + all servers/users loaded, so only register events
        // After this event.
        $this->client->on('ready', function(Discord $discord){
            if($this->readyTimer !== null){
                $this->client->getLoop()->cancelTimer($this->readyTimer);
                $this->readyTimer = null;
            }
            $this->discordEventHandler->onReady();
        });

        $this->client->on('ws_closed', [$this, 'webSocketHandler']);
        $this->client->on('error', [$this, 'discordErrorHandler']);
        $this->client->on('closed', [$this, 'close']);
    }

    public function tick(): void{
        $data = $this->thread->readInboundData($this->config["protocol"]["packets_per_tick"]);

        foreach($data as $d){
            $this->communicationHandler->handle($d);
        }

        if(($this->tickCount % 20) === 0){
            if($this->thread->getStatus() === BotThread::STATUS_READY){
                $this->communicationHandler->checkHeartbeat();
                $this->communicationHandler->sendHeartbeat();
            }

            //GC Tests.
            if(microtime(true)-$this->lastGCCollection >= 6000){
                $cycles = gc_collect_cycles();
                $mem = round(gc_mem_caches()/1024, 3);
                $this->logger->debug("[GC] Claimed {$mem}kb and {$cycles} cycles.");
                $this->lastGCCollection = time();
            }
        }

        $this->tickCount++;
    }

    public function getConfig(): array{
        return $this->config;
    }

    public function getLogger(): Logger{
        return $this->logger;
    }

    public function getThread(): BotThread{
        return $this->thread;
    }

    public function getDiscordClient(): Discord{
        return $this->client;
    }

    public function getCommunicationHandler(): CommunicationHandler{
        return $this->communicationHandler;
    }

    public function sysErrorHandler(int $severity, string $message, string $file, int $line): bool{
        $this->close(new ErrorException($message, 0, $severity, $file, $line));
        return true;
    }

    public function websocketHandler(int $op, string $reason): void{
        switch($op){
            case 4014:
                $this->thread->getLogger()->emergency("Disallowed intents detected, Please follow the wiki provided ".
                    "(https://github.com/DiscordBot-PMMP/DiscordBot/wiki/Creating-your-discord-bot) and ensure both privileged intents are enabled.");
                break;
            case 4013:
                //Should never happen considering were set to a specific version of the gateway
                $this->thread->getLogger()->emergency("Invalid intents specified, Please create a new issue on github ".
                    "(https://github.com/DiscordBot-PMMP/DiscordBot/issues/new) quoting the text `op:4013 - {$reason}`.");
                break;
        }
        if(in_array($op, Op::getCriticalCloseCodes())) {
            $this->close($reason);
        }
    }

    /** @var array $data */
    public function discordErrorHandler(array $data): void{
        $this->close($data[0]??null);
    }

    public function close($error = null): void{ /** @phpstan-ignore-line  */
        if($this->thread->getStatus() === BotThread::STATUS_CLOSED) return;
        $this->thread->setStatus(BotThread::STATUS_CLOSED);
        if($error instanceof Throwable){
            $errorConversion = [
                0 => "EXCEPTION",
                E_ERROR => "E_ERROR",
                E_WARNING => "E_WARNING",
                E_PARSE => "E_PARSE",
                E_NOTICE => "E_NOTICE",
                E_CORE_ERROR => "E_CORE_ERROR",
                E_CORE_WARNING => "E_CORE_WARNING",
                E_COMPILE_ERROR => "E_COMPILE_ERROR",
                E_COMPILE_WARNING => "E_COMPILE_WARNING",
                E_USER_ERROR => "E_USER_ERROR",
                E_USER_WARNING => "E_USER_WARNING",
                E_USER_NOTICE => "E_USER_NOTICE",
                E_STRICT => "E_STRICT",
                E_RECOVERABLE_ERROR => "E_RECOVERABLE_ERROR",
                E_DEPRECATED => "E_DEPRECATED",
                E_USER_DEPRECATED => "E_USER_DEPRECATED"
            ];
            $errno = $errorConversion[$error->getCode()]??$error->getCode();
            $this->logger->critical(get_class($error) . ": \"{$error->getMessage()}\" ({$errno}) in \"{$error->getFile()}\" at line {$error->getLine()}");
            foreach(Utils::printableTrace($error->getTrace()) as $line){
                $this->logger->critical($line);
            }
        }
        if($this->client instanceof Discord){
            try{
                $this->client->close(true);
            }catch (Error $e){
                $this->logger->debug("Failed to close client, probably not started.");
            }
        }
        $this->logger->debug("Client closed.");
        $this->logger->close();
        exit(0);
    }
}