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

namespace JaxkDev\DiscordBot\Plugin;

use JaxkDev\DiscordBot\Communication\Models\Channel;
use JaxkDev\DiscordBot\Communication\Models\Member;
use JaxkDev\DiscordBot\Communication\Models\Role;
use JaxkDev\DiscordBot\Communication\Models\Server;
use JaxkDev\DiscordBot\Communication\Models\User;

/*
 * Notes:
 * Dont use array_search or foreach, way too slow on large arrays.
 * (~= 10ms to search for one user in a 30k db
 *
 * unset() on the removes doesnt destroy the objects until all references are unset....
 */

class Storage{

	/** @var Array<string, Server> */
	private static $serverMap = [];

	/** @var Array<string, Channel> */
	private static $channelMap = [];

	/** @var Array<string, string[]> */
	private static $channelServerMap = [];

	/** @var Array<string, Member> */
	private static $memberMap = [];

	/** @var Array<string, string[]> */
	private static $memberServerMap = [];

	/** @var Array<string, User> */
	private static $userMap = [];

	/** @var Array<string, Role> */
	private static $roleMap = [];

	/** @var Array<string, string[]> */
	private static $roleServerMap = [];

	/** @var null|User */
	private static $botUser = null;

	/** @var int */
	private static $timestamp = 0;

	public static function getServer(string $id): ?Server{
		return self::$serverMap[$id] ?? null;
	}

	public static function addServer(Server $server): void{
		self::$serverMap[$server->getId()] = $server;
		if(!isset(self::$channelServerMap[$server->getId()])) self::$channelServerMap[$server->getId()] = [];
		if(!isset(self::$memberServerMap[$server->getId()])) self::$memberServerMap[$server->getId()] = [];
		if(!isset(self::$roleServerMap[$server->getId()])) self::$roleServerMap[$server->getId()] = [];
	}

	/**
	 * NOTICE, Removes all linked members,channels and roles.
	 * @param string $serverId
	 */
	public static function removeServer(string $serverId): void{
		if(!isset(self::$serverMap[$serverId])) return; //Was never added or already deleted.
		unset(self::$serverMap[$serverId]);
		//Remove servers channels.
		foreach(self::$channelServerMap[$serverId] as $cid){
			unset(self::$channelMap[$cid]);
		}
		unset(self::$channelServerMap[$serverId]);
		//Remove servers members.
		foreach(self::$memberServerMap[$serverId] as $mid){
			unset(self::$memberMap[$mid]);
		}
		unset(self::$memberServerMap[$serverId]);
		//Remove servers roles.
		foreach(self::$roleServerMap[$serverId] as $rid){
			unset(self::$roleMap[$rid]);
		}
		unset(self::$roleServerMap[$serverId]);
	}

	public static function getChannel(string $id): ?Channel{
		return self::$channelMap[$id] ?? null;
	}

	/**
	 * @param string $serverId
	 * @return Channel[]
	 */
	public static function getChannelsByServer(string $serverId): array{
		$channels = [];
		foreach((self::$channelServerMap[$serverId] ?? []) as $id){
			$c = self::getChannel($id);
			if($c !== null) $channels[] = $c;
		}
		return $channels;
	}

	public static function addChannel(Channel $channel): void{
		self::$channelServerMap[$channel->getServerId()][] = $channel->getId();
		self::$channelMap[$channel->getId()] = $channel;
	}

	public static function removeChannel(string $channelId): void{
		$channel = self::getChannel($channelId);
		if($channel === null) return; //Already deleted or not added.
		$serverId = $channel->getServerId();
		unset(self::$channelMap[$channelId]);
		$i = array_search($channelId, self::$channelServerMap[$serverId], true);
		if($i === false || is_string($i)) return; //Not in this servers channel map.
		array_splice(self::$channelServerMap[$serverId], $i, 1);
	}

	public static function getMember(string $id): ?Member{
		return self::$memberMap[$id] ?? null;
	}

	/**
	 * @param string $serverId
	 * @return Member[]
	 */
	public static function getMembersByServer(string $serverId): array{
		$members = [];
		foreach((self::$memberServerMap[$serverId] ?? []) as $id){
			$m = self::getMember($id);
			if($m !== null) $members[] = $m;
		}
		return $members;
	}

	public static function addMember(Member $member): void{
		self::$memberServerMap[$member->getServerId()][] = $member->getId();
		self::$memberMap[$member->getId()] = $member;
	}

	public static function removeMember(string $memberID): void{
		$member = self::getMember($memberID);
		if($member === null) return; //Already deleted or not added.
		$serverId = $member->getServerId();
		unset(self::$memberMap[$memberID]);
		$i = array_search($memberID, self::$memberServerMap[$serverId], true);
		if($i === false || is_string($i)) return; //Not in this servers member map.
		array_splice(self::$memberServerMap[$serverId], $i, 1);
	}

	public static function getUser(string $id): ?User{
		return self::$userMap[$id] ?? null;
	}

	public static function addUser(User $user): void{
		self::$userMap[$user->getId()] = $user;
	}

	public static function removeUser(string $userId): void{
		unset(self::$userMap[$userId]);
	}

	public static function getRole(string $id): ?Role{
		return self::$roleMap[$id] ?? null;
	}

	/**
	 * @param string $serverId
	 * @return Role[]
	 */
	public static function getRolesByServer(string $serverId): array{
		$roles = [];
		foreach((self::$roleServerMap[$serverId] ?? []) as $id){
			$r = self::getRole($id);
			if($r !== null) $roles[] = $r;
		}
		return $roles;
	}

	public static function addRole(Role $role): void{
		self::$roleServerMap[$role->getServerId()][] = $role->getId();
		self::$roleMap[$role->getId()] = $role;
	}

	public static function removeRole(string $roleID): void{
		$role = self::getRole($roleID);
		if($role === null) return; //Already deleted or not added.
		$serverId = $role->getServerId();
		unset(self::$roleMap[$roleID]);
		$i = array_search($roleID, self::$roleServerMap[$serverId], true);
		if($i === false || is_string($i)) return; //Not in this servers role map.
		array_splice(self::$roleServerMap[$serverId], $i, 1);
	}

	public static function getBotUser(): ?User{
		return self::$botUser;
	}

	public static function setBotUser(User $user): void{
		self::$botUser = $user;
	}

	public static function getBotMemberByServer(string $serverId): ?Member{
		$u = self::getBotUser();
		if($u === null) return null;
		return self::getMember("{$serverId}.{$u->getId()}");
	}

	public static function getTimestamp(): int{
		return self::$timestamp;
	}

	public static function setTimestamp(int $timestamp): void{
		self::$timestamp = $timestamp;
	}

	public static function reset(): void{
		self::$serverMap = [];
		self::$channelServerMap = [];
		self::$channelMap = [];
		self::$roleMap = [];
		self::$roleServerMap = [];
		self::$memberMap = [];
		self::$memberServerMap = [];
		self::$userMap = [];
		self::$botUser = null;
		self::$timestamp = 0;
	}
}