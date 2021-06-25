<?php /** @noinspection PhpUnhandledExceptionInspection */

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

use Discord\Parts\Channel\Channel as DiscordChannel;
use Discord\Parts\Channel\Message as DiscordMessage;
use Discord\Parts\Guild\Guild as DiscordGuild;
use Discord\Parts\Guild\Invite as DiscordInvite;
use Discord\Parts\User\Activity as DiscordActivity;
use Discord\Parts\User\Member as DiscordMember;
use Discord\Parts\User\User as DiscordUser;
use Discord\Repository\Guild\InviteRepository as DiscordInviteRepository;
use JaxkDev\DiscordBot\Bot\Client;
use JaxkDev\DiscordBot\Bot\ModelConverter;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestBroadcastTyping;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestDeleteChannel;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestDeleteMessage;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestEditMessage;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestInitialiseBan;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestInitialiseInvite;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestKickMember;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestRevokeBan;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestRevokeInvite;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestUpdateNickname;
use JaxkDev\DiscordBot\Models\Activity;
use JaxkDev\DiscordBot\Communication\Packets\Resolution;
use JaxkDev\DiscordBot\Communication\Packets\Heartbeat;
use JaxkDev\DiscordBot\Communication\Packets\Packet;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestSendMessage;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestUpdateActivity;
use JaxkDev\DiscordBot\Communication\Protocol;
use pocketmine\utils\MainLogger;

class CommunicationHandler{

	/** @var Client */
	private $client;

	/** @var float|null */
	private $lastHeartbeat = null;

	public function __construct(Client $client){
		$this->client = $client;
	}

	//--- Handlers:

	public function handle(Packet $pk): void{
		//Internals:
		if($pk instanceof Heartbeat){
			$this->lastHeartbeat = $pk->getHeartbeat();
			return;
		}

		//API Check:
		if($this->client->getThread()->getStatus() !== Protocol::THREAD_STATUS_READY){
			$this->resolveRequest($pk->getUID(), false, "Thread not ready for API Requests.");
			return;
		}

		//API Packets:
		if($pk instanceof RequestUpdateNickname) $this->handleUpdateNickname($pk);
		elseif($pk instanceof RequestUpdateActivity) $this->handleUpdateActivity($pk);
		elseif($pk instanceof RequestSendMessage) $this->handleSendMessage($pk);
		elseif($pk instanceof RequestEditMessage) $this->handleEditMessage($pk);
		elseif($pk instanceof RequestDeleteMessage) $this->handleDeleteMessage($pk);
		elseif($pk instanceof RequestKickMember) $this->handleKickMember($pk);
		elseif($pk instanceof RequestInitialiseBan) $this->handleInitialiseBan($pk);
		elseif($pk instanceof RequestRevokeBan) $this->handleRevokeBan($pk);
		elseif($pk instanceof RequestInitialiseInvite) $this->handleInitialiseInvite($pk);
		elseif($pk instanceof RequestRevokeInvite) $this->handleRevokeInvite($pk);
		elseif($pk instanceof RequestBroadcastTyping) $this->handleBroadcastTyping($pk);
		elseif($pk instanceof RequestDeleteChannel) $this->handleDeleteChannel($pk);
	}

	private function handleDeleteChannel(RequestDeleteChannel $pk): void{
		$this->getServer($pk, $pk->getServerId(), function(DiscordGuild $guild) use($pk){
			$this->getChannel($pk, $pk->getChannelId(), function(DiscordChannel $channel) use($guild, $pk){
				$guild->channels->delete($channel)->then(function() use($pk){
					$this->resolveRequest($pk->getUID(), true, "Channel deleted.");
				}, function(\Throwable $e) use($pk){
					$this->resolveRequest($pk->getUID(), false, "Failed to delete channel.", [$e->getMessage(), $e->getTraceAsString()]);
					MainLogger::getLogger()->debug("Failed to delete channel ({$pk->getUID()}) - {$e->getMessage()}");
				});
			});
		});
	}

	private function handleBroadcastTyping(RequestBroadcastTyping $pk): void{
		$this->getChannel($pk, $pk->getChannelId(), function(DiscordChannel $channel) use($pk){
			$channel->broadcastTyping()->done(function() use($pk){
				$this->resolveRequest($pk->getUID());
				MainLogger::getLogger()->debug("BroadcastTyping - success ({$pk->getUID()})");
			}, function(\Throwable $e) use($pk){
				$this->resolveRequest($pk->getUID(), false, "Failed to broadcast typing.", [$e->getMessage(), $e->getTraceAsString()]);
				MainLogger::getLogger()->debug("Failed to broadcast typing ({$pk->getUID()}) - {$e->getMessage()}");
			});
		});
	}

