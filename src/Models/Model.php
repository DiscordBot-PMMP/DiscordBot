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

namespace JaxkDev\DiscordBot\Models;

use JaxkDev\DiscordBot\Communication\BinarySerializable;
use JaxkDev\DiscordBot\Communication\BinaryStream;

abstract class Model implements \JsonSerializable, BinarySerializable{

    public const SERIALIZE_ID = 0;

    abstract public function binarySerialize(): BinaryStream;
    abstract public static function fromBinary(BinaryStream $stream): self; //Update self reference by adding abstract interface function.

    abstract public function jsonSerialize(): array;
    abstract public static function fromJson(array $data): self;

}