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

	public function onLoad(){
		if(!defined('JaxkDev\DiscordBot\COMPOSER')){
			define("JaxkDev\DiscordBot\VERSION", "v".$this->getDescription()->getVersion());
			define('JaxkDev\DiscordBot\COMPOSER', (Phar::running(true) !== "") ? Phar::running(true) . "/vendor/autoload.php" : dirname(__DIR__, 4) . "/DiscordBot/vendor/autoload.php");
		}

		if(!is_dir($this->getDataFolder().DIRECTORY_SEPARATOR."logs")){
			mkdir($this->getDataFolder().DIRECTORY_SEPARATOR."logs");
		}

		$this->saveResource("config.yml");

		$this->getLogger()->debug("Loading initial configuration...");

		$config = yaml_parse_file($this->getDataFolder().DIRECTORY_SEPARATOR."config.yml");
		if($config === false){
			$this->getLogger()->critical("Failed to parse config.yml");
			$this->getServer()->getPluginManager()->disablePlugin($this);
		}
		// TODO Verify Config before using it.
		$config['logging']['directory'] = $this->getDataFolder().DIRECTORY_SEPARATOR.($initialConfig['logging']['directory'] ?? "logs");

		$this->getLogger()->debug("Constructing DiscordBot...");

		$this->inboundData = new Volatile();
		$this->outboundData = new Volatile();

		$this->botCommsHandler = new BotCommunicationHandler($this);
		$this->discordBot = new BotThread($this->getServer()->getLogger(), $config, $this->outboundData, $this->inboundData);
	}

	public function tick(int $currentTick): void{
		$data = $this->readInboundData();
		$count = 0;
		while($data !== null and $count < 20){
			$this->botCommsHandler->handle($data);
			$data = $this->readInboundData();
			$count++;
		}

		if(($currentTick % 20) === 0){
			//Run every second.
			if($this->discordBot->getStatus() === BotThread::STATUS_READY) $this->botCommsHandler->checkHeartbeat();
			if($this->discordBot->getStatus() === BotThread::STATUS_CLOSED) $this->stopAll();
			$this->botCommsHandler->sendHeartbeat();
		}
	}

	public function readInboundData(): ?array{
		return $this->inboundData->shift();
	}

	public function writeOutboundData(int $id, array $data): void{
		$this->outboundData[] = (array)[$id, $data];
	}

	public function onEnable() {
		$this->getLogger()->debug("Starting DiscordBot Thread...");
		$this->discordBot->start(PTHREADS_INHERIT_CONSTANTS);

		$this->tickTask = $this->getScheduler()->scheduleRepeatingTask(new PluginTickTask($this), 1);
	}

	public function onDisable() {
		$this->stopAll(false);
	}

	public function stopAll(bool $stopPlugin = true) {
		if(!$this->tickTask->isCancelled()){
			$this->tickTask->cancel();
		}
		if($this->discordBot !== null){
			$this->discordBot->setStatus(BotThread::STATUS_CLOSED);
			$this->discordBot->quit();  // Joins thread (<-- beware)
			$this->discordBot = null;
		}
		if($stopPlugin){
			$this->getServer()->getPluginManager()->disablePlugin($this);
		}
	}
}