<?php
/*
 * DiscordBot, PocketMine-MP Plugin.
 *
 * Licensed under the Open Software License version 3.0 (OSL-3.0)
 * Copyright (C) 2020-2021 JaxkDev
 *
 * Twitter :: @JaxkDev
 * Discord :: JaxkDev#2698
 * Email   :: JaxkDev@gmail.com
 */

namespace JaxkDev\DiscordBot\Bot\Handlers;

use Discord\Discord;
use Discord\Parts\Channel\Channel as DiscordChannel;
use Discord\Parts\Channel\Message as DiscordMessage;
use Discord\Parts\Guild\Guild as DiscordGuild;
use Discord\Parts\Guild\Role as DiscordRole;
use Discord\Parts\User\Member as DiscordMember;
use Discord\Parts\User\User as DiscordUser;
use JaxkDev\DiscordBot\Bot\Client;
use JaxkDev\DiscordBot\Bot\ModelConverter;
use JaxkDev\DiscordBot\Communication\Models\Activity;
use JaxkDev\DiscordBot\Communication\Packets\Discord\DiscordAllData;
use JaxkDev\DiscordBot\Communication\Protocol;
use pocketmine\utils\MainLogger;

class DiscordEventHandler{

	/** @var Client */
	private $client;

	public function __construct(Client $client){
		$this->client = $client;
	}

	public function registerEvents(): void{
		$discord = $this->client->getDiscordClient();
		$discord->on('MESSAGE_CREATE', [$this, 'onMessageCreate']);
		$discord->on('MESSAGE_DELETE', [$this, 'onMessageDelete']);
		$discord->on('MESSAGE_UPDATE', [$this, 'onMessageUpdate']);  //AKA Edit

		$discord->on('GUILD_MEMBER_ADD', [$this, 'onMemberJoin']);
		$discord->on('GUILD_MEMBER_REMOVE', [$this, 'onMemberLeave']);
		$discord->on('GUILD_MEMBER_UPDATE', [$this, 'onMemberUpdate']);   //Includes Roles,nickname etc

		$discord->on('GUILD_CREATE', [$this, 'onGuildJoin']);
		$discord->on('GUILD_UPDATE', [$this, 'onGuildUpdate']);
		$discord->on('GUILD_DELETE', [$this, 'onGuildLeave']);

		$discord->on('CHANNEL_CREATE', [$this, 'onChannelCreate']);
		$discord->on('CHANNEL_UPDATE', [$this, 'onChannelUpdate']);
		$discord->on('CHANNEL_DELETE', [$this, 'onChannelDelete']);

		$discord->on('GUILD_ROLE_CREATE', [$this, 'onRoleCreate']);
		$discord->on('GUILD_ROLE_UPDATE', [$this, 'onRoleUpdate']);
		$discord->on('GUILD_ROLE_DELETE', [$this, 'onRoleDelete']);

		/*
		 * TODO (others not yet planned for 2.0.0):
		 * - Reactions
		 * - Pins
		 * - Server Integrations ?
		 * - Invites
		 * - Bans
		 */
	}

	public function onReady(): void{
		if($this->client->getThread()->getStatus() !== Protocol::THREAD_STATUS_STARTED){
			MainLogger::getLogger()->warning("Closing thread, unexpected state change.");
			$this->client->close();
		}

		//Default activity.
		$ac = new Activity();
		$ac->setMessage("PocketMine-MP v".\pocketmine\VERSION)->setType(Activity::TYPE_PLAYING)->setStatus(Activity::STATUS_IDLE);
		$this->client->updatePresence($ac);

		// Register all other events.
		$this->registerEvents();

		// Dump all discord data.
		$pk = new DiscordAllData();
		$pk->setTimestamp(time());

		MainLogger::getLogger()->debug("Starting the data pack, please be patient.");
		$t = microtime(true);
		$mem = memory_get_usage(true);

		$client = $this->client->getDiscordClient();

		/** @var DiscordGuild $guild */
		foreach($client->guilds as $guild){
			$pk->addServer(ModelConverter::genModelServer($guild));

			/** @var DiscordChannel $channel */
			foreach($guild->channels as $channel){
				if($channel->type !== DiscordChannel::TYPE_TEXT) continue;
				$pk->addChannel(ModelConverter::genModelChannel($channel));
			}

			/** @var DiscordRole $role */
			foreach($guild->roles as $role){
				$pk->addRole(ModelConverter::genModelRole($role));
			}

			/** @var DiscordMember $member */
			foreach($guild->members as $member){
				$pk->addMember(ModelConverter::genModelMember($member));
			}
		}

		/** @var DiscordUser $user */
		foreach($client->users as $user){
			$pk->addUser(ModelConverter::genModelUser($user));
		}

		$pk->setBotUser(ModelConverter::genModelUser($client->user));

		$this->client->getThread()->writeOutboundData($pk);

		MainLogger::getLogger()->debug("Data pack Took: ".round(microtime(true)-$t, 5)."s & ".
			round(((memory_get_usage(true)-$mem)/1024)/1024, 4)."mb of memory, Final size: ".$pk->getSize());

		// Force fresh heartbeat asap, as that took quite some time.
		$this->client->getCommunicationHandler()->sendHeartbeat();

		$this->client->getThread()->setStatus(Protocol::THREAD_STATUS_READY);
		MainLogger::getLogger()->info("Client ready.");

		$this->client->logDebugInfo();
	}