	private function handleUpdateNickname(RequestUpdateNickname $pk): void{
		$this->getMember($pk, $pk->getServerId(), $pk->getUserId(), function(DiscordMember $dMember) use($pk){
			$dMember->setNickname($pk->getNickname())->done(function() use($pk){
				$this->resolveRequest($pk->getUID(), true, "Updated nickname.");
			}, function(\Throwable $e) use($pk){
				$this->resolveRequest($pk->getUID(), false, "Failed to update nickname.", [$e->getMessage(), $e->getTraceAsString()]);
				MainLogger::getLogger()->debug("Failed to update nickname ({$pk->getUID()}) - {$e->getMessage()}");
			});
		});
	}

	private function handleUpdateActivity(RequestUpdateActivity $pk): void{
		$activity = $pk->getActivity();
		$presence = new DiscordActivity($this->client->getDiscordClient(), [
			'name' => $activity->getMessage(),
			'type' => $activity->getType()
		]);

		try{
			$this->client->getDiscordClient()->updatePresence($presence, $activity->getStatus() === Activity::STATUS_IDLE, $activity->getStatus());
			$this->resolveRequest($pk->getUID());
		}catch (\Throwable $e){
			$this->resolveRequest($pk->getUID(), false, $e->getMessage());
		}
	}

	private function handleSendMessage(RequestSendMessage $pk): void{
		$this->getChannel($pk, $pk->getMessage()->getChannelId(), function(DiscordChannel $channel) use($pk){
			//TODO Embeds / Model->DiscordModel.
			$channel->sendMessage($pk->getMessage()->getContent())->done(function(DiscordMessage $msg) use($pk){
				$this->resolveRequest($pk->getUID(), true, "Message sent.", [ModelConverter::genModelMessage($msg)]);
				MainLogger::getLogger()->debug("Sent message ({$pk->getUID()})");
			}, function(\Throwable $e) use($pk){
				$this->resolveRequest($pk->getUID(), false, "Failed to send.", [$e->getMessage(), $e->getTraceAsString()]);
				MainLogger::getLogger()->debug("Failed to send message ({$pk->getUID()}) - {$e->getMessage()}");
			});
		});
	}

	private function handleEditMessage(RequestEditMessage $pk): void{
		$message = $pk->getMessage();
		if($message->getId() === null){
			$this->resolveRequest($pk->getUID(), false, "No message ID provided.");
			return;
		}
		$this->getChannel($pk, $message->getChannelId(), function(DiscordChannel $channel) use($pk, $message){
			$channel->messages->fetch($message->getId())->done(function(DiscordMessage $dMessage) use ($pk, $message){
				$dMessage->content = $message->getContent();
				//$dMessage->embeds = x.y.z;
				$dMessage->channel->messages->save($dMessage)->done(function(DiscordMessage $dMessage) use ($pk){
					$this->resolveRequest($pk->getUID(), true, "Message edited.", [ModelConverter::genModelMessage($dMessage)]);
				}, function(\ThreadException $e) use ($pk){
					$this->resolveRequest($pk->getUID(), false, "Failed to edit message.", [$e->getMessage(), $e->getTraceAsString()]);
					MainLogger::getLogger()->debug("Failed to edit message ({$pk->getUID()}) - {$e->getMessage()}");
				});
			}, function(\Throwable $e) use($pk){
				$this->resolveRequest($pk->getUID(), false, "Failed to edit message.", [$e->getMessage(), $e->getTraceAsString()]);
				MainLogger::getLogger()->debug("Failed to edit message ({$pk->getUID()}) - message error: {$e->getMessage()}");
			});
		});
	}

