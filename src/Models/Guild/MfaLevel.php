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

/** @link https://discord.com/developers/docs/resources/guild#guild-object-mfa-level */
enum MfaLevel: int{

    /** Guild has no MFA/2FA requirement for moderation actions */
    case NONE = 0;

    /** Guild has a 2FA requirement for moderation actions */
    case ELEVATED = 1;
}