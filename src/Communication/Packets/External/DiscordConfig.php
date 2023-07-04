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

namespace JaxkDev\DiscordBot\Communication\Packets\External;

use JaxkDev\DiscordBot\Communication\Packets\Packet;
use JaxkDev\DiscordBot\Communication\BinaryStream;

/** @extends Packet<DiscordConfig> */
class DiscordConfig extends Packet{

    public const SERIALIZE_ID = 3;

    private array $config;

    public function __construct(array $config, ?int $uid = null){
        parent::__construct($uid);
        $this->config = $config;
    }

    public function getConfig(): array{
        return $this->config;
    }

    //config is just a string. (json parsed from string)
    public function binarySerialize(): BinaryStream{
        $stream = new BinaryStream();
        $data = json_encode($this->config, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if($data === false){
            throw new \RuntimeException("Failed to encode config JSON to string.");
        }
        $stream->putString($data);
        return $stream;
    }

    public static function fromBinary(BinaryStream $stream): self{
        $data = $stream->getString();
        $config = json_decode($data, true);
        if($config === null){
            throw new \RuntimeException("Failed to decode config JSON from string.");
        }
        return new self(
            (array)$config
        );
    }
}