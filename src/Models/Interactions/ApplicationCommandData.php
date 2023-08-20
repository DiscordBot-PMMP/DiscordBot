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

namespace JaxkDev\DiscordBot\Models\Interactions;

use JaxkDev\DiscordBot\Communication\BinarySerializable;
use JaxkDev\DiscordBot\Communication\BinaryStream;

/**
 * @implements BinarySerializable<ApplicationCommandData>
 * @link https://discord.com/developers/docs/interactions/receiving-and-responding#interaction-object-application-command-data-structure
 */
class ApplicationCommandData implements BinarySerializable{

    //TODO

    public function binarySerialize(): BinaryStream{
        // TODO: Implement binarySerialize() method.
        return new BinaryStream();
    }

    public static function fromBinary(BinaryStream $stream): self{
        // TODO: Implement fromBinary() method.
        return new self();
    }
}