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

namespace JaxkDev\DiscordBot\Communication;

use JaxkDev\DiscordBot\Main;
use pocketmine\scheduler\Task;

class PluginTickTask extends Task {

	/**
	 * @var Main
	 */
	private $plugin;

	public function __construct(Main $plugin){
		$this->plugin = $plugin;
	}

	public function onRun(int $currentTick) {
		$data = $this->plugin->readInboundData();
		if($data !== null){
			var_dump("Got data - TickTask Plugin");
			var_dump($data);
		}
		// Stress Test, run at your own risk...
		// for($i = 0; $i < 100; $i++) $this->plugin->writeOutboundData([0,[str_repeat("S", 20000)]]);
	}
}