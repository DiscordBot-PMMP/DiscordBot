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

namespace JaxkDev\DiscordBot\Plugin;

use JaxkDev\DiscordBot\Communication\BotThread;
use JaxkDev\DiscordBot\Communication\Packets\Packet;
use JaxkDev\DiscordBot\Plugin\Events\DiscordClosed;
use JaxkDev\DiscordBot\Plugin\Tasks\DebugData;
use Phar;
use pmmp\thread\Thread;
use pmmp\thread\ThreadSafeArray;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\PluginBase;
use pocketmine\plugin\PluginException;
use pocketmine\scheduler\ClosureTask;
use pocketmine\scheduler\TaskHandler;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;

class Main extends PluginBase{

    /** @var BotThread */
    private $discordBot;

    /** @var ThreadSafeArray */
    private $inboundData;
    /** @var ThreadSafeArray */
    private $outboundData;

    /** @var TaskHandler */
    private $tickTask;

    /** @var BotCommunicationHandler */
    private $communicationHandler;

    /** @var Api */
    private $api;

    /** @var array */
    private $config;

    protected function onLoad(): void{
        if(($phar = Phar::running()) === ""){
            throw new PluginException("Cannot be run from source.");
        }

        if(!defined("JaxkDev\DiscordBot\COMPOSER")){
            define("JaxkDev\DiscordBot\DATA_PATH", $this->getDataFolder());
            define("JaxkDev\DiscordBot\VERSION", "v".$this->getDescription()->getVersion());
            define("JaxkDev\DiscordBot\COMPOSER", $phar."/vendor/autoload.php");
        }
        if (!function_exists('JaxkDev\DiscordBot\Libs\React\Promise\resolve')) {
            require $phar.'/src/Libs/React/Promise/functions.php';
        }

        if(!is_dir($this->getDataFolder()."logs")){
            mkdir($this->getDataFolder()."logs");
        }

        $this->saveDefaultConfig();
        $this->saveResource("HELP_ENG.txt", true); //Always keep these up-to-date.
        $this->saveResource("cacert.pem", true);

        $this->inboundData = new ThreadSafeArray();
        $this->outboundData = new ThreadSafeArray();
    }

    protected function onEnable(): void{
        if(!$this->loadConfig()) return;
        if(is_file($this->getDataFolder()."events.yml")){
            // Don't delete file, DiscordChat will transfer it then delete it.
            $this->getLogger()->alert("DiscordBot v1 events.yml file found, please note this has been stripped out of ".
                "the DiscordBot core, see https://github.com/DiscordBot-PMMP/DiscordChat for similar features.");
        }

        /** @noinspection PhpUndefinedFunctionInspection */
        if(extension_loaded("xdebug") and (!function_exists('xdebug_info') || count(xdebug_info('mode')) !== 0)){
            $this->getLogger()->warning("xdebug is enabled, this will cause major performance issues with the discord thread.");
        }

        $this->api = new Api($this);
        $this->communicationHandler = new BotCommunicationHandler($this);

        $this->getLogger()->debug("Starting DiscordBot Thread...");
        $this->discordBot = new BotThread(ThreadSafeArray::fromArray($this->config), $this->outboundData, $this->inboundData);
        $this->discordBot->start(Thread::INHERIT_CONSTANTS);

        //Redact token.
        $this->config["discord"]["token"] = preg_replace('([a-zA-Z0-9])','*', $this->config["discord"]["token"]);

        $this->tickTask = $this->getScheduler()->scheduleRepeatingTask(new ClosureTask(function(): void{
            $this->tick($this->getServer()->getTick());
        }), 1);
    }

