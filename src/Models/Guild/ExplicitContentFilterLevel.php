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

namespace JaxkDev\DiscordBot\Models\Guild;

/**
 * @link https://discord.com/developers/docs/resources/guild#guild-object-explicit-content-filter-level
 */
enum ExplicitContentFilterLevel: int{

    /** Media content will not be scanned */
    case DISABLED = 0;

    /** Media content sent by members without roles will be scanned */
    case MEMBERS_WITHOUT_ROLES = 1;

    /** Media content sent by all members will be scanned */
    case ALL_MEMBERS = 2;
}