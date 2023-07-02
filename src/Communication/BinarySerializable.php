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

namespace JaxkDev\DiscordBot\Communication;

use pocketmine\utils\BinaryDataException;

interface BinarySerializable{

    // Serialise to a specific network format.

    /**
     * @throws BinaryDataException If the packet data is invalid, should never happen.
     */
    public function binarySerialize(): BinaryStream;

    /**
     * @throws BinaryDataException If the packet data is invalid, may happen on external thread inbound.
     */
    public static function fromBinary(BinaryStream $stream): self;
}