    protected function onDisable(): void{
        (new DiscordClosed($this))->call();

        if($this->tickTask !== null and !$this->tickTask->isCancelled()){
            $this->tickTask->cancel();
        }

        //TODO Check if thread closed before we disabled (indicating an error/crash occurred in thread TBD on this method)
        //If so, we need to generate a crash dump (debug data but in a separate folder for 'crashes'/'errors')
        //TODO Also generate dump if PLUGIN crashes or similar.
        if($this->discordBot !== null and $this->discordBot->isTerminated()){
            $this->getLogger()->error("Discord thread terminated, check logs for more information.");
        }

        if($this->discordBot !== null and $this->discordBot->isRunning()){
            $this->discordBot->setStatus(BotThread::STATUS_CLOSING);
            $this->getLogger()->info("Stopping discord thread gracefully, waiting for discord thread to stop...");
            //Never had a condition where it hangs more than 1s (only long period of wait should be during the data dump.)
            $this->discordBot->join();
            $this->getLogger()->info("Thread stopped.");
        }
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool{
        if($command->getName() !== "debugdiscord") return false;
        if(!$command->testPermission($sender)) return true;

        $sender->sendMessage(TextFormat::YELLOW."Building debug file, please be patient this can take several seconds.");

        $task = new DebugData($this, $sender);
        $this->getServer()->getAsyncPool()->submitTask($task);

        return true;
    }

    private function loadConfig(): bool{
        $this->getLogger()->debug("Loading configuration...");

        /** @var array<string, mixed>|false $config */
        $config = yaml_parse_file($this->getDataFolder()."config.yml");
        if($config === false or !is_int($config["version"]??"")){
            $this->getLogger()->critical("Failed to parse config.yml");
            $this->getServer()->getPluginManager()->disablePlugin($this);
            return false;
        }

        if(intval($config["version"]) !== ConfigUtils::VERSION){
            $this->getLogger()->info("Updating your config from v".intval($config["version"])." to v".ConfigUtils::VERSION);
            ConfigUtils::update($config);
            rename($this->getDataFolder()."config.yml", $this->getDataFolder()."config.yml.old");
            yaml_emit_file($this->getDataFolder()."config.yml", $config);
            $this->getLogger()->info("Config updated, old config was saved to '{$this->getDataFolder()}config.yml.old'");
        }

        $this->getLogger()->debug("Verifying config...");
        $result_raw = ConfigUtils::verify($config);
        if(sizeof($result_raw) !== 0){
            $result = TextFormat::RED."There were some problems with your config.yml, see below:\n".TextFormat::RESET;
            foreach($result_raw as $value){
                $result .= "{$value}\n";
            }
            $this->getLogger()->error(rtrim($result));
            $this->getServer()->getPluginManager()->disablePlugin($this);
            return false;
        }

        //Config is now updated and verified.
        $this->config = $config;
        return true;
    }

    private function tick(int $currentTick): void{
        $data = $this->readInboundData($this->config["protocol"]["packets_per_tick"]);

        /** @var Packet $d */
        foreach($data as $d){
            $this->communicationHandler->handle($d);
        }

        if(($currentTick % 20) === 0){
            //Run every second. [Faster/More accurate over bots tick]
            if($this->discordBot->getStatus() === BotThread::STATUS_READY){
                $this->communicationHandler->checkHeartbeat();
                $this->communicationHandler->sendHeartbeat();
            }
            if($this->discordBot->getStatus() === BotThread::STATUS_CLOSED){
                $this->getServer()->getPluginManager()->disablePlugin($this);
            }
        }

        if($this->inboundData->count() > 2000){
            $this->getLogger()->emergency("Too much data coming in from discord, stopping plugin+thread.  (If this issue persists, create a issue at https://github.com/DiscordBot-PMMP/DiscordBot/issues/new)");
            $this->getServer()->getPluginManager()->disablePlugin($this);
        }

        if($this->outboundData->count() > 2000){
            $this->getLogger()->emergency("Too much data going out, stopping plugin+thread.  (If this issue persists, create a issue at https://github.com/DiscordBot-PMMP/DiscordBot/issues/new)");
            $this->getServer()->getPluginManager()->disablePlugin($this);
        }
    }

    private function readInboundData(int $count = 1): array{
        return array_map(function($data){
            $packet = unserialize($data);
            if(!$packet instanceof Packet){
                throw new \AssertionError("Data did not unserialize to a Packet.");
            }
            return $packet;
        }, $this->inboundData->chunk($count));
    }

    /**
     * @internal INTERNAL USE ONLY.
     */
    public function writeOutboundData(Packet $packet): void{
        $this->outboundData[] = serialize($packet);
    }

    public function getBotCommunicationHandler(): BotCommunicationHandler{
        return $this->communicationHandler;
    }

    public function getApi(): Api{
        return $this->api;
    }

    /**
     * @return never-return
     * @noinspection PhpDocSignatureInspection
     */
    public function getConfig(): Config{
        throw new PluginException("getConfig() is not used, see Main::getPluginConfig()");
    }

    public function getPluginConfig(): array{
        return $this->config;
    }

    // Don't allow this.
    public function reloadConfig(): void{}
    public function saveConfig(): void{}
}