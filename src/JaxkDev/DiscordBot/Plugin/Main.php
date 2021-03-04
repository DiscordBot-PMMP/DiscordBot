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

namespace JaxkDev\DiscordBot\Plugin;

use JaxkDev\DiscordBot\Communication\BotThread;
use JaxkDev\DiscordBot\Communication\Packets\Packet;
use JaxkDev\DiscordBot\Communication\Protocol;
use JaxkDev\DiscordBot\Plugin\Events\DiscordClosed;
use JaxkDev\DiscordBot\Plugin\Handlers\PocketMineEventHandler;
use JaxkDev\DiscordBot\Plugin\Handlers\BotCommunicationHandler;
use Phar;
use pocketmine\plugin\PluginBase;
use pocketmine\plugin\PluginException;
use pocketmine\scheduler\ClosureTask;
use pocketmine\scheduler\TaskHandler;
use pocketmine\utils\MainLogger;
use pocketmine\utils\TextFormat;
use Volatile;

class Main extends PluginBase{

	/** @var Main */
	static private $instance;

	/** @var BotThread */
	private $discordBot;

	/** @var Volatile */
	private $inboundData;
	/** @var Volatile */
	private $outboundData;

	/** @var TaskHandler */
	private $tickTask;

	/** @var BotCommunicationHandler */
	private $botCommsHandler;

	/** @var PocketMineEventHandler */
	private $pocketmineEventHandler;

	/** @var Api */
	private $api;

	/** @var array */
	private $eventConfig;
	/** @var array */
	private $config;

	public function onLoad(){
		self::$instance = $this;
		if(Phar::running(true) === ""){
			throw new PluginException("Cannot be run from source.");
		}
		if(PHP_VERSION_ID < 70400){
			//Hopefully temporary, https://github.com/pmmp/PocketMine-MP/pull/3960
			throw new PluginException("Must be run with PHP 7.4+ or 8.0.3+");
		}

		if(!defined("JaxkDev\DiscordBot\COMPOSER")){
			define("JaxkDev\DiscordBot\DATA_PATH", $this->getDataFolder());
			define("JaxkDev\DiscordBot\VERSION", "v".$this->getDescription()->getVersion());
			define("JaxkDev\DiscordBot\COMPOSER", Phar::running(true)."/vendor/autoload.php");
		}
		if (!function_exists('JaxkDev\DiscordBot\Libs\React\Promise\resolve')) {
			/** @noinspection PhpIncludeInspection */
			require Phar::running(true).'/src/JaxkDev/DiscordBot/Libs/React/Promise/functions.php';
		}

		if(!is_dir($this->getDataFolder()."logs")) mkdir($this->getDataFolder()."logs");

		$this->saveResource("config.yml");
		$this->saveResource("events.yml");
		$this->saveResource("HELP_ENG.txt", true); //Always keep that up-to-date.
		$this->saveResource("cacert.pem", true); //And this ^

		$this->inboundData = new Volatile();
		$this->outboundData = new Volatile();
	}

	public function onEnable(){
		if(!$this->loadConfig()) return;
		if(extension_loaded("xdebug")){
			if(ini_get("xdebug.output_dir") === $this->getDataFolder()){
				$this->getLogger()->warning("X-Debug is running, this will cause data pack to be ~3min long.");
			}else{
				$this->getLogger()->emergency("Plugin will not run with xdebug due to the performance drops.");
				$this->getServer()->getPluginManager()->disablePlugin($this);
				return;
			}
		}

		$this->api = new Api($this);
		$this->botCommsHandler = new BotCommunicationHandler($this);
		$this->pocketmineEventHandler = new PocketMineEventHandler($this, $this->eventConfig);

		$this->getLogger()->debug("Starting DiscordBot Thread...");
		$this->discordBot = new BotThread($this->getServer()->getLogger(), $this->config, $this->outboundData, $this->inboundData);
		$this->discordBot->start(PTHREADS_INHERIT_CONSTANTS);
		unset($this->config);

		$this->getServer()->getPluginManager()->registerEvents($this->pocketmineEventHandler, $this);
		$this->tickTask = $this->getScheduler()->scheduleRepeatingTask(new ClosureTask(function (int $currentTick): void{
			$this->tick($currentTick);
		}), 1);
	}

	public function onDisable(){
		$this->stopAll(false);
	}