	/**
	 * Checks if we handle this type of message in this type of channel.
	 * @param DiscordMessage $message
	 * @return bool
	 */
	private function checkMessage(DiscordMessage $message): bool{
		// Can be user if bot doesnt have correct intents enabled on discord developer dashboard.
		if($message->author === null) return false; //Investigating specific case.
		if($message->author instanceof DiscordMember ? $message->author->user->bot : (isset($message->author) ? $message->author->bot : true)) return false;

		// Other types of messages not used right now.
		if($message->type !== DiscordMessage::TYPE_NORMAL) return false;
		if($message->channel->type !== DiscordChannel::TYPE_TEXT) return false;
		if(($message->content ?? "") === "") return false; //Images/Files, can be empty strings or just null in other cases.
		if($message->channel->guild_id === null) return false;

		return true;
	}

	public function onMessageCreate(DiscordMessage $message, Discord $discord): void{
		//var_dump(microtime(true)." - create message #".$message->id);
		if(!$this->checkMessage($message)) return;
		//if($message->author->id === "305060807887159296") $message->react("❤️");
		//Dont ask questions...
		$this->client->getCommunicationHandler()->sendMessageSentEvent(ModelConverter::genModelMessage($message));
	}

	/**
	 * @param DiscordMessage|\stdClass $message
	 * @param Discord                  $discord
	 */
	public function onMessageDelete($message, Discord $discord): void{
		//var_dump(microtime(true)." - delete message #".$message->id);
 		$this->client->getCommunicationHandler()->sendMessageDeleteEvent($message->id);
	}

	public function onMessageUpdate(DiscordMessage $message, Discord $discord): void{
		//var_dump(microtime(true)." - update message #".$message->id);
		if(!$this->checkMessage($message)) return;
		$this->client->getCommunicationHandler()->sendMessageUpdateEvent(ModelConverter::genModelMessage($message));
	}

	public function onMemberJoin(DiscordMember $member, Discord $discord): void{
		$this->client->getCommunicationHandler()->sendMemberJoinEvent(ModelConverter::genModelMember($member),
			ModelConverter::genModelUser($member->user));
	}

	public function onMemberLeave(DiscordMember $member, Discord $discord): void{
		$this->client->getCommunicationHandler()->sendMemberLeaveEvent($member->guild_id.".".$member->id);
	}

	public function onMemberUpdate(DiscordMember $member, Discord $discord): void{
		$this->client->getCommunicationHandler()->sendMemberUpdateEvent(ModelConverter::genModelMember($member));
	}

	public function onGuildJoin(DiscordGuild $guild, Discord $discord): void{
		$channels = [];
		/** @var DiscordChannel $channel */
		foreach($guild->channels->toArray() as $channel){
			if($channel->type === DiscordChannel::TYPE_TEXT){
				$channels[] = ModelConverter::genModelChannel($channel);
			}
		}
		$roles = [];
		/** @var DiscordRole $role */
		foreach($guild->roles->toArray() as $role){
			$roles[] = ModelConverter::genModelRole($role);
		}
		$members = [];
		/** @var DiscordMember $member */
		foreach($guild->members->toArray() as $member){
			$members[] = ModelConverter::genModelMember($member);
		}
		$server = ModelConverter::genModelServer($guild);
		$this->client->getCommunicationHandler()->sendServerJoinEvent($server, $channels, $roles, $members);
	}

	public function onGuildLeave(DiscordGuild $guild, Discord $discord): void{
		$this->client->getCommunicationHandler()->sendServerLeaveEvent(ModelConverter::genModelServer($guild));
	}

	public function onGuildUpdate(DiscordGuild $guild, Discord $discord): void{
		$this->client->getCommunicationHandler()->sendServerUpdateEvent(ModelConverter::genModelServer($guild));
	}

	public function onChannelCreate(DiscordChannel $channel, Discord $discord): void{
		$this->client->getCommunicationHandler()->sendChannelCreateEvent(ModelConverter::genModelChannel($channel));
	}

	public function onChannelUpdate(DiscordChannel $channel, Discord $discord): void{
		$this->client->getCommunicationHandler()->sendChannelUpdateEvent(ModelConverter::genModelChannel($channel));
	}

	public function onChannelDelete(DiscordChannel $channel, Discord $discord): void{
		$this->client->getCommunicationHandler()->sendChannelDeleteEvent(ModelConverter::genModelChannel($channel));
	}

	public function onRoleCreate(DiscordRole $role, Discord $discord): void{
		$this->client->getCommunicationHandler()->sendRoleCreateEvent(ModelConverter::genModelRole($role));
	}

	public function onRoleUpdate(DiscordRole $role, Discord $discord): void{
		$this->client->getCommunicationHandler()->sendRoleUpdateEvent(ModelConverter::genModelRole($role));
	}

	public function onRoleDelete(DiscordRole $role, Discord $discord): void{
		$this->client->getCommunicationHandler()->sendRoleDeleteEvent(ModelConverter::genModelRole($role));
	}
}