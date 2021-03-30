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

/**
 * All events are cancellable [Excluding Ready/Closed event].
 *
 * Cancelling events will result in NO changes to Storage and NO messages sent.
 * We assume if cancelled the responsible plugin is handling it
 *
 * Do note that if the change is not reflected to `Storage` it may result in unforeseen consequences,
 * YOU HAVE BEEN WARNED.
 *
 * Also note cancelling the event DOES NOT cancel the cause, eg `DiscordServerJoined` event if cancelled
 * the bot will still remain in that server, if you want to undo the cause you must do so yourself
 * eg on the `DiscordServerJoined` event you can use the API to leave the server.
 */
class DiscordBotEvent extends PluginEvent{}