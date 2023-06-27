<?php
/*
 * DiscordBot, PocketMine-MP Plugin.
 *
 * Licensed under the Open Software License version 3.0 (OSL-3.0)
 * Copyright (C) 2020-present JaxkDev
 *
 * Twitter :: @JaxkDev
 * Discord :: JaxkDev
 * Email   :: JaxkDev@gmail.com
 */

namespace JaxkDev\DiscordBot\Plugin\Events;

/**
 * DiscordBot has connected, and we are now in contact with discord.
 *
 * Storage has been populated with initial data but bans + invites may not yet be added,
 * suggest waiting a few seconds before using bans/invite data if any.
 *
 * @see DiscordClosed Emitted when DiscordBot disconnects.
 */
class DiscordReady extends DiscordBotEvent{}