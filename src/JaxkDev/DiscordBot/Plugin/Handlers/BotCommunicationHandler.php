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

namespace JaxkDev\DiscordBot\Plugin\Handlers;

use JaxkDev\DiscordBot\Communication\Models\Channel;
use JaxkDev\DiscordBot\Communication\Models\Member;
use JaxkDev\DiscordBot\Communication\Models\Server;
use JaxkDev\DiscordBot\Communication\Models\User;
use JaxkDev\DiscordBot\Communication\Packets\DiscordEventAllData;
use JaxkDev\DiscordBot\Communication\Packets\DiscordEventMemberJoin;
use JaxkDev\DiscordBot\Communication\Packets\DiscordEventMemberLeave;
use JaxkDev\DiscordBot\Communication\Packets\DiscordEventMessageSent;
use JaxkDev\DiscordBot\Communication\Packets\Heartbeat;
use JaxkDev\DiscordBot\Communication\Packets\Packet;
use JaxkDev\DiscordBot\Communication\Protocol;
use JaxkDev\DiscordBot\Plugin\Main;
use JaxkDev\DiscordBot\Plugin\Storage;
use JaxkDev\DiscordBot\Utils;
use pocketmine\utils\MainLogger;

class BotCommunicationHandler {

	/** @var Main */
	private $plugin;

	/** @var float */
	private $lastHeartbeat;

	public function __construct(Main $plugin){
		$this->plugin = $plugin;
	}

	public function handle(Packet $packet): bool{
		// If's instances instead of ID switching due to phpstan/types.
		if($packet instanceof Heartbeat) return $this->handleHeartbeat($packet);
		if($packet instanceof DiscordEventMemberJoin) return $this->handleMemberJoin($packet);
		if($packet instanceof DiscordEventMemberLeave) return $this->handleMemberLeave($packet);
		if($packet instanceof DiscordEventMessageSent) return $this->handleMessageSent($packet);
		if($packet instanceof DiscordEventAllData) return $this->handleAllDiscordData($packet);
		return false;
	}

	private function handleHeartbeat(Heartbeat $packet): bool{
		$this->lastHeartbeat = $packet->getHeartbeat();
		return true;
	}

	private function handleMessageSent(DiscordEventMessageSent $packet): bool{
		$config = $this->plugin->getEventsConfig()['message']['fromDiscord'];
		$message = $packet->getMessage();

		if(!in_array($message->getServerId().".".$message->getChannelId(), $config['channels'])) return true;

		//If any of these asserts fire theres a mismatch between Storage and discord.

		/** @var Server $server */
		$server = Storage::getServer($message->getServerId());
		Utils::assert($server instanceof Server);

		/** @var Channel $channel */
		$channel = Storage::getChannel($message->getChannelId());
		Utils::assert($channel instanceof Channel);

		/** @var Member $author */
		$author = Storage::getMember($message->getAuthorId()??"");
		Utils::assert($author instanceof Member);

		/** @var User $user */
		$user = Storage::getUser($author->getUserId());
		Utils::assert($user instanceof User);

		/*var_dump($server);
		var_dump($channel);
		var_dump($author);
		var_dump($user);*/

		$formatted = str_replace(['{TIME}', '{USER_ID}', '{USERNAME}', '{USER_DISCRIMINATOR}', '{SERVER_ID}',
			'{SERVER_NAME}', '{CHANNEL_ID}', '{CHANNEL_NAME}', '{MESSAGE}'], [
				date('G:i:s', (int)$message->getTimestamp()??0), $author->getUserId(), $user->getUsername(),
				$user->getDiscriminator(), $server->getId(), $server->getName(), $channel->getId(), $channel->getName(),
				$message->getContent()
			],
			$config['format']);

		$this->plugin->getServer()->broadcastMessage($formatted);

		return true;
	}

	private function handleMemberJoin(DiscordEventMemberJoin $packet): bool{
		$config = $this->plugin->getEventsConfig()['member_join']['fromDiscord'];
		if(($config['format'] ?? "") === "") return true;

		/** @var Server $server */
		$server = Storage::getServer($packet->getMember()->getServerId());
		Utils::assert($server instanceof Server);

		$member = $packet->getMember();
		$user = $packet->getUser();

		Storage::addMember($member);
		Storage::addUser($user);

		$formatted = str_replace(
			['{TIME}', '{USER_ID}', '{USERNAME}', '{USER_DISCRIMINATOR}', '{SERVER_ID}', '{SERVER_NAME}'],
			[date('G:i:s', $member->getJoinTimestamp()), $member->getId(), $user->getUsername(),
				$user->getDiscriminator(), $server->getId(), $server->getName()], $config['format']);

		$this->plugin->getServer()->broadcastMessage($formatted);

		return true;
	}

	private function handleMemberLeave(DiscordEventMemberLeave $packet): bool{
		$config = $this->plugin->getEventsConfig()['member_leave']['fromDiscord'];
		if(($config['format'] ?? "") === "") return true;

		/** @var Member $member */
		$member = Storage::getMember($packet->getMemberID());
		Utils::assert($member instanceof Member);

		/** @var Server $server */
		$server = Storage::getServer($member->getServerId());
		Utils::assert($server instanceof Server);

		/** @var User $user */
		$user = Storage::getUser($member->getUserId());
		Utils::assert($user instanceof User);

		$formatted = str_replace(
			['{TIME}', '{USER_ID}', '{USERNAME}', '{USER_DISCRIMINATOR}', '{SERVER_ID}', '{SERVER_NAME}'],
			[date('G:i:s', $member->getJoinTimestamp()), $user->getId(), $user->getUsername(),
				$user->getDiscriminator(), $server->getId(), $server->getName()], $config['format']);

		$this->plugin->getServer()->broadcastMessage($formatted);

		return true;
	}

	public function handleAllDiscordData(DiscordEventAllData $packet): bool{
		//Todo verify packet before resetting data.
		Storage::reset();
		foreach($packet->getServers() as $server){
			Storage::addServer($server);
		}
		foreach($packet->getChannels() as $channel){
			Storage::addChannel($channel);
		}
		foreach($packet->getRoles() as $role){
			Storage::addRole($role);
		}
		foreach($packet->getMembers() as $member){
			Storage::addMember($member);
		}
		foreach($packet->getUsers() as $user){
			Storage::addUser($user);
		}
		Storage::setBotUser($packet->getBotUser());
		Storage::setTimestamp($packet->getTimestamp());

		return true;
	}

	/*public function sendMessage(string $guild, string $channel, string $content): void{
		$this->plugin->writeOutboundData(
			Protocol::ID_SEND_MESSAGE,
			[$guild, $channel, $content]
		);
	}*/

	/**
	 * Checks last KNOWN Heartbeat timestamp with current time, does not check pre-start condition.
	 */
	public function checkHeartbeat(): void{
		if(($diff = microtime(true) - ($this->lastHeartbeat ?? microtime(true))) > Protocol::HEARTBEAT_ALLOWANCE){
			// Bot is dead, shutdown plugin.
			MainLogger::getLogger()->emergency("DiscordBot has not responded for 2 seconds, disabling plugin + bot.");
			$this->plugin->stopAll();
		}
	}

	public function sendHeartbeat(): void{
		$p = new Heartbeat();
		$p->setHeartbeat(microtime(true));
		$this->plugin->writeOutboundData($p);
	}

	public function getLastHeartbeat(): float {
		return $this->lastHeartbeat;
	}
}