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

abstract class Packet{

    // Used for responses.
    public static int $UID_COUNT = 0;

    protected int $UID;

    public function __construct(){
        Packet::$UID_COUNT += 2;  //BotThread = Odd, PluginThread = Even. (Keeps them unique, *shrugs*)
        $this->UID = Packet::$UID_COUNT;
    }

    public function getUID(): int{
        return $this->UID;
    }

    // Explicit serialization to significantly reduce serialized size.

    public abstract function __serialize(): array;

    public abstract function __unserialize(array $data): void;
}