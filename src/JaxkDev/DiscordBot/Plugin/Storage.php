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
 */

//todo, removes.

class Storage{

	/** @var Array<int, Server> */
	private static $serverMap = [];

	/** @var Array<string, int> */
	private static $serverNameMap = [];

	/** @var Array<int, Channel> */
	private static $channelMap = [];

	/** @var Array<string, int> */
	private static $channelNameMap = [];

	/** @var Array<string, Member> */
	private static $memberMap = [];

	/** @var Array<int, User> */
	private static $userMap = [];

	/** @var Array<int, Role> */
	private static $roleMap = [];

	/** @var int */
	private static $timestamp = 0;

	public static function getServer(int $id): ?Server{
		return self::$serverMap[$id];
	}

	public static function getServerByName(string $name): ?Server{
		return self::$serverMap[self::$serverNameMap[$name]];
	}

	public static function addServer(Server $server): void{
		self::$serverNameMap[$server->getName()] = $server->getId();
		self::$serverMap[$server->getId()] = $server;
	}

	public static function getChannel(int $id): ?Channel{
		return self::$channelMap[$id];
	}

	public static function getChannelByName(string $name): ?Channel{
		return self::$channelMap[self::$channelNameMap[$name]];
	}

	public static function addChannel(Channel $channel): void{
		self::$channelNameMap[$channel->getName()] = $channel->getId();
		self::$channelMap[$channel->getId()] = $channel;
	}

	public static function getMember(string $id): ?Member{
		return self::$memberMap[$id];
	}

	public static function addMember(Member $member): void{
		self::$memberMap[$member->getId()] = $member;
	}

	public static function getUser(int $id): ?User{
		return self::$userMap[$id];
	}

	public static function addUser(User $user): void{
		self::$userMap[$user->getId()] = $user;
	}

	public static function getRole(int $id): ?Role{
		return self::$roleMap[$id];
	}

	public static function addRole(Role $role): void{
		self::$roleMap[$role->getId()] = $role;
	}

	public static function getTimestamp(): int{
		return self::$timestamp;
	}

	public static function setTimestamp(int $timestamp): void{
		self::$timestamp = $timestamp;
	}

	public static function reset(): void{
		self::$serverNameMap = [];
		self::$serverMap = [];
		self::$channelNameMap = [];
		self::$channelMap = [];
		self::$roleMap = [];
		self::$memberMap = [];
		self::$userMap = [];
		self::$timestamp = 0;
	}
}