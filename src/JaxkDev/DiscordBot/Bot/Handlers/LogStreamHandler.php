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

use JaxkDev\DiscordBot\Bot\Pocketmine\Terminal;
use JaxkDev\DiscordBot\Bot\Pocketmine\TextFormat;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\AbstractHandler;
use Monolog\Logger;

/**
 * Sends all messages(aka records) from monolog logger to PocketMines log if running in debug mode.
 */
class LogStreamHandler extends AbstractHandler{

    /** @var LineFormatter */
    private $formatter;

    /** @var bool */
    private $debug;

    public function __construct(bool $debug, $level = Logger::DEBUG, bool $bubble = true){
        $this->debug = $debug;
        $this->formatter = new LineFormatter("%message% %context%");
        Terminal::init();
        parent::__construct($level, $bubble);
    }

    public function close(): void{}

    public function handle(array $record): bool{
        $record['message'] = str_replace(" []\X1","",$this->formatter->format($record)."\X1");

        if($this->debug or in_array(strtolower($record['level_name']), ["warning", "error", "critical", "emergency", "alert"])){
            Terminal::writeLine(TextFormat::AQUA."[".(new \DateTime('now'))->format("H:i:s.v")."] ".
                TextFormat::RED."[Discord thread/".strtoupper($record['level_name']).": ".trim($record['message']));
        }

        return false;
    }
}