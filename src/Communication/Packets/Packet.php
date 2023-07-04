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
use JaxkDev\DiscordBot\Communication\BinaryStream;

/**
 * @template-covariant T
 * @implements BinarySerializable<T>
 */
abstract class Packet implements BinarySerializable{

    public static int $UID_COUNT = 1;

    protected int $UID;

    public function __construct(?int $uid = null){
        if($uid === null){
            if(Packet::$UID_COUNT > 4294967295){
                //32bit int overflow, reset.
                Packet::$UID_COUNT = 1;
            }
            $this->UID = Packet::$UID_COUNT++;
        }else{
            $this->UID = $uid;
        }
    }

    public function getUID(): int{
        return $this->UID;
    }
}