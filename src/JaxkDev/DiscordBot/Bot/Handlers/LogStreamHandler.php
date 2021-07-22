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

use Monolog\Handler\AbstractHandler;
use Monolog\Logger;
use pocketmine\utils\MainLogger;
use pocketmine\utils\TextFormat;

/**
 * Sends *all* messages(aka records) from monolog logger to PocketMines log.
 */
class LogStreamHandler extends AbstractHandler{

	const FORMAT = TextFormat::AQUA . "[%s] " . TextFormat::RESET . "%s[%s/%s]: %s" . TextFormat::RESET;

	/** @var MainLogger|null */
	private $logger;

	/** @var bool */
	private $debug;

	public function __construct(MainLogger $logger, bool $debug, $level = Logger::DEBUG, bool $bubble = true){
		//This is used so we can write to log file safely but without outputting to console because DiscordPHP can spam the shit out of info's etc.
		if(!$debug){
			try{
				(new \ReflectionProperty($logger, "logStream"))->setAccessible(true);
			}catch(\Exception $e){
				$logger->critical("Failed to make log stream accessible, logs will not be stored in server.log");
				$logger = null;
			}
		}
		$this->debug = $debug;
		$this->logger = $logger;
		parent::__construct($level, $bubble);
	}

	public function close(): void{}

	public function handle(array $record): bool{
		if($this->logger === null) return false;

		//TODO 'context'
		if(!$this->debug){
			$message = sprintf(self::FORMAT, $record["datetime"]->format("H:i:s"), "", "Discord thread", $record['level_name'], TextFormat::clean($record['message'], false));
			/** @phpstan-ignore-next-line */
			$this->logger->logStream[] = $record["datetime"]->format("Y-m-d")." ".TextFormat::clean($message).PHP_EOL;
			$this->logger->syncFlushBuffer();
		}else{
			$this->logger->log(strtolower($record['level_name']), trim($record['message']));
		}

		return true;
	}
}