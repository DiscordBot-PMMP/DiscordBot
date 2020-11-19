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

use pocketmine\plugin\PluginBase;

class Plugin extends PluginBase {
	/**
	 * @var BotThread
	 */
	private $discordBot;

	public function onLoad(){
		if(!is_dir($this->getDataFolder().DIRECTORY_SEPARATOR."logs")){
			mkdir($this->getDataFolder().DIRECTORY_SEPARATOR."logs");
		}
	}

	public function onEnable() {
		$this->getLogger()->debug("Starting DiscordBot Thread...");
		$initialConfig = [
			'token' => "TOKEN HERE",
			'logDirectory' => $this->getDataFolder().DIRECTORY_SEPARATOR."logs"
		];
		$this->discordBot = new BotThread($this->getServer()->getLogger(), $initialConfig);
	}

	public function onDisable() {
		if($this->discordBot !== null and $this->discordBot->isStarted() and !$this->discordBot->isStopping()){
			$this->discordBot->stop();
		}
	}
}