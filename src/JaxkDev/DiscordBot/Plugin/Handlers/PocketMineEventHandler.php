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

namespace JaxkDev\DiscordBot\Plugin\Handlers;

use JaxkDev\DiscordBot\Plugin\Main;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerTransferEvent;
use pocketmine\event\server\CommandEvent;

class PocketMineEventHandler implements Listener{

	/** @var Main */
	private $plugin;

	/** @var array */
	private $eventConfig;

	public function __construct(Main $plugin, array $eventConfig){
		$this->plugin = $plugin;
		$this->eventConfig = $eventConfig;
	}

	/**
	 * @priority MONITOR
	 * @param PlayerJoinEvent $event
	 */
	public function onJoin(PlayerJoinEvent $event): void{
		$config = $this->eventConfig['member_join']['toDiscord'];
		if(count($config['channels']) === 0) return;

		$message = str_replace(['{TIME}', '{USERNAME}'], [date('G:i:s'), $event->getPlayer()->getName()], $config['format']);

		foreach($config['channels'] as $channel){
			$msg = $this->plugin->getAPI()->createMessage($channel, $message);
			if($msg === null) continue;
			$this->plugin->getAPI()->sendMessage($msg);
		}
	}

	/**
	 * @priority MONITOR
	 * @param PlayerQuitEvent $event
	 */
	public function onPlayerQuit(PlayerQuitEvent $event): void{
		$config = $this->eventConfig['member_leave']['toDiscord'];
		if(count($config['channels']) === 0) return;

		$message = str_replace(['{TIME}', '{USERNAME}'], [date('G:i:s'), $event->getPlayer()->getName()], $config['format']);

		foreach($config['channels'] as $channel){
			$msg = $this->plugin->getAPI()->createMessage($channel, $message);
			if($msg === null) continue;
			$this->plugin->getAPI()->sendMessage($msg);
		}
	}

	public function onPlayerTransfer(PlayerTransferEvent $event): void{
		$config = $this->eventConfig['member_transfer']['toDiscord'];
		if(count($config['channels']) === 0) return;

		$message = str_replace(['{TIME}', '{USERNAME}', '{ADDRESS}', '{PORT}'],
			[date('G:i:s'), $event->getPlayer()->getName(), $event->getAddress(), $event->getPort()], $config['format']);

		foreach($config['channels'] as $channel){
			$msg = $this->plugin->getAPI()->createMessage($channel, $message);
			if($msg === null) continue;
			$this->plugin->getAPI()->sendMessage($msg);
		}
	}

	/**
	 * @priority MONITOR
	 * @param PlayerChatEvent $event
	 */
	public function onPlayerChat(PlayerChatEvent $event): void{
		$config = $this->eventConfig['message']['toDiscord'];
		if(count($config['channels']) === 0) return;

		$message = str_replace(['{TIME}', '{USERNAME}', '{MESSAGE}'],
			[date('G:i:s'), $event->getPlayer()->getName(), $event->getMessage()], $config['format']);

		foreach($config['channels'] as $channel){
			$msg = $this->plugin->getAPI()->createMessage($channel, $message);
			if($msg === null) continue;
			$this->plugin->getAPI()->sendMessage($msg);
		}
	}

	/**
	 * @priority MONITOR
	 * @param CommandEvent $event
	 */
	public function onPlayerCommand(CommandEvent $event): void{
		$config = $this->eventConfig['command']['toDiscord'];
		if(count($config['channels']) === 0) return;

		$message = str_replace(['{TIME}', '{USERNAME}', '{COMMAND}'],
			[date('G:i:s'), $event->getSender()->getName(), $event->getCommand()], $config['format']);

		foreach($config['channels'] as $channel){
			$msg = $this->plugin->getAPI()->createMessage($channel, $message);
			if($msg === null) continue;
			$this->plugin->getAPI()->sendMessage($msg);
		}
	}
}