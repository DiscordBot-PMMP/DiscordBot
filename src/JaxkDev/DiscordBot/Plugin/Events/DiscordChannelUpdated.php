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

namespace JaxkDev\DiscordBot\Plugin\Events;

use JaxkDev\DiscordBot\Communication\Models\Channel;
use JaxkDev\DiscordBot\Communication\Models\Server;
use JaxkDev\DiscordBot\Plugin\Storage;
use pocketmine\event\Cancellable;
use pocketmine\plugin\Plugin;

/**
 * Emitted when a channel gets updated.
 * @see DiscordChannelDeleted
 * @see DiscordChannelCreated
 */
class DiscordChannelUpdated extends DiscordBotEvent implements Cancellable{

	/** @var Channel */
	private $channel;

	/** @var Server */
	private $server;

	public function __construct(Plugin $plugin, Channel $channel){
		parent::__construct($plugin);
		$this->channel = $channel;
		$s = Storage::getServer($channel->getServerId());
		if($s === null){
			throw new \AssertionError("No server found in storage ({$channel->getServerId()}) for channel '{$channel->getId()}'");
		}else{
			$this->server = $s;
		}
	}

	public function getChannel(): Channel{
		return $this->channel;
	}

	public function getServer(): Server{
		return $this->server;
	}
}