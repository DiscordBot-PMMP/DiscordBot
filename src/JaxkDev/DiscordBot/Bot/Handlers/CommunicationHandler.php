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

	public function handle(Packet $packet): bool{
		if($packet instanceof Heartbeat){
			$this->lastHeartbeat = $packet->getHeartbeat();
			return true;
		}

		//API:
		if($this->client->getThread()->getStatus() !== Protocol::THREAD_STATUS_READY){
			$this->resolveRequest($packet->getUID(), false, "Thread not ready for API Requests.");
			return false;
		}
		if($packet instanceof RequestUpdateNickname) return $this->handleUpdateNickname($packet);
		if($packet instanceof RequestUpdateActivity) return $this->handleUpdateActivity($packet);
		if($packet instanceof RequestSendMessage) return $this->handleSendMessage($packet);
		if($packet instanceof RequestEditMessage) return $this->handleEditMessage($packet);
		if($packet instanceof RequestDeleteMessage) return $this->handleDeleteMessage($packet);
		if($packet instanceof RequestKickMember) return $this->handleKickMember($packet);
		if($packet instanceof RequestInitialiseBan) return $this->handleInitialiseBan($packet);
		if($packet instanceof RequestRevokeBan) return $this->handleRevokeBan($packet);
		if($packet instanceof RequestInitialiseInvite) return $this->handleInitialiseInvite($packet);
		if($packet instanceof RequestRevokeInvite) return $this->handleRevokeInvite($packet);
		return false;
	}

	private function handleUpdateNickname(RequestUpdateNickname $packet): bool{
		$pid = $packet->getUID();
		$member = $packet->getMember();

		/** @noinspection PhpUnhandledExceptionInspection */ //Impossible
		$this->client->getDiscordClient()->guilds->fetch($member->getServerId())->then(function(DiscordGuild $guild) use($pid, $member){
			$guild->members->fetch($member->getUserId())->then(function(DiscordMember $dMember) use($pid, $member){
				$dMember->setNickname($member->getNickname())->done(function() use($pid){
					$this->resolveRequest($pid, true, "Updated nickname.");
				}, function(\Throwable $e) use($pid){
					$this->resolveRequest($pid, false, "Failed to update nickname.", [$e->getMessage(), $e->getTraceAsString()]);
					MainLogger::getLogger()->debug("Failed to update nickname ({$pid}) - {$e->getMessage()}");
				});
			}, function(\Throwable $e) use($pid){
				$this->resolveRequest($pid, false, "Failed to fetch member.", [$e->getMessage(), $e->getTraceAsString()]);
				MainLogger::getLogger()->debug("Failed to update nickname ({$pid}) - member error: {$e->getMessage()}");
			});
		}, function(\Throwable $e) use($pid){
			$this->resolveRequest($pid, false, "Failed to fetch server.", [$e->getMessage(), $e->getTraceAsString()]);
			MainLogger::getLogger()->debug("Failed to update nickname ({$pid}) - server error: {$e->getMessage()}");
		});
		return true;
	}

	private function handleUpdateActivity(RequestUpdateActivity $packet): bool{
		$activity = $packet->getActivity();
		$presence = new DiscordActivity($this->client->getDiscordClient(), [
			'name' => $activity->getMessage(),
			'type' => $activity->getType()
		]);

		try{
			$this->client->getDiscordClient()->updatePresence($presence, $activity->getStatus() === Activity::STATUS_IDLE, $activity->getStatus());
			$this->resolveRequest($packet->getUID());
		}catch (\Throwable $e){
			$this->resolveRequest($packet->getUID(), false, $e->getMessage());
		}
		return true;
	}

	private function handleSendMessage(RequestSendMessage $packet): bool{
		$pid = $packet->getUID();
		$message = $packet->getMessage();

		// DM.
		if($message->getServerId() === null){
			/** @noinspection PhpUnhandledExceptionInspection */ //Impossible
			$this->client->getDiscordClient()->users->fetch($message->getChannelId())->done(function(DiscordUser $user) use($pid, $message){
				//User::sendMessage handles getting DM channel.
				$user->sendMessage($message->getContent())->done(function(DiscordMessage $message) use($pid){
					$this->resolveRequest($pid, true, "Sent DM.", [ModelConverter::genModelMessage($message)]);
				}, function(\Throwable $e) use($pid){
					$this->resolveRequest($pid, false, "Failed to send.", [$e->getMessage(), $e->getTraceAsString()]);
					MainLogger::getLogger()->debug("Failed to send dm ({$pid}) - {$e->getMessage()}");
				});
			}, function(\Throwable $e) use($pid){
				$this->resolveRequest($pid, false, "Failed to fetch user.", [$e->getMessage(), $e->getTraceAsString()]);
				MainLogger::getLogger()->debug("Failed to send dm ({$pid}) - user error: {$e->getMessage()}");
			});
			return true;
		}

		/** @noinspection PhpUnhandledExceptionInspection */ //Impossible.
		$this->client->getDiscordClient()->guilds->fetch($message->getServerId())->done(function(DiscordGuild $guild) use($pid, $message){
			$guild->channels->fetch($message->getChannelId())->done(function(DiscordChannel $channel) use($pid, $message){
				$channel->sendMessage($message->getContent())->done(function(DiscordMessage $msg) use($pid){
					$this->resolveRequest($pid, true, "Message sent.", [ModelConverter::genModelMessage($msg)]);
					MainLogger::getLogger()->debug("Sent message ({$pid})");
				}, function(\Throwable $e) use($pid){
					$this->resolveRequest($pid, false, "Failed to send.", [$e->getMessage(), $e->getTraceAsString()]);
					MainLogger::getLogger()->debug("Failed to send message ({$pid}) - {$e->getMessage()}");
				});
			}, function(\Throwable $e) use($pid){
				$this->resolveRequest($pid, false, "Failed to fetch channel.", [$e->getMessage(), $e->getTraceAsString()]);
				MainLogger::getLogger()->debug("Failed to send message ({$pid}) - channel error: {$e->getMessage()}");
			});
		}, function(\Throwable $e) use($pid){
			$this->resolveRequest($pid, false, "Failed to fetch server.", [$e->getMessage(), $e->getTraceAsString()]);
			MainLogger::getLogger()->debug("Failed to send message ({$pid}) - server error: {$e->getMessage()}");
		});
		return true;
	}

	private function handleEditMessage(RequestEditMessage $packet): bool{
		$pid = $packet->getUID();
		$message = $packet->getMessage();
		$id = $message->getId();
		$channel = $this->client->getDiscordClient()->getChannel($message->getChannelId());
		if($channel === null){
			$this->resolveRequest($pid, false, "Failed to fetch channel.");
			return true;
		}
		if($id === null){
			$this->resolveRequest($pid, false, "No message ID provided.");
			return true;
		}
		/** @noinspection PhpUnhandledExceptionInspection */ //Impossible
		$channel->messages->fetch($id)->done(function(DiscordMessage $dMessage) use($pid, $message){
			$dMessage->content = $message->getContent();
			//$dMessage->embeds = x.y.z;
			$dMessage->channel->messages->save($dMessage)->done(function(DiscordMessage $dMessage) use($pid){
				$this->resolveRequest($pid, true, "Message edited.", [ModelConverter::genModelMessage($dMessage)]);
			}, function(\ThreadException $e) use($pid){
				$this->resolveRequest($pid, false, "Failed to edit message.", [$e->getMessage(), $e->getTraceAsString()]);
				MainLogger::getLogger()->debug("Failed to edit message ({$pid}) - {$e->getMessage()}");
			});
		});
		return true;
	}

	private function handleDeleteMessage(RequestDeleteMessage $packet): bool{
		$pid = $packet->getUID();
		$message = $packet->getMessage();$id = $message->getId();
		$channel = $this->client->getDiscordClient()->getChannel($message->getChannelId());
		if($channel === null){
			$this->resolveRequest($pid, false, "Failed to fetch channel.");
			return true;
		}
		if($id === null){
			$this->resolveRequest($pid, false, "No message ID provided.");
			return true;
		}
		/** @noinspection PhpUnhandledExceptionInspection */ //Impossible
		$channel->messages->fetch($id)->done(function(DiscordMessage $dMessage) use($pid){
			$dMessage->delete()->done(function() use($pid){
				$this->resolveRequest($pid);
			}, function(\ThreadException $e) use($pid){
				$this->resolveRequest($pid, false, "Failed to delete message.", [$e->getMessage(), $e->getTraceAsString()]);
				MainLogger::getLogger()->debug("Failed to delete message ({$pid}) - {$e->getMessage()}");
			});
		});
		return true;
	}

	private function handleKickMember(RequestKickMember $packet): bool{
		/** @noinspection PhpUnhandledExceptionInspection */ //Impossible
		$this->client->getDiscordClient()->guilds->fetch($packet->getMember()->getServerId())->then(function(DiscordGuild $guild) use($packet){
			$guild->members->fetch($packet->getMember()->getUserId())->then(function(DiscordMember $member) use($packet, $guild){
				$guild->members->kick($member)->then(function() use($packet){
					$this->resolveRequest($packet->getUID(), true, "Member kicked.");
				}, function(\Throwable $e) use($packet){
					$this->resolveRequest($packet->getUID(), false, "Failed to kick member.", [$e->getMessage(), $e->getTraceAsString()]);
					MainLogger::getLogger()->debug("Failed to kick member ({$packet->getUID()}) - {$e->getMessage()}");
				});
			}, function(\Throwable $e) use($packet){
				$this->resolveRequest($packet->getUID(), false, "Failed to fetch member.", [$e->getMessage(), $e->getTraceAsString()]);
				MainLogger::getLogger()->debug("Failed to kick member ({$packet->getUID()}) - member error: {$e->getMessage()}");
			});
		}, function(\Throwable $e) use($packet){
			$this->resolveRequest($packet->getUID(), false, "Failed to fetch server.", [$e->getMessage(), $e->getTraceAsString()]);
			MainLogger::getLogger()->debug("Failed to kick member ({$packet->getUID()}) - server error: {$e->getMessage()}");
		});
		return true;
	}

	private function handleInitialiseBan(RequestInitialiseBan $packet): bool{
		/** @noinspection PhpUnhandledExceptionInspection */ //Impossible
		$this->client->getDiscordClient()->guilds->fetch($packet->getBan()->getServerId())->then(function(DiscordGuild $guild) use($packet){
			$guild->bans->ban($packet->getBan()->getUserId())->then(function() use($packet){
				$this->resolveRequest($packet->getUID(), true, "Member banned.");
			}, function(\Throwable $e) use($packet){
				$this->resolveRequest($packet->getUID(), false, "Failed to ban member.", [$e->getMessage(), $e->getTraceAsString()]);
				MainLogger::getLogger()->debug("Failed to ban member ({$packet->getUID()}) - {$e->getMessage()}");
			});
		}, function(\Throwable $e) use($packet){
			$this->resolveRequest($packet->getUID(), false, "Failed to fetch server.", [$e->getMessage(), $e->getTraceAsString()]);
			MainLogger::getLogger()->debug("Failed to ban member ({$packet->getUID()}) - server error: {$e->getMessage()}");
		});
		return true;
	}

	private function handleRevokeBan(RequestRevokeBan $packet): bool{
		/** @noinspection PhpUnhandledExceptionInspection */ //Impossible
		$this->client->getDiscordClient()->guilds->fetch($packet->getBan()->getServerId())->then(function(DiscordGuild $guild) use($packet){
			$guild->unban($packet->getBan()->getUserId())->then(function() use($packet){
				$this->resolveRequest($packet->getUID(), true, "Member unbanned.");
			}, function(\Throwable $e) use($packet){
				$this->resolveRequest($packet->getUID(), false, "Failed to unban member.", [$e->getMessage(), $e->getTraceAsString()]);
				MainLogger::getLogger()->debug("Failed to unban member ({$packet->getUID()}) - {$e->getMessage()}");
			});
		}, function(\Throwable $e) use($packet){
			$this->resolveRequest($packet->getUID(), false, "Failed to fetch server.", [$e->getMessage(), $e->getTraceAsString()]);
			MainLogger::getLogger()->debug("Failed to unban member ({$packet->getUID()}) - server error: {$e->getMessage()}");
		});
		return true;
	}

	private function handleInitialiseInvite(RequestInitialiseInvite $pk): bool{
		$pid = $pk->getUID();
		$invite = $pk->getInvite();

		/** @noinspection PhpUnhandledExceptionInspection */ //Impossible. TODO Function getting channel.
		$this->client->getDiscordClient()->guilds->fetch($invite->getServerId())->done(function(DiscordGuild $guild) use($pid, $invite){
			$guild->channels->fetch($invite->getChannelId())->done(function(DiscordChannel $channel) use($pid, $invite){
				/** @phpstan-ignore-next-line Poorly documented function on discord.php's side. */
				$channel->createInvite([
					"max_age" => $invite->getMaxAge(), "max_uses" => $invite->getMaxUses(), "temporary" => $invite->isTemporary(), "unique" => true
				])->done(function(DiscordInvite $dInvite) use($pid){
					$this->resolveRequest($pid, true, "Invite initialised.", [ModelConverter::genModelInvite($dInvite)]);
					MainLogger::getLogger()->debug("Invite initialised ({$pid})");
				}, function(\Throwable $e) use($pid){
					$this->resolveRequest($pid, false, "Failed to initialise.", [$e->getMessage(), $e->getTraceAsString()]);
					MainLogger::getLogger()->debug("Failed to initialise invite ({$pid}) - {$e->getMessage()}");
				});
			}, function(\Throwable $e) use($pid){
				$this->resolveRequest($pid, false, "Failed to fetch channel.", [$e->getMessage(), $e->getTraceAsString()]);
				MainLogger::getLogger()->debug("Failed to initialise invite ({$pid}) - channel error: {$e->getMessage()}");
			});
		}, function(\Throwable $e) use($pid){
			$this->resolveRequest($pid, false, "Failed to fetch server.", [$e->getMessage(), $e->getTraceAsString()]);
			MainLogger::getLogger()->debug("Failed to initialise invite ({$pid}) - server error: {$e->getMessage()}");
		});
		return true;
	}

	private function handleRevokeInvite(RequestRevokeInvite $pk): bool{
		$pid = $pk->getUID();
		$invite = $pk->getInvite();

		/** @noinspection PhpUnhandledExceptionInspection */ //Impossible. TODO Function getting channel.
		$this->client->getDiscordClient()->guilds->fetch($invite->getServerId())->done(function(DiscordGuild $guild) use($pid, $invite){
			$guild->invites->freshen()->done(function(DiscordInviteRepository $invites) use($pid, $invite){
				/** @var DiscordInvite $dInvite */
				$dInvite = $invites->offsetGet($invite->getCode());
				$invites->delete($dInvite)->done(function(DiscordInvite $dInvite) use($pid){
					$this->resolveRequest($pid, true, "Invite revoked.", [ModelConverter::genModelInvite($dInvite)]);
					MainLogger::getLogger()->debug("Invite revoked ({$pid})");
				}, function(\Throwable $e) use($pid){
					$this->resolveRequest($pid, false, "Failed to revoke.", [$e->getMessage(), $e->getTraceAsString()]);
					MainLogger::getLogger()->debug("Failed to revoke invite ({$pid}) - {$e->getMessage()}");
				});
			}, function(\Throwable $e) use($pid){
				$this->resolveRequest($pid, false, "Failed to freshen invites.", [$e->getMessage(), $e->getTraceAsString()]);
				MainLogger::getLogger()->debug("Failed to revoke invite ({$pid}) - invite freshen error: {$e->getMessage()}");
			});
		}, function(\Throwable $e) use($pid){
			$this->resolveRequest($pid, false, "Failed to fetch server.", [$e->getMessage(), $e->getTraceAsString()]);
			MainLogger::getLogger()->debug("Failed to revoke invite ({$pid}) - server error: {$e->getMessage()}");
		});
		return true;
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
		$packet = new Heartbeat();
		$packet->setHeartbeat(microtime(true));
		$this->client->getThread()->writeOutboundData($packet);
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