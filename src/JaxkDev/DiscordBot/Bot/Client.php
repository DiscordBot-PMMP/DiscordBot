<?php
/*
 * DiscordBot, PocketMine-MP Plugin.
 *
 * Licensed under the Open Software License version 3.0 (OSL-3.0)
 * Copyright (C) 2020 JaxkDev
 *
 * Twitter :: @JaxkDev
 * Discord :: JaxkDev#2698
 * Email   :: JaxkDev@gmail.com
 */

namespace JaxkDev\DiscordBot\Bot;

use Discord\Discord;
use Discord\Parts\Channel\Channel as DiscordChannel;
use Discord\Parts\Guild\Guild as DiscordGuild;
use Discord\Parts\Guild\Role as DiscordRole;
use Discord\Parts\User\Activity as DiscordActivity;
use Discord\Parts\User\Member as DiscordMember;
use Discord\Parts\User\User as DiscordUser;
use Error;
use ErrorException;
use Exception;
use JaxkDev\DiscordBot\Bot\Handlers\DiscordEventHandler;
use JaxkDev\DiscordBot\Bot\Handlers\PluginCommunicationHandler;
use JaxkDev\DiscordBot\Communication\BotThread;
use JaxkDev\DiscordBot\Communication\Models\Activity;
use JaxkDev\DiscordBot\Communication\Models\Channel;
use JaxkDev\DiscordBot\Communication\Models\Member;
use JaxkDev\DiscordBot\Communication\Models\Message;
use JaxkDev\DiscordBot\Communication\Models\Role;
use JaxkDev\DiscordBot\Communication\Models\Server;
use JaxkDev\DiscordBot\Communication\Models\User;
use JaxkDev\DiscordBot\Communication\Packets\DiscordAllData;
use JaxkDev\DiscordBot\Communication\Packets\Packet;
use JaxkDev\DiscordBot\Communication\Protocol;
use JaxkDev\DiscordBot\Utils;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\RotatingFileHandler;
use pocketmine\utils\MainLogger;
use React\EventLoop\TimerInterface;

class Client {

	/** @var BotThread */
	private $thread;

	/** @var Discord */
	private $client;

	/** @var PluginCommunicationHandler */
	private $pluginCommsHandler;

	/** @var DiscordEventHandler */
	private $discordEventHandler;

	/** @var TimerInterface|null */
	private $readyTimer, $tickTimer;

	/** @var int */
	private $tickCount, $lastGCCollection = 0;

	/** @var array */
	private $config;

	public function __construct(BotThread $thread, array $config) {
		$this->thread = $thread;
		$this->config = $config;

		gc_enable();

		error_reporting(E_ALL & ~E_NOTICE);
		set_error_handler(array($this, 'errorHandler'));
		register_shutdown_function(array($this, 'close'));

		// Mono logger can have issues with other timezones, for now use UTC.
		// Note, this does not effect outside thread config.
		// TODO CDT Investigate.
		ini_set("date.timezone", "UTC");
		MainLogger::getLogger()->debug("Log files will be in UTC timezone.");

		Utils::$BOT_THREAD = true;
		Packet::$UID_COUNT = 1;

		$logger = new Logger('DiscordPHP');
		$httpLogger = new Logger('DiscordPHP.HTTP');
		$handler = new RotatingFileHandler($config['logging']['directory'].DIRECTORY_SEPARATOR."DiscordBot.log", $config['logging']['maxFiles'], Logger::DEBUG);
		$handler->setFilenameFormat('{filename}-{date}', 'Y-m-d');
		$logger->setHandlers(array($handler));
		$httpLogger->setHandlers(array($handler));

		if($config['logging']['debug']) {
			$handler = new StreamHandler(($r = fopen('php://stdout', 'w')) === false ? "" : $r);
			$logger->pushHandler($handler);
			$httpLogger->pushHandler($handler);
		}

		// TODO Intents.

		$socket_opts = [];
		if($config['security']['disable_ssl']){
			MainLogger::getLogger()->warning("SSL/TLS verification has been disabled.");
			$socket_opts['tls'] = ['verify_peer' => false, 'verify_peer_name' => false];
		}

		/** @noinspection PhpUnhandledExceptionInspection */
		$this->client = new Discord([
			'token' => $config['discord']['token'],
			'logger' => $logger,
			'httpLogger' => $httpLogger,
			'socket_options' => $socket_opts,
			'loadAllMembers' => true  // Seems like this is the only way...
		]);

		$this->config['discord']['token'] = "REDACTED";

		$this->pluginCommsHandler = new PluginCommunicationHandler($this);
		$this->discordEventHandler = new DiscordEventHandler($this);

		$this->registerHandlers();
		$this->registerTimers();

		$this->thread->setStatus(Protocol::THREAD_STATUS_STARTED);

		$this->client->run();
	}

