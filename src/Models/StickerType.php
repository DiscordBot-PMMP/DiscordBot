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

namespace JaxkDev\DiscordBot\Models;

/**
 * @link https://discord.com/developers/docs/resources/sticker#sticker-object-sticker-types
 */
enum StickerType: int{

    /** an official sticker in a pack, part of Nitro or in a removed purchasable pack */
    case STANDARD = 1;
    /** a sticker uploaded to a guild for the guild's members */
    case GUILD = 2;
}
