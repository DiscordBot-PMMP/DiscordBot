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

namespace JaxkDev\DiscordBot\Bot\Handlers;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\AbstractHandler;
use Monolog\Logger;
use pocketmine\utils\MainLogger;
use pocketmine\utils\TextFormat;

/**
 * Sends all messages(aka records) from monolog logger to PocketMines log if running in debug mode.
 */
class LogStreamHandler extends AbstractHandler{

    const FORMAT = TextFormat::AQUA . "[%s] " . TextFormat::RESET . "%s[%s/%s]: %s" . TextFormat::RESET;

    /** @var MainLogger|null */
    private $logger;

    /** @var LineFormatter */
    private $formatter;

    /** @var bool */
    private $debug;

    public function __construct(MainLogger $logger, bool $debug, $level = Logger::DEBUG, bool $bubble = true){
        $this->debug = $debug;
        $this->logger = $logger;
        $this->formatter = new LineFormatter("%message% %context%");
        parent::__construct($level, $bubble);
    }

    public function close(): void{}

    public function handle(array $record): bool{
        if($this->logger === null) return false;
        $record['message'] = str_replace(" []\X1","",$this->formatter->format($record)."\X1");

        if($this->debug or in_array(strtolower($record['level_name']), ["error", "critical", "emergency", "alert"])){
            $this->logger->log(strtolower($record['level_name']), trim($record['message']));
        }

        return false;
    }
}