<?php

/*
 * DiscordBot, PocketMine-MP Plugin.
 *
 * Licensed under the Open Software License version 3.0 (OSL-3.0)
 * Copyright (C) 2020-present JaxkDev
 *
 * Discord :: JaxkDev
 * Email   :: JaxkDev@gmail.com
 */

namespace JaxkDev\DiscordBot\Models;

/**
 * @link https://discord.com/developers/docs/resources/sticker#sticker-object-sticker-format-types
 */
enum StickerFormatType: int{

    case PNG = 1;
    case APNG = 2;
    case LOTTIE = 3;
    case GIF = 4;
}