	private function registerTimers(): void{
		// Handles shutdown, rather than a SHUTDOWN const to send through internal communication, set flag to closed.
		// Saves time & will guarantee closure ASAP rather then waiting in line through ^
		$this->client->getLoop()->addPeriodicTimer(1, function(){
			if($this->thread->getStatus() === Protocol::THREAD_STATUS_CLOSING){
				$this->close();
			}
		});

		// Handles any problems pre-ready.
		$this->readyTimer = $this->client->getLoop()->addTimer(30, function(){
			if($this->client->id !== null){
				MainLogger::getLogger()->warning("Client has taken >30s to get ready, How large is your discord server !?  [Create an issue on github is this persists]");
				$this->client->getLoop()->addTimer(30, function(){
					if($this->thread->getStatus() !== Protocol::THREAD_STATUS_READY){
						MainLogger::getLogger()->critical("Client has taken too long to become ready, shutting down.");
						$this->close();
					}
				});
			} else {
				MainLogger::getLogger()->critical("Client failed to login/connect within 30 seconds, See log file for details.");
				$this->close();
			}
		});

		$this->tickTimer = $this->client->getLoop()->addPeriodicTimer(1/20, function(){
			$this->tick();
		});
	}

	/** @noinspection PhpUnusedParameterInspection */
	private function registerHandlers(): void{
		// https://github.com/teamreflex/DiscordPHP/issues/433
		// Note ready is emitted after successful connection + all servers/users loaded, so only register events
		// After this event.
		$this->client->on('ready', function (Discord $discord) {
			if($this->readyTimer !== null) {
				$this->client->getLoop()->cancelTimer($this->readyTimer);
				$this->readyTimer = null;
			}

			$ac = new Activity();
			$ac->setMessage("In PocketMine-MP.")->setType(Activity::TYPE_PLAYING)->setStatus(Activity::STATUS_IDLE);
			$this->updatePresence($ac);

			// Register all over events.
			$this->discordEventHandler->registerEvents();

			// Dump all discord data.
			$pk = new DiscordAllData();
			$pk->setTimestamp(time());

			MainLogger::getLogger()->debug("Starting the data pack, this can take up to a minute please be patient.\nNote this does not effect the main thread.");
			$t = microtime(true);
			$mem = memory_get_usage(true);

			/** @var DiscordGuild $guild */
			foreach($this->client->guilds as $guild){
				$server = new Server();
				$server->setId((int)$guild->id)
					->setCreationTimestamp(Utils::convertIdToTime($guild->id))
					->setIconUrl($guild->icon)
					->setLarge($guild->large)
					->setMemberCount($guild->member_count)
					->setName($guild->name)
					->setOwnerId((int)$guild->owner_id)
					->setRegion($guild->region);
				$pk->addServer($server);

				/** @var DiscordChannel $channel */
				foreach($guild->channels as $channel){
					if($channel->type !== DiscordChannel::TYPE_TEXT) continue;
					$ch = new Channel();
					$ch->setName($channel->name)
						->setId((int)$channel->id)
						->setCategory(null) //todo, parent_id gives ID of channel (Type_category)
						->setDescription($channel->topic)
						->setServerId((int)$guild->id);
					$pk->addChannel($ch);
				}

				/** @var DiscordRole $role */
				foreach($guild->roles as $role){
					$r = new Role();
					$r->setServerId((int)$guild->id)
						->setId((int)$role->id)
						->setName($role->name)
						->setColour($role->color)
						->setHoistedPosition($role->position)
						->setMentionable($role->mentionable)
						->setPermissions($role->permissions->bitwise);
					$pk->addRole($r);
				}

				/** @var DiscordMember $member */
				foreach($guild->members as $member){
					$m = new Member();
					$m->setGuildId((int)$guild->id)
						->setUserId((int)$member->user->id)
						->setNickname($member->nick)
						->setJoinTimestamp($member->joined_at->getTimestamp())
						->setBoostTimestamp($member->premium_since === null ? null : $member->premium_since->getTimestamp())
						->setId();

					/** @var int[] $roles */
					$roles = [];

					/** @var DiscordRole $role */
					foreach($member->roles as $role){
						$roles[] = (int)$role->id;
					}
					$m->setRolesId($roles);

					$pk->addMember($m);
				}
			}

			/** @var DiscordUser $user */
			foreach($this->client->users as $user){
				$u = new User();
				$u->setId((int)$user->id)
					->setCreationTimestamp((int)$user->createdTimestamp())
					->setAvatarUrl($user->avatar)
					->setDiscriminator((int)$user->discriminator)
					->setUsername($user->username);
				$pk->addUser($u);
			}

			$this->thread->writeOutboundData($pk);

			MainLogger::getLogger()->debug("Data pack Took: ".round(microtime(true)-$t, 5)."s & ".
				round(((memory_get_usage(true)-$mem)/1024)/1024, 4)."mb of memory, Final size: ".$pk->getSize());

			// Force fresh heartbeat asap, as that took quite some time.
			$this->getPluginCommunicationHandler()->sendHeartbeat();

			$this->thread->setStatus(Protocol::THREAD_STATUS_READY);
			MainLogger::getLogger()->info("Client ready.");

			$this->logDebugInfo();
		});
	}

