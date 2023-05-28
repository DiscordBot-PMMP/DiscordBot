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

namespace JaxkDev\DiscordBot\Plugin\Tasks;

use JaxkDev\DiscordBot\Plugin\Main;
use JaxkDev\DiscordBot\Plugin\Storage;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;
use ZipArchive;

class DebugData extends \pocketmine\scheduler\AsyncTask{

    /** @var string */
    private $serverFolder;
    /** @var string */
    private $pluginFolder;
    /** @var string */
    private $config;
    /** @var string */
    private $version;
    /** @var string */
    private $pocketmineVersion;
    /** @var string */
    private $serverVersion;

    public function __construct(Main $plugin, CommandSender $sender){
        $this->storeLocal("sender", $sender);
        $this->serverFolder = $plugin->getServer()->getDataPath();
        $this->pluginFolder = $plugin->getDataFolder();
        $this->config = yaml_emit($plugin->getPluginConfig());
        $this->version = $plugin->getDescription()->getVersion();
        $this->pocketmineVersion = $plugin->getServer()->getPocketMineVersion();
        $this->serverVersion = $plugin->getServer()->getVersion();
    }

    public function onRun(): void{
        $startTime = microtime(true);

        if(!is_dir($this->pluginFolder."debug")){
            if(!mkdir($this->pluginFolder."debug")){
                throw new \RuntimeException("Failed to create debug folder.");
            }
        }

        $path = $this->pluginFolder."debug/"."discordbot_".time().".zip";
        $z = new ZipArchive();
        $z->open($path, ZIPARCHIVE::CREATE);

        //Config file, (USE $plugin->config, token is redacted in this but not on file.) (yaml_emit to avoid any comments that include sensitive data)
        $z->addFromString("config.yml", $this->config);

        //Server log.
        $z->addFile($this->serverFolder."server.log", "server.log");

        //Add Discord thread logs.
        $dir = scandir($this->pluginFolder."logs");
        if($dir !== false){
            foreach($dir as $file){
                if($file !== "." and $file !== ".."){
                    $z->addFile($this->pluginFolder."logs/".$file, "thread_logs/".$file);
                }
            }
        }

        //Add Storage.
        if(Storage::getTimestamp() !== 0){
            $z->addFromString("storage.serialized", Storage::serializeStorage());
        }

        //Some metadata, instead of users having no clue of anything I ask, therefore generate this information beforehand.
        $time = time();
        $ver = $this->version;
        $pmmp = $this->pocketmineVersion." | ".$this->serverVersion;
        $os = php_uname();
        $php = PHP_VERSION;
        $jit = "N/A";
        $jit_opt = "N/A";
        //TODO Check 8.1+
        if(function_exists('opcache_get_status') and (($opcacheStatus = opcache_get_status(false)) !== false)){
            $jit = ((($opcacheStatus["jit"]??[])["on"]??false) ? "Enabled" : "Disabled");
            $opcacheConfig = opcache_get_configuration();
            if($opcacheConfig !== false){
                $jit_opt = (($jit === "Enabled") ? (($opcacheConfig["directives"]??[])["opcache.jit"]??"N/A") : "N/A");
            }
        }
        $z->addFromString("metadata.txt", <<<META
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
 
Version    | {$ver}
Timestamp  | {$time}

PocketMine | {$pmmp}
PHP        | {$php}
JIT        | {$jit} [$jit_opt]
OS         | {$os}
META);
        $z->close();
        $time = round(microtime(true)-$startTime, 3);
        $this->setResult(TextFormat::GREEN."Successfully generated debug data in {$time} seconds, saved file to '$path'");
    }

    public function onError(): void{
        /** @var CommandSender $sender */
        $sender = $this->fetchLocal("sender");
        $sender->sendMessage(TextFormat::RED."Unable to generate debug data, internal error occurred.");
    }

    public function onCompletion(): void{
        /** @var CommandSender $sender */
        $sender = $this->fetchLocal("sender");
        /** @var string $res */
        $res = $this->getResult()??(TextFormat::RED."Internal error occurred");
        $sender->sendMessage($res);
    }
}