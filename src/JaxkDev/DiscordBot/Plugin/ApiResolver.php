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

use JaxkDev\DiscordBot\Communication\Packets\Resolution;
use JaxkDev\DiscordBot\Libs\React\Promise\Deferred;
use JaxkDev\DiscordBot\Libs\React\Promise\PromiseInterface;

/**
 * For internal use only, maps any API Call packets going out by their UID and then links a deferred to it.
 * @internal
 */
abstract class ApiResolver{

	/** @var Array<int, Deferred> */
	static private $map = [];

	static public function create(int $uid): PromiseInterface{
		if(isset(self::$map[$uid])){
			throw new \AssertionError("Packet {$uid} already linked to a promise resolver.");
		}
		$d = new Deferred();
		self::$map[$uid] = $d;
		return $d->promise();
	}

	static public function getPromise(int $uid): ?PromiseInterface{
		return isset(self::$map[$uid]) ? self::$map[$uid]->promise() : null;
	}

	static public function handleResolution(Resolution $packet): void{
		if(isset(self::$map[$packet->getPid()])){
			$d = self::$map[$packet->getPid()];
			if($packet->wasSuccessful()){
				$d->resolve(...$packet->getSuccessData());
			}else{
				$d->reject(new \Exception($packet->getRejectReason()??"No rejection reason provided."));
			}
		}
	}

	/*static public function resolve(int $uid, ...$data): void{
		if(!isset(self::$map[$uid])){
			throw new \AssertionError("Packet {$uid} already resolved or no promise created.");
		}
		self::$map[$uid]->resolve(...$data);
		unset(self::$map[$uid]);
	}

	static public function reject(int $uid, ?string $reason = null): void{
		if(!isset(self::$map[$uid])){
			throw new \AssertionError("Packet {$uid} already resolved or no promise created.");
		}
		self::$map[$uid]->reject(new \Exception($reason));
		unset(self::$map[$uid]);
	}*/
}