<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 * file directly from https://github.com/pmmp/PocketMine-MP/blob/dca5a9d8ea13117d9eef350500e28ec77187b4a3/src/utils/Utils.php
 * with some modifications
 *
*/

declare(strict_types=1);

/**
 * Various Utilities used around the code
 */

namespace JaxkDev\DiscordBot\Bot\Pocketmine;

use function array_map;
use function array_reverse;
use function array_values;
use function bin2hex;
use function chunk_split;
use function count;
use function dechex;
use function file_exists;
use function function_exists;
use function gettype;
use function implode;
use function is_array;
use function is_bool;
use function is_object;
use function is_string;
use function mb_check_encoding;
use function ord;
use function php_uname;
use function preg_replace;
use function spl_object_id;
use function str_pad;
use function str_split;
use function stripos;
use function strlen;
use function substr;
use function xdebug_get_function_stack;
use const PHP_EOL;
use const STR_PAD_LEFT;
use const STR_PAD_RIGHT;

/**
 * Big collection of functions
 */
final class Utils{
    public const OS_WINDOWS = "win";
    public const OS_IOS = "ios";
    public const OS_MACOS = "mac";
    public const OS_ANDROID = "android";
    public const OS_LINUX = "linux";
    public const OS_BSD = "bsd";
    public const OS_UNKNOWN = "other";

    /** @var string|null */
    private static $os;

    /**
     * Returns a readable identifier for the class of the given object. Sanitizes class names for anonymous classes
     */
    public static function getNiceClassName(object $obj) : string{
        $reflect = new \ReflectionClass($obj);
        if($reflect->isAnonymous()){
            $filename = $reflect->getFileName();

            return "anonymous@" . ($filename !== false ?
                    Filesystem::cleanPath($filename) . "#L" . $reflect->getStartLine() :
                    "internal"
                );
        }

        return $reflect->getName();
    }

    /**
     * @phpstan-return \Closure(object) : object
     */
    public static function cloneCallback() : \Closure{
        return static function(object $o){
            return clone $o;
        };
    }

    /**
     * Returns the current Operating System
     * Windows => win
     * MacOS => mac
     * iOS => ios
     * Android => android
     * Linux => Linux
     * BSD => bsd
     * Other => other
     */
    public static function getOS(bool $recalculate = false) : string{
        if(self::$os === null or $recalculate){
            $uname = php_uname("s");
            if(stripos($uname, "Darwin") !== false){
                if(str_starts_with(php_uname("m"), "iP")){
                    self::$os = self::OS_IOS;
                }else{
                    self::$os = self::OS_MACOS;
                }
            }elseif(stripos($uname, "Win") !== false or $uname === "Msys"){
                self::$os = self::OS_WINDOWS;
            }elseif(stripos($uname, "Linux") !== false){
                if(@file_exists("/system/build.prop")){
                    self::$os = self::OS_ANDROID;
                }else{
                    self::$os = self::OS_LINUX;
                }
            }elseif(stripos($uname, "BSD") !== false or $uname === "DragonFly"){
                self::$os = self::OS_BSD;
            }else{
                self::$os = self::OS_UNKNOWN;
            }
        }

        return self::$os;
    }

    /**
     * Returns a prettified hexdump
     */
    public static function hexdump(string $bin) : string{
        $output = "";
        $bin = str_split($bin, 16);
        foreach($bin as $counter => $line){
            $hex = chunk_split(chunk_split(str_pad(bin2hex($line), 32, " ", STR_PAD_RIGHT), 2, " "), 24, " ");
            $ascii = preg_replace('#([^\x20-\x7E])#', ".", $line);
            $output .= str_pad(dechex($counter << 4), 4, "0", STR_PAD_LEFT) . "  " . $hex . " " . $ascii . PHP_EOL;
        }

        return $output;
    }

    /**
     * Returns a string that can be printed, replaces non-printable characters
     */
    public static function printable(mixed $str) : string{
        if(!is_string($str)){
            return gettype($str);
        }

        return preg_replace('#([^\x20-\x7E])#', '.', $str)??"";
    }

    public static function javaStringHash(string $string) : int{
        $hash = 0;
        for($i = 0, $len = strlen($string); $i < $len; $i++){
            $ord = ord($string[$i]);
            if(($ord & 0x80) !== 0){
                $ord -= 0x100;
            }
            $hash = 31 * $hash + $ord;
            while($hash > 0x7FFFFFFF){
                $hash -= 0x100000000;
            }
            while($hash < -0x80000000){
                $hash += 0x100000000;
            }
            $hash &= 0xFFFFFFFF;
        }
        return $hash;
    }

    /**
     * @param mixed[][] $trace
     * @phpstan-param list<array<string, mixed>> $trace
     *
     * @return string[]
     */
    public static function printableTrace(array $trace, int $maxStringLength = 80) : array{
        $messages = [];
        for($i = 0; isset($trace[$i]); ++$i){
            $params = "";
            if(isset($trace[$i]["args"]) or isset($trace[$i]["params"])){
                if(isset($trace[$i]["args"])){
                    $args = $trace[$i]["args"];
                }else{
                    $args = $trace[$i]["params"];
                }

                $params = implode(", ", array_map(function($value) use($maxStringLength) : string{
                    if(is_object($value)){
                        return "object " . self::getNiceClassName($value) . "#" . spl_object_id($value);
                    }
                    if(is_array($value)){
                        return "array[" . count($value) . "]";
                    }
                    if(is_string($value)){
                        return "string[" . strlen($value) . "] " . substr(Utils::printable($value), 0, $maxStringLength);
                    }
                    if(is_bool($value)){
                        return $value ? "true" : "false";
                    }
                    return gettype($value) . " " . Utils::printable((string) $value);
                }, $args));
            }
            $messages[] = "#$i " . (isset($trace[$i]["file"]) ? Filesystem::cleanPath($trace[$i]["file"]) : "") . "(" . (isset($trace[$i]["line"]) ? $trace[$i]["line"] : "") . "): " . (isset($trace[$i]["class"]) ? $trace[$i]["class"] . (($trace[$i]["type"] === "dynamic" or $trace[$i]["type"] === "->") ? "->" : "::") : "") . $trace[$i]["function"] . "(" . Utils::printable($params) . ")";
        }
        return $messages;
    }

    /**
     * @return mixed[][]
     * @phpstan-return list<array<string, mixed>>
     */
    public static function currentTrace(int $skipFrames = 0) : array{
        ++$skipFrames; //omit this frame from trace, in addition to other skipped frames
        if(function_exists("xdebug_get_function_stack")){
            $trace = array_reverse(xdebug_get_function_stack());
        }else{
            $e = new \Exception();
            $trace = $e->getTrace();
        }
        for($i = 0; $i < $skipFrames; ++$i){
            unset($trace[$i]);
        }
        return array_values($trace);
    }

    /**
     * @return string[]
     */
    public static function printableCurrentTrace(int $skipFrames = 0) : array{
        return self::printableTrace(self::currentTrace(++$skipFrames));
    }

    public static function checkUTF8(string $string) : void{
        if(!mb_check_encoding($string, 'UTF-8')){
            throw new \InvalidArgumentException("Text must be valid UTF-8");
        }
    }
}