	public function tick(): void{
		$data = $this->thread->readInboundData(Protocol::PPT);

		foreach($data as $d) $this->pluginCommsHandler->handle($d);

		if(($this->tickCount % 20) === 0){
			//Run every second.
			$this->pluginCommsHandler->checkHeartbeat();
			$this->pluginCommsHandler->sendHeartbeat();

			//GC Tests.
			if(microtime(true)-$this->lastGCCollection >= 600){
				$cycles = gc_collect_cycles();
				$mem = round(gc_mem_caches()/1024, 3);
				MainLogger::getLogger()->debug("[GC] Claimed {$mem}kb and {$cycles} cycles.");
				$this->lastGCCollection = time();
			}
		}

		$this->tickCount++;
	}

	public function getThread(): BotThread{
		return $this->thread;
	}

	public function getDiscordClient(): Discord{
		return $this->client;
	}

	public function getPluginCommunicationHandler(): PluginCommunicationHandler{
		return $this->pluginCommsHandler;
	}

	/*
	 * Note, It will only show warning ONCE per channel/guild that fails.
	 * Fix on the way hopefully.
	 */
	public function sendMessage(Message $message): void{
		if($this->thread->getStatus() !== Protocol::THREAD_STATUS_READY) return;

		/** @noinspection PhpUnhandledExceptionInspection */
		$this->client->guilds->fetch((string)$message->getGuildId())->done(function(DiscordGuild $guild) use($message) {
			$guild->channels->fetch((string)$message->getChannelId())->done(function(DiscordChannel $channel) use($message) {
				$channel->sendMessage($message->getContent());
				MainLogger::getLogger()->debug("Sent message(".strlen($message->getContent()).") to ({$message->getGuildId()}|{$message->getChannelId()})");
			}, function() use($message) {
				MainLogger::getLogger()->warning("Failed to fetch channel {$message->getChannelId()} in guild {$message->getGuildId()} while attempting to send message.");
			});
		}, function() use($message) {
			MainLogger::getLogger()->warning("Failed to fetch guild {$message->getGuildId()} while attempting to send message.");
		});
	}

	public function updatePresence(Activity $activity): bool{
		$presence = new DiscordActivity($this->client, [
			'name' => $activity->getMessage(),
			'type' => $activity->getType()
		]);

		try {
			$this->client->updatePresence($presence, $activity->getStatus() === "idle", $activity->getStatus());
			return true;
		} catch (Exception $e) {
			return false;
		}
	}

	public function logDebugInfo(): void{
		MainLogger::getLogger()->debug("Debug Information:\n".
			"> Username: {$this->client->username}#{$this->client->discriminator}\n".
			"> ID: {$this->client->id}\n".
			"> Servers: {$this->client->guilds->count()}\n".
			"> Users: {$this->client->users->count()}"
		);
	}

	public function errorHandler(int $severity, string $message, string $file, int $line): bool{
		if(substr($message,0,51) === "stream_socket_client(): unable to connect to udp://" and $line === 130){
			// Really nasty hack to check if connection fails,
			// Really need to fork/fix this in DiscordPHP...
			MainLogger::getLogger()->critical("Failed to connect to discord, please check your internet connection.");
		}
		MainLogger::getLogger()->logException(new ErrorException($message, 0, $severity, $file, $line));
		$this->close();
		return true;
	}

	public function close(): void{
		if($this->thread->getStatus() === Protocol::THREAD_STATUS_CLOSED) return;
		if($this->client instanceof Discord){
			try{
				$this->client->close(true);
			} catch (Error $e){
				MainLogger::getLogger()->debug("Failed to close client, probably due it not being started.");
			}
		}
		$this->thread->setStatus(Protocol::THREAD_STATUS_CLOSED);
		MainLogger::getLogger()->debug("Client closed.");
		//if(extension_loaded('xdebug')) var_dump(xdebug_stop_gcstats());
		exit(0);
	}
}