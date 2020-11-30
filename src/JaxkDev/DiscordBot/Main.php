<?php
/*
 * DiscordBot, PocketMine-MP Plugin.
 *
 * Licensed under the Open Software License version 3.0 (OSL-3.0)
 * Copyright (C) 2020 JaxkDev
 *
 * Twitter :: @JaxkDev
 * Discord :: JaxkDev#2698
 * Email   :: JaxkDev@gmail.com
 */

namespace JaxkDev\DiscordBot;

use JaxkDev\DiscordBot\Communication\BotThread;
use JaxkDev\DiscordBot\Communication\Protocol;
use JaxkDev\DiscordBot\Plugin\Handlers\PocketMineEventHandler;
use JaxkDev\DiscordBot\Plugin\PluginTickTask;
use JaxkDev\DiscordBot\Plugin\Handlers\BotCommunicationHandler;
use Phar;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\TaskHandler;
use Volatile;

class Main extends PluginBase {
	/**
	 * @var BotThread
	 */
	private $discordBot;

	/**
	 * @var Volatile
	 */
	private $inboundData, $outboundData;

	/**
	 * @var TaskHandler
	 */
	private $tickTask;

	/**
	 * @var BotCommunicationHandler
	 */
	private $botCommsHandler;

	/**
	 * @var PocketMineEventHandler
	 */
	private $pocketmineEventHandler;

	/**
	 * @var array
	 */
	private $eventConfig;

	public function onLoad(){
		if(!defined('JaxkDev\DiscordBot\COMPOSER')){
			define("JaxkDev\DiscordBot\VERSION", "v".$this->getDescription()->getVersion());
			define('JaxkDev\DiscordBot\COMPOSER', (Phar::running(true) !== "") ? Phar::running(true) . "/vendor/autoload.php" : dirname(__DIR__, 4) . "/DiscordBot/vendor/autoload.php");
		}

		if(!is_dir($this->getDataFolder().DIRECTORY_SEPARATOR."logs")){
			mkdir($this->getDataFolder().DIRECTORY_SEPARATOR."logs");
		}

		$this->saveResource("config.yml");
		$this->saveResource("events.yml");
		$this->saveResource("discord_commands.yml");

		$this->getLogger()->debug("Loading initial configuration...");

		$config = yaml_parse_file($this->getDataFolder().DIRECTORY_SEPARATOR."config.yml");
		if($config === false){
			$this->getLogger()->critical("Failed to parse config.yml");
			$this->getServer()->getPluginManager()->disablePlugin($this);
		}
		// TODO Verify Config before using it.
		$config['logging']['directory'] = $this->getDataFolder().DIRECTORY_SEPARATOR.($initialConfig['logging']['directory'] ?? "logs");

		$this->eventConfig = yaml_parse_file($this->getDataFolder().DIRECTORY_SEPARATOR."events.yml");
		if($this->eventConfig === false){
			$this->getLogger()->critical("Failed to parse events.yml");
			$this->getServer()->getPluginManager()->disablePlugin($this);
		}

		$this->getLogger()->debug("Constructing DiscordBot...");

		$this->inboundData = new Volatile();
		$this->outboundData = new Volatile();

		$this->botCommsHandler = new BotCommunicationHandler($this);
		$this->pocketmineEventHandler = new PocketMineEventHandler($this, yaml_parse_file($this->getDataFolder().DIRECTORY_SEPARATOR."events.yml"));
		$this->discordBot = new BotThread($this->getServer()->getLogger(), $config, $this->outboundData, $this->inboundData);
	}

	public function onEnable() {
		$this->getLogger()->debug("Starting DiscordBot Thread...");
		$this->discordBot->start(PTHREADS_INHERIT_CONSTANTS);

		$this->getServer()->getPluginManager()->registerEvents($this->pocketmineEventHandler, $this);
		$this->tickTask = $this->getScheduler()->scheduleRepeatingTask(new PluginTickTask($this), 1);
	}

	public function onDisable() {
		$this->stopAll(false);
	}

	public function tick(int $currentTick): void{
		$data = $this->readInboundData(Protocol::PPT);

		foreach($data as $d) $this->botCommsHandler->handle($d);

		if(($currentTick % 20) === 0){
			//Run every second.
			if($this->discordBot->getStatus() === Protocol::THREAD_STATUS_READY) $this->botCommsHandler->checkHeartbeat();
			if($this->discordBot->getStatus() === Protocol::THREAD_STATUS_CLOSED) $this->stopAll();
			$this->botCommsHandler->sendHeartbeat();
		}

		if($this->inboundData->count() > 5000){
			//That's 20MB, Bail and clear all data.
			//Although technically the heartbeat would get backlogged and cause a death event after 5000.
			$this->getLogger()->emergency("Too much data coming in from discord, wiping past 5000 events.");
			$this->inboundData->chunk(5000);  // Return and remove (note keys are not changed)
		}

		if($this->outboundData->count() > 5000){
			$this->getLogger()->emergency("Too much data going out, wiping past 5000 events.");
			$this->outboundData->chunk(5000);
		}
	}

	public function readInboundData(int $count = 1): array{
		return $this->inboundData->chunk($count);
	}

	public function writeOutboundData(int $id, array $data): void{
		$this->outboundData[] = (array)[$id, $data];
	}

	public function getBotCommunicationHandler(): BotCommunicationHandler{
		return $this->botCommsHandler;
	}

	public function getEventsConfig(): array{
		return $this->eventConfig;
	}

	public function stopAll(bool $stopPlugin = true): void{
		if(!$this->tickTask->isCancelled()){
			$this->tickTask->cancel();
		}
		if($this->discordBot !== null){
			$this->discordBot->setStatus(Protocol::THREAD_STATUS_CLOSING);
			$this->discordBot->quit();  // Joins thread (<-- beware) (Right now this forces bot to close)
		}
		if($stopPlugin){
			$this->getServer()->getPluginManager()->disablePlugin($this);
		}
	}
}