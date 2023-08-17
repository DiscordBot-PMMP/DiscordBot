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

/** @template-covariant T */
interface BinarySerializable{

    /**
     * All serializable CLASSES (not enums) must have a unique ID to identify them.
     * IDs must be unique, and must not be changed.
     * Modifying this value will break compatibility with other versions.
     * @var int<0, 65535>
     * @internal
     */
    public const SERIALIZE_ID = 0;

    /**
     * @internal
     * @throws BinaryDataException If the packet data is invalid, should never happen.
     */
    public function binarySerialize(): BinaryStream;

    /**
     * @internal
     * @return BinarySerializable<T>
     * @throws BinaryDataException If the packet data is invalid, may happen on external thread inbound.
     */
    public static function fromBinary(BinaryStream $stream): self;
}