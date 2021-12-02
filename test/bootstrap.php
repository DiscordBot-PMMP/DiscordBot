<?php
/*
 * DiscordBot, PocketMine-MP Plugin.
 *
 * Licensed under the Open Software License version 3.0 (OSL-3.0)
 * Copyright (C) 2020-present JaxkDev
 *
 * Twitter :: @JaxkDev
 * Discord :: JaxkDev#2698
 * Email   :: JaxkDev@gmail.com
 */

/*
 * Constants used in the plugin, value types defined for use with phpstan.
 *
 * Note, changing this file requires you to clear phpstan's result cache before analysing.
 */

define('JaxkDev\DiscordBot\COMPOSER', "");
define('JaxkDev\DiscordBot\VERSION', "");
define('JaxkDev\DiscordBot\DATA_PATH', "");

//JIT Should also be disabled for PHPStan analysis as it hangs on analysis.

//OPCache should also be disabled because of https://github.com/phpstan/phpstan/issues/5503
ini_set('opcache.enable', 'off');