	private function handleDeleteMessage(RequestDeleteMessage $pk): void{
		$message = $pk->getMessage();
		if($message->getId() === null){
			$this->resolveRequest($pk->getUID(), false, "No message ID provided.");
			return;
		}
		$this->getChannel($pk, $message->getChannelId(), function(DiscordChannel $channel) use($pk, $message){
			$channel->messages->fetch($message->getId())->done(function(DiscordMessage $dMessage) use ($pk){
				$dMessage->delete()->done(function() use ($pk){
					$this->resolveRequest($pk->getUID());
				}, function(\ThreadException $e) use ($pk){
					$this->resolveRequest($pk->getUID(), false, "Failed to delete message.", [$e->getMessage(), $e->getTraceAsString()]);
					MainLogger::getLogger()->debug("Failed to delete message ({$pk->getUID()}) - {$e->getMessage()}");
				});
			}, function(\Throwable $e) use($pk){
				$this->resolveRequest($pk->getUID(), false, "Failed to delete message.", [$e->getMessage(), $e->getTraceAsString()]);
				MainLogger::getLogger()->debug("Failed to delete message ({$pk->getUID()}) - message error: {$e->getMessage()}");
			});
		});
	}

	private function handleKickMember(RequestKickMember $pk): void{
		$this->getMember($pk, $pk->getServerId(), $pk->getUserId(), function(DiscordMember $member, DiscordGuild $guild) use($pk){
			$guild->members->kick($member)->then(function() use($pk){
				$this->resolveRequest($pk->getUID(), true, "Member kicked.");
			}, function(\Throwable $e) use($pk){
				$this->resolveRequest($pk->getUID(), false, "Failed to kick member.", [$e->getMessage(), $e->getTraceAsString()]);
				MainLogger::getLogger()->debug("Failed to kick member ({$pk->getUID()}) - {$e->getMessage()}");
			});
		});
	}

	private function handleInitialiseBan(RequestInitialiseBan $pk): void{
		$this->getServer($pk, $pk->getBan()->getServerId(), function(DiscordGuild $guild) use($pk){
			$guild->bans->ban($pk->getBan()->getUserId(), $pk->getBan()->getDaysToDelete(), $pk->getBan()->getReason())->then(function() use($pk){
				$this->resolveRequest($pk->getUID(), true, "Member banned.");
			}, function(\Throwable $e) use($pk){
				$this->resolveRequest($pk->getUID(), false, "Failed to ban member.", [$e->getMessage(), $e->getTraceAsString()]);
				MainLogger::getLogger()->debug("Failed to ban member ({$pk->getUID()}) - {$e->getMessage()}");
			});
		});
	}

	private function handleRevokeBan(RequestRevokeBan $pk): void{
		$this->getServer($pk, $pk->getServerId(), function(DiscordGuild $guild) use($pk){
			$guild->unban($pk->getUserId())->then(function() use($pk){
				$this->resolveRequest($pk->getUID(), true, "Member unbanned.");
			}, function(\Throwable $e) use($pk){
				$this->resolveRequest($pk->getUID(), false, "Failed to unban member.", [$e->getMessage(), $e->getTraceAsString()]);
				MainLogger::getLogger()->debug("Failed to unban member ({$pk->getUID()}) - {$e->getMessage()}");
			});
		});
	}

	private function handleInitialiseInvite(RequestInitialiseInvite $pk): void{
		$invite = $pk->getInvite();
		$this->getChannel($pk, $invite->getChannelId(), function(DiscordChannel $channel) use($pk, $invite){
			/** @phpstan-ignore-next-line Poorly documented function on discord.php's side. */
			$channel->createInvite([
				"max_age" => $invite->getMaxAge(), "max_uses" => $invite->getMaxUses(), "temporary" => $invite->isTemporary(), "unique" => true
			])->done(function(DiscordInvite $dInvite) use($pk){
				$this->resolveRequest($pk->getUID(), true, "Invite initialised.", [ModelConverter::genModelInvite($dInvite)]);
				MainLogger::getLogger()->debug("Invite initialised ({$pk->getUID()})");
			}, function(\Throwable $e) use($pk){
				$this->resolveRequest($pk->getUID(), false, "Failed to initialise.", [$e->getMessage(), $e->getTraceAsString()]);
				MainLogger::getLogger()->debug("Failed to initialise invite ({$pk->getUID()}) - {$e->getMessage()}");
			});
		});
	}

