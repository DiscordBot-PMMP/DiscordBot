<?php
/*
 * DiscordBot, PocketMine-MP Plugin.
 *
 * Licensed under the Open Software License version 3.0 (OSL-3.0)
 * Copyright (C) 2020-present JaxkDev
 *
 * Twitter :: @JaxkDev
 * Discord :: JaxkDev
 * Email   :: JaxkDev@gmail.com
 */

namespace JaxkDev\DiscordBot\Communication\Packets;

use JaxkDev\DiscordBot\Communication\BinarySerializable;
use pocketmine\utils\BinaryStream;

abstract class Packet implements \JsonSerializable, BinarySerializable{

    // Each packet has a unique ID, this will not change.
    public const ID = 0;

    // Used for responses.
    public static int $UID_COUNT = 0;

    protected int $UID;

    public function __construct(?int $uid = null){
        if($uid === null){
            //Thread = Odd, Plugin = Even. (Keeps them unique, *shrugs*)
            Packet::$UID_COUNT += 2;
            $this->UID = Packet::$UID_COUNT;
        }else{
            $this->UID = $uid;
        }
    }

    public function getUID(): int{
        return $this->UID;
    }

    abstract public function binarySerialize(): BinaryStream;
    abstract public static function fromBinary(BinaryStream $stream): self; //Update self reference by adding abstract interface function.

    abstract public function jsonSerialize(): array;
    abstract public static function fromJson(array $data): self;
}