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

namespace JaxkDev\DiscordBot\Bot;

use Discord\Exceptions\IntentException;
use Discord\WebSockets\Intents;
use Discord\WebSockets\Op;
use Error;
use ErrorException;
use JaxkDev\DiscordBot\Bot\Handlers\DiscordEventHandler;
use JaxkDev\DiscordBot\Bot\Handlers\CommunicationHandler;
use JaxkDev\DiscordBot\Communication\BotThread;
use JaxkDev\DiscordBot\Communication\Packets\Packet;
use Monolog\Level as LoggerLevel;
use Monolog\Logger;
use Monolog\Handler\RotatingFileHandler;
use React\EventLoop\TimerInterface;
use Throwable;

class Client{

    private BotThread $thread;

    private Discord $client;

    private Logger $logger;

    private CommunicationHandler $communicationHandler;

    private DiscordEventHandler $discordEventHandler;

    private ?TimerInterface $readyTimer;

    private int $tickCount = 0;

    private int $lastGCCollection = 0;

    private array $config;

    public function __construct(BotThread $thread, array $config){
        $this->thread = $thread;
        $this->config = $config;

        gc_enable();

        error_reporting(E_ALL & ~E_NOTICE);
        set_error_handler([$this, 'sysErrorHandler']);
        set_exception_handler([$this, 'close']);
        register_shutdown_function([$this, 'close']);

        // Mono logger can have issues with other timezones, for now use UTC.
        // Must be set globally due to internal methods in the rotating file handler.
        // Note, this does not affect outside thread config.
        ini_set("date.timezone", "UTC");

        Packet::$UID_COUNT = 1;

        $this->logger = new Logger('DiscordThread');
        $handler = new RotatingFileHandler(\JaxkDev\DiscordBot\DATA_PATH.$config['logging']['directory'].DIRECTORY_SEPARATOR."DiscordBot.log", $config['logging']['max_files'], LoggerLevel::Debug);
        $handler->setFilenameFormat('{filename}-{date}', 'Y-m-d');
        $this->logger->setHandlers(array($handler));

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
        // Saves time & will guarantee closure ASAP rather than waiting in line through ^
        $this->client->getLoop()->addPeriodicTimer(1, function(){
            if($this->thread->getStatus() === BotThread::STATUS_CLOSING){
                $this->close();
            }
        });

        // Handles any problems pre-ready.
        $this->readyTimer = $this->client->getLoop()->addTimer(30, function(){
            if($this->client->id !== null){
                $this->logger->warning("Client has taken >30s to get ready, How large is your discord guild !?  [Create an issue on github is this persists]");
                $this->client->getLoop()->addTimer(30, function(){
                    if($this->thread->getStatus() !== BotThread::STATUS_READY){
                        $this->logger->critical("Client has taken too long to become ready (>60s), shutting down.");
                        $this->close();
                    }
                });
            }else{
                $this->logger->critical("Client failed to login/connect within 30 seconds, See log file for details.");
                $this->close();
            }
        });

        $this->client->getLoop()->addPeriodicTimer(1/20, function(){
            // Note this is not accurate/fixed dynamically to 1/20th of a second.
            $this->tick();
        });
    }