	private function handleRevokeInvite(RequestRevokeInvite $pk): void{
		$this->getServer($pk, $pk->getServerId(), function(DiscordGuild $guild) use($pk){
			$guild->invites->freshen()->done(function(DiscordInviteRepository $invites) use($pk){
				/** @var DiscordInvite $dInvite */
				$dInvite = $invites->offsetGet($pk->getInviteCode());
				$invites->delete($dInvite)->done(function(DiscordInvite $dInvite) use($pk){
					$this->resolveRequest($pk->getUID(), true, "Invite revoked.", [ModelConverter::genModelInvite($dInvite)]);
					MainLogger::getLogger()->debug("Invite revoked ({$pk->getUID()})");
				}, function(\Throwable $e) use($pk){
					$this->resolveRequest($pk->getUID(), false, "Failed to revoke.", [$e->getMessage(), $e->getTraceAsString()]);
					MainLogger::getLogger()->debug("Failed to revoke invite ({$pk->getUID()}) - {$e->getMessage()}");
				});
			}, function(\Throwable $e) use($pk){
				$this->resolveRequest($pk->getUID(), false, "Failed to freshen invites.", [$e->getMessage(), $e->getTraceAsString()]);
				MainLogger::getLogger()->debug("Failed to revoke invite ({$pk->getUID()}) - invite freshen error: {$e->getMessage()}");
			});
		});
	}

	//---------------------------------------------------

	private function getServer(Packet $pk, string $server_id, callable $cb): void{
		$this->client->getDiscordClient()->guilds->fetch($server_id)->done(function(DiscordGuild $guild) use($cb){
			$cb($guild);
		}, function(\Throwable $e) use($pk){
			$this->resolveRequest($pk->getUID(), false, "Failed to fetch server.", [$e->getMessage(), $e->getTraceAsString()]);
			MainLogger::getLogger()->debug("Failed to process request (".get_class($pk)."|{$pk->getUID()}) - server error: {$e->getMessage()}");
		});
	}

	//Includes DM Channels.
	private function getChannel(Packet $pk, string $channel_id, callable $cb): void{
		$c = $this->client->getDiscordClient()->getChannel($channel_id);
		if($c === null){
			/** @var DiscordUser|null $u */
			$u = $this->client->getDiscordClient()->users->offsetGet($channel_id);
			if($u === null){
				$this->resolveRequest($pk->getUID(), false, "Failed to find channel/user.", ["Failed to find channel from local storage."]);
				MainLogger::getLogger()->debug("Failed to process request (".get_class($pk)."|{$pk->getUID()}) - channel error: Failed to find channel from local storage.");
			}else{
				$u->getPrivateChannel()->then(function(DiscordChannel $channel) use($cb){
					$cb($channel);
				}, function(\Throwable $e) use($pk){
					$this->resolveRequest($pk->getUID(), false, "Failed to fetch private channel..", [$e->getMessage(), $e->getTraceAsString()]);
					MainLogger::getLogger()->debug("Failed to process request (".get_class($pk)."|{$pk->getUID()}) - private channel error: {$e->getMessage()}");
				});
			}
		}else{
			$cb($c);
		}
	}

	private function getMember(Packet $pk, string $server_id, string $user_id, callable $cb): void{
		$this->getServer($pk, $server_id, function(DiscordGuild $guild) use($pk, $user_id, $cb){
			$guild->members->fetch($user_id)->then(function(DiscordMember $member) use($guild, $cb){
				$cb($member, $guild);
			}, function(\Throwable $e) use($pk){
				$this->resolveRequest($pk->getUID(), false, "Failed to fetch member.", [$e->getMessage(), $e->getTraceAsString()]);
				MainLogger::getLogger()->debug("Failed to process request (".get_class($pk)."|{$pk->getUID()}) - member error: {$e->getMessage()}");
			});
		});
	}

	//---------------------------------------------------

	private function resolveRequest(int $pid, bool $successful = true, string $response = "Success.", array $data = []): void{
		$pk = new Resolution();
		$pk->setPid($pid);
		$pk->setSuccessful($successful);
		$pk->setResponse($response);
		$pk->setData($data);
		$this->client->getThread()->writeOutboundData($pk);
	}

	public function sendHeartbeat(): void{
		$pk = new Heartbeat();
		$pk->setHeartbeat(microtime(true));
		$this->client->getThread()->writeOutboundData($pk);
	}

	public function checkHeartbeat(): void{
		if($this->lastHeartbeat === null) return;
		if(($diff = (microtime(true) - $this->lastHeartbeat)) > Protocol::HEARTBEAT_ALLOWANCE){
			MainLogger::getLogger()->emergency("Plugin has not responded for {$diff} seconds, closing thread.");
			$this->client->close();
		}
	}

	public function getLastHeartbeat(): ?float{
		return $this->lastHeartbeat;
	}
}