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

namespace JaxkDev\DiscordBot\Plugin;

use JaxkDev\DiscordBot\Communication\BotThread;
use JaxkDev\DiscordBot\Communication\Packets\Packet;
use JaxkDev\DiscordBot\Communication\Protocol;
use JaxkDev\DiscordBot\Plugin\Handlers\PocketMineEventHandler;
use JaxkDev\DiscordBot\Plugin\Handlers\BotCommunicationHandler;
use JaxkDev\DiscordBot\Utils;
use Phar;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\ClosureTask;
use pocketmine\scheduler\TaskHandler;
use pocketmine\utils\MainLogger;
use pocketmine\utils\TextFormat;
use Volatile;

class Main extends PluginBase {

	/** @var BotThread */
	private $discordBot;

	/** @var Volatile */
	private $inboundData, $outboundData;

	/** @var TaskHandler */
	private $tickTask;

	/** @var BotCommunicationHandler */
	private $botCommsHandler;

	/** @var PocketMineEventHandler */
	private $pocketmineEventHandler;

	/** @var array */
	private $eventConfig, $config;

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
		$this->saveResource("HELP_ENG.txt", true); //Always keep that up-to-date.

		$this->inboundData = new Volatile();
		$this->outboundData = new Volatile();

		$this->botCommsHandler = new BotCommunicationHandler($this);
		$this->pocketmineEventHandler = new PocketMineEventHandler($this, yaml_parse_file($this->getDataFolder().DIRECTORY_SEPARATOR."events.yml"));
	}

	public function onEnable(){
		if(!$this->loadConfig()) return;

		$this->getLogger()->debug("Starting DiscordBot Thread...");
		$this->discordBot = new BotThread($this->getServer()->getLogger(), $this->config, $this->outboundData, $this->inboundData);
		$this->discordBot->start(PTHREADS_INHERIT_CONSTANTS);
		unset($this->config);

		$this->getServer()->getPluginManager()->registerEvents($this->pocketmineEventHandler, $this);
		$this->tickTask = $this->getScheduler()->scheduleRepeatingTask(new ClosureTask(function (int $currentTick): void {
			$this->tick($currentTick);
		}), 1);
	}

	public function onDisable(){
		$this->stopAll(false);
	}

	private function loadConfig(): bool{
		$this->getLogger()->debug("Loading initial configuration...");

		$config = yaml_parse_file($this->getDataFolder().DIRECTORY_SEPARATOR."config.yml");
		if($config === false){
			$this->getLogger()->critical("Failed to parse config.yml");
			$this->getServer()->getPluginManager()->disablePlugin($this);
			return false;
		}
		$config = (array)$config;
		$config['logging']['directory'] = $this->getDataFolder().DIRECTORY_SEPARATOR.($initialConfig['logging']['directory'] ?? "logs");

		$this->eventConfig = yaml_parse_file($this->getDataFolder().DIRECTORY_SEPARATOR."events.yml");
		if($this->eventConfig === false){
			$this->getLogger()->critical("Failed to parse events.yml");
			$this->getServer()->getPluginManager()->disablePlugin($this);
			return false;
		}

		if($config['version'] !== ConfigUtils::VERSION){
			$this->getLogger()->info("Updating your config from v{$config['version']} to v" . ConfigUtils::VERSION);
			ConfigUtils::update($config);
			rename($this->getDataFolder() . "config.yml", $this->getDataFolder() . "config.yml.old");
			yaml_emit_file($this->getDataFolder() . "config.yml", $config);
			$this->getLogger()->info("Config updated, old config was saved to '{$this->getDataFolder()}config.yml.old'");
		}

		$this->getLogger()->debug("Verifying configs...");
		$result_raw = ConfigUtils::verify($config);
		if(sizeof($result_raw) !== 0){
			$result = TextFormat::RED."There were some problems with your config.yml, see below:\n".TextFormat::RESET;
			foreach($result_raw as $key => $value){
				$result .= "'{$key}' - {$value}\n";
			}
			$this->getLogger()->error($result);
			$this->getServer()->getPluginManager()->disablePlugin($this);
			return false;
		}

		//Config is now updated and verified.
		$this->config = $config;
		return true;
	}

	public function tick(int $currentTick): void{
		$data = $this->readInboundData(Protocol::PPT);

		/** @var Packet $d */
		foreach($data as $d){
			if(!$this->botCommsHandler->handle($d)){
				MainLogger::getLogger()->debug("Packet ".get_class($d)." [".$d->getUID()."] not handled.");
			}
		}

		if(($currentTick % 20) === 0){
			//Run every second. [Faster/More accurate over bots tick]
			if($this->discordBot->getStatus() === Protocol::THREAD_STATUS_READY) $this->botCommsHandler->checkHeartbeat();
			if($this->discordBot->getStatus() === Protocol::THREAD_STATUS_CLOSED) $this->stopAll();
			$this->botCommsHandler->sendHeartbeat();
		}

		if($this->inboundData->count() > 5000){
			//That's 20MB, Bail and clear all data.
			//Although technically the heartbeat would get backlogged and cause a death event after 5000.
			$this->getLogger()->emergency("Too much data coming in from discord, wiping past 5000 events.");
			$this->inboundData->chunk(5000); /* @phpstan-ignore-line */ // Return and remove (note keys are not changed)
		}

		if($this->outboundData->count() > 5000){
			$this->getLogger()->emergency("Too much data going out, wiping past 5000 events.");
			$this->outboundData->chunk(5000); /* @phpstan-ignore-line */
		}
	}

	private function readInboundData(int $count = 1): array{
		return array_map(function($data){
			/** @var Packet $packet */
			$packet = unserialize($data);
			Utils::assert($packet instanceof Packet);
			return $packet;
		}, /* @phpstan-ignore-line */ $this->inboundData->chunk($count));
	}

	public function writeOutboundData(Packet $packet): void{
		$this->outboundData[] = serialize($packet);
	}

	public function getBotCommunicationHandler(): BotCommunicationHandler{
		return $this->botCommsHandler;
	}

	public function getEventsConfig(): array{
		return $this->eventConfig;
	}

	public function stopAll(bool $stopPlugin = true): void{
		if($this->tickTask !== null){
			if(!$this->tickTask->isCancelled()){
				$this->tickTask->cancel();
			}
		}
		if($this->discordBot !== null){
			//Stopping while bot is not ready causes it to hang.
			$this->discordBot->setStatus(Protocol::THREAD_STATUS_CLOSING);
			$this->discordBot->quit();  // Joins thread (<-- beware) (Right now this forces bot to close)
		}
		if($stopPlugin){
			$this->getServer()->getPluginManager()->disablePlugin($this);
		}
	}
}