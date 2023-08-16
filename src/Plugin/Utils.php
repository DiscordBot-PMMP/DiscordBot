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

namespace JaxkDev\DiscordBot\Plugin;

use function intval;
use function floor;
use function strlen;
use function time;
use function preg_match;
use function file_exists;
use function mime_content_type;
use function in_array;
use function file_get_contents;
use function base64_encode;

abstract class Utils{

    public static function getDiscordSnowflakeTimestamp(string $snowflake): int{
        return intval(floor(((intval($snowflake) >> 22) + 1420070400000) / 1000));
    }

    /** Checks a discord snowflake by verifying the timestamp at when it was created. */
    public static function validDiscordSnowflake(string $snowflake): bool{
        $len = strlen($snowflake);
        if($len < 17 or $len > 19) return false;
        $timestamp = self::getDiscordSnowflakeTimestamp($snowflake);
        if($timestamp > time()+86400 or $timestamp <= 1420070400) return false; //+86400 (24h for any timezone problems)
        return true;
    }

    /** Checks a discord image hash by verifying the format. */
    public static function validImageHash(string $hash): bool{
        return preg_match('/^data:(image\/(jpeg|png|gif));base64,([a-zA-Z0-9+\/]+={0,2})$/', $hash) === 1;
    }

    /**
     * Creates an image hash based on discord docs.
     *
     * @param string $file The file path to the image (jpeg/png/gif only)
     *
     * @see https://discord.com/developers/docs/reference#image-data
     */
    public static function imageToHash(string $file): string{
        if(!file_exists($file)){
            throw new \InvalidArgumentException("File does not exist - " . $file);
        }

        $type = mime_content_type($file);
        if($type === false){
            throw new \InvalidArgumentException("Failed to get mime type of file - " . $file);
        }

        if(!in_array($type, ['image/jpeg', 'image/png', 'image/gif'], true)) {
            throw new \InvalidArgumentException("Invalid mime type - " . $type);
        }

        $contents = file_get_contents($file);
        if($contents === false){
            throw new \AssertionError("Failed to read file contents - " . $file);
        }

        return "data:" . $type . ";base64," . base64_encode($contents);
    }
}