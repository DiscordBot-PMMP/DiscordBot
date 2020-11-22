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

use Phar;
use pocketmine\plugin\PluginBase;
use JaxkDev\DiscordBot\Communication\BotThread;

class Plugin extends PluginBase {
	/**
	 * @var BotThread
	 */
	private $discordBot;

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

		$this->discordBot = new BotThread($this->getServer()->getLogger(), $config);
	}

	public function onEnable() {
		$this->getLogger()->debug("Starting DiscordBot Thread...");
		$this->discordBot->start(PTHREADS_INHERIT_CONSTANTS);
	}

	public function onDisable() {
		if($this->discordBot !== null and $this->discordBot->isStarted() and !$this->discordBot->isStopping()){
			$this->discordBot->stop();
		}
	}
}