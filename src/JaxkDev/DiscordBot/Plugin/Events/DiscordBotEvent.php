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

use pocketmine\event\plugin\PluginEvent;

/*
 * Some events are cancellable.
 * Cancelled events only means the plugin will NOT send if applicable messages announcing the event.
 * Cancelling these events DOES NOT cancel the cause, eg DiscordServerJoined event if cancelled the bot will still
 * remain in that server if you want to undo the cause you must do so yourself, eg DiscordServerJoined use the API to
 * leave it.
 */

class DiscordBotEvent extends PluginEvent{}