	private function loadConfig(): bool{
		$this->getLogger()->debug("Loading initial configuration...");

		$config = yaml_parse_file($this->getDataFolder()."config.yml");
		if($config === false or !is_int($config["version"]??"")){
			$this->getLogger()->critical("Failed to parse config.yml");
			$this->getServer()->getPluginManager()->disablePlugin($this);
			return false;
		}

		$eventConfig = yaml_parse_file($this->getDataFolder()."events.yml");
		if($eventConfig === false or !is_int($eventConfig["version"]??"")){
			$this->getLogger()->critical("Failed to parse events.yml");
			$this->getServer()->getPluginManager()->disablePlugin($this);
			return false;
		}

		if($config["version"] !== ConfigUtils::VERSION){
			$this->getLogger()->info("Updating your config from v{$config["version"]} to v".ConfigUtils::VERSION);
			ConfigUtils::update($config);
			rename($this->getDataFolder()."config.yml", $this->getDataFolder()."config.yml.old");
			yaml_emit_file($this->getDataFolder()."config.yml", $config);
			$this->getLogger()->info("Config updated, old config was saved to '{$this->getDataFolder()}config.yml.old'");
		}

		if($eventConfig["version"] !== ConfigUtils::EVENT_VERSION){
			$this->getLogger()->info("Updating your event config from v{$eventConfig["version"]} to v".ConfigUtils::EVENT_VERSION);
			ConfigUtils::update_event($eventConfig);
			rename($this->getDataFolder()."events.yml", $this->getDataFolder()."events.yml.old");
			yaml_emit_file($this->getDataFolder()."events.yml", $eventConfig);
			$this->getLogger()->info("Event config updated, old event config was saved to '{$this->getDataFolder()}events.yml.old'");
		}

		$this->getLogger()->debug("Verifying config...");
		$result_raw = ConfigUtils::verify($config);
		if(sizeof($result_raw) !== 0){
			$result = TextFormat::RED."There were some problems with your config.yml, see below:\n".TextFormat::RESET;
			foreach($result_raw as $value){
				$result .= "{$value}\n";
			}
			$this->getLogger()->error(rtrim($result));
			$this->getServer()->getPluginManager()->disablePlugin($this);
			return false;
		}
		$result_raw = ConfigUtils::verify_event($eventConfig);
		if(sizeof($result_raw) !== 0){
			$result = TextFormat::RED."There were some problems with your events.yml, see below:\n".TextFormat::RESET;
			foreach($result_raw as $value){
				$result .= "{$value}\n";
			}
			$this->getLogger()->error(rtrim($result));
			$this->getServer()->getPluginManager()->disablePlugin($this);
			return false;
		}

		//Config is now updated and verified.
		$this->config = $config;
		$this->eventConfig = $eventConfig;
		return true;
	}

	private function tick(int $currentTick): void{
		$data = $this->readInboundData(Protocol::PPT);

		/** @var Packet $d */
		foreach($data as $d){
			if(!$this->botCommsHandler->handle($d)){
				MainLogger::getLogger()->debug("Packet ".get_class($d)." [".$d->getUID()."] not handled.");
			}
		}

		if(($currentTick % 20) === 0){
			//Run every second. [Faster/More accurate over bots tick]
			if($this->discordBot->getStatus() === Protocol::THREAD_STATUS_READY){
				$this->botCommsHandler->checkHeartbeat();
				$this->botCommsHandler->sendHeartbeat();
			}
			if($this->discordBot->getStatus() === Protocol::THREAD_STATUS_CLOSED){
				$this->stopAll();
			}
		}

		if($this->inboundData->count() > 2000){
			$this->getLogger()->emergency("Too much data coming in from discord, stopping plugin+thread.  (If this issue persists, contact JaxkDev)");
			$this->stopAll();
		}

		if($this->outboundData->count() > 2000){
			$this->getLogger()->emergency("Too much data going out, stopping plugin+thread.  (If this issue persists, contact JaxkDev)");
			$this->stopAll();
		}
	}

	private function readInboundData(int $count = 1): array{
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

	public function getBotCommunicationHandler(): BotCommunicationHandler{
		return $this->botCommsHandler;
	}

	public function getEventsConfig(): array{
		return $this->eventConfig;
	}

	public function getApi(): Api{
		return $this->api;
	}

	public static function getInstance(): Main{
		return self::$instance;
	}

	public function stopAll(bool $stopPlugin = true): void{
		if($this->tickTask !== null){
			if(!$this->tickTask->isCancelled()){
				$this->tickTask->cancel();
			}
		}
		if($this->discordBot !== null){
			//Stopping while bot is not ready (midway through data dump) causes it to wait.
			$this->discordBot->setStatus(Protocol::THREAD_STATUS_CLOSING);
			$this->discordBot->quit();  // Joins thread (<-- beware) (Right now this forces bot to close)
			(new DiscordClosed($this))->call();
		}
		if($stopPlugin){
			$this->getServer()->getPluginManager()->disablePlugin($this);
		}
	}
}