    /** @noinspection PhpUnusedParameterInspection */
    private function registerHandlers(): void{
        // Note init is emitted after successful connection + all guilds/users loaded, so only register events
        // After this event.
        $this->client->on('init', function(Discord $discord){
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
            case Op::CLOSE_DISALLOWED_INTENTS:
                $this->logger->emergency("Disallowed intents detected, Please follow the wiki provided ".
                    "(https://github.com/DiscordBot-PMMP/DiscordBot/wiki/Creating-your-discord-bot) and ensure both privileged intents are enabled.");
                break;
            case Op::CLOSE_INVALID_TOKEN:
                $this->logger->emergency("Invalid token, rejected by discord , Please follow the wiki provided ".
                    "(https://github.com/DiscordBot-PMMP/DiscordBot/wiki/Creating-your-discord-bot).");
                break;
            case Op::CLOSE_INVALID_INTENTS:
                //Should never happen considering were set to a specific version of the gateway
                $this->logger->emergency("Invalid intents specified, Please create a new issue on github ".
                    "(https://github.com/DiscordBot-PMMP/DiscordBot/issues/new) quoting the text `op:4013 - {$reason}`.");
                break;
        }
        if(in_array($op, Op::getCriticalCloseCodes(), true)) {
            $this->close($reason);
        }
    }

    public function discordErrorHandler(array $data): void{
        $this->close($data[0]??null);
    }

    /** @noinspection PhpConditionAlreadyCheckedInspection */
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
            $this->logger->critical(get_class($error) . ": \"{$error->getMessage()}\" (".strval($errno).") in \"{$error->getFile()}\" at line {$error->getLine()}");
            foreach(self::printableTrace($error->getTrace()) as $line){
                $this->logger->critical($line);
            }
        }
        try{
            $this->client?->close();
        }catch (Error $e){
            $this->logger->debug("Failed to close client, probably not started. ({$e->getMessage()})");
        }
        $this->logger->notice("Discord thread closed.");
        $this->logger->close();
        exit(0);
    }

    private static function cleanPath(string $path): string{
        $result = str_replace([DIRECTORY_SEPARATOR, ".php", "phar://"], ["/", "", ""], $path);
        $cleanPath = rtrim(str_replace([DIRECTORY_SEPARATOR, "phar://"], ["/", ""], \pocketmine\PATH), "/");
        return str_starts_with($result, $cleanPath) ? ltrim(str_replace($cleanPath, "pmsrc", $result), "/") : $result;
    }

    /**
     * PocketMine's prinatableTrace function.
     *
     * @param mixed[][] $trace
     * @phpstan-param list<array<string, mixed>> $trace
     *
     * @return string[]
     */
    private static function printableTrace(array $trace, int $maxStringLength = 80) : array{
        $messages = [];
        for($i = 0; isset($trace[$i]); ++$i){
            $params = "";
            if(isset($trace[$i]["args"]) or isset($trace[$i]["params"])){
                if(isset($trace[$i]["args"])){
                    $args = $trace[$i]["args"];
                }else{
                    $args = $trace[$i]["params"];
                }
                if(!is_array($args)){
                    $args = [$args];
                }

                $params = implode(", ", array_map(function($value) use($maxStringLength) : string{
                    if(is_object($value)){
                        try{
                            $reflect = new \ReflectionClass($value);
                            if($reflect->isAnonymous()){
                                $name = "anonymous@" . ($reflect->getFileName() !== false ?
                                        self::cleanPath($reflect->getFileName()) . "#L" . $reflect->getStartLine() :
                                        "internal"
                                    );
                            }else{
                                $name = $reflect->getName();
                            }
                        }/** @noinspection PhpUnusedLocalVariableInspection */catch(\ReflectionException $e){
                            $name = "Unknown";
                        }
                        return "object " . $name . "#" . spl_object_id($value);
                    }
                    if(is_array($value)){
                        return "array[" . count($value) . "]";
                    }
                    if(is_string($value)){
                        return "string[" . strlen($value) . "] " . substr((preg_replace('#([^\x20-\x7E])#', '.', $value)??""), 0, $maxStringLength);
                    }
                    if(is_bool($value)){
                        return $value ? "true" : "false";
                    }
                    return gettype($value) . " " . (preg_replace('#([^\x20-\x7E])#', '.', strval($value))??"");
                }, $args));
            }
            $messages[] = "#$i " . ((isset($trace[$i]["file"]) && is_string($trace[$i]["file"])) ? self::cleanPath($trace[$i]["file"]) : "") . "(" . (isset($trace[$i]["line"]) ? $trace[$i]["line"] : "") . "): " . (isset($trace[$i]["class"]) ? $trace[$i]["class"] . (($trace[$i]["type"] === "dynamic" or $trace[$i]["type"] === "->") ? "->" : "::") : "") . $trace[$i]["function"] . "(" . (preg_replace('#([^\x20-\x7E])#', '.', $params)??"") . ")";
        }
        return $messages;
    }
}