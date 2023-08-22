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

namespace JaxkDev\DiscordBot\Models\Messages;

use JaxkDev\DiscordBot\Communication\BinarySerializable;
use JaxkDev\DiscordBot\Communication\BinaryStream;
use JaxkDev\DiscordBot\Plugin\Utils;

/**
 * @implements BinarySerializable<Reference>
 * @link https://discord.com/developers/docs/resources/channel#message-reference-object-message-reference-structure
 * @link https://discord.com/developers/docs/resources/channel#message-types
 */
final class Reference implements BinarySerializable{

    private ?string $guild_id;

    private ?string $channel_id;

    private ?string $message_id;

    private ?bool $fail_if_not_exists;

    public function __construct(?string $guild_id = null, ?string $channel_id = null, ?string $message_id = null,
                                ?bool $fail_if_not_exists = null){
        $this->setGuildId($guild_id);
        $this->setChannelId($channel_id);
        $this->setMessageId($message_id);
        $this->setFailIfNotExists($fail_if_not_exists);
    }

    public function getGuildId(): ?string{
        return $this->guild_id;
    }

    public function setGuildId(?string $guild_id): void{
        if($guild_id !== null && !Utils::validDiscordSnowflake($guild_id)){
            throw new \AssertionError("Guild ID '$guild_id' is invalid.");
        }
        $this->guild_id = $guild_id;
    }

    public function getChannelId(): ?string{
        return $this->channel_id;
    }

    public function setChannelId(?string $channel_id): void{
        if($channel_id !== null && !Utils::validDiscordSnowflake($channel_id)){
            throw new \AssertionError("Channel ID '$channel_id' is invalid.");
        }
        $this->channel_id = $channel_id;
    }

    public function getMessageId(): ?string{
        return $this->message_id;
    }

    public function setMessageId(?string $message_id): void{
        if($message_id !== null && !Utils::validDiscordSnowflake($message_id)){
            throw new \AssertionError("Message ID '$message_id' is invalid.");
        }
        $this->message_id = $message_id;
    }

    public function getFailIfNotExists(): ?bool{
        return $this->fail_if_not_exists;
    }

    public function setFailIfNotExists(?bool $fail_if_not_exists): void{
        $this->fail_if_not_exists = $fail_if_not_exists;
    }

    public function binarySerialize(): BinaryStream{
        $stream = new BinaryStream();
        $stream->putNullableString($this->guild_id);
        $stream->putNullableString($this->channel_id);
        $stream->putNullableString($this->message_id);
        $stream->putNullableBool($this->fail_if_not_exists);
        return $stream;
    }

    public static function fromBinary(BinaryStream $stream): BinarySerializable{
        return new self(
            $stream->getNullableString(), // guild_id
            $stream->getNullableString(), // channel_id
            $stream->getNullableString(), // message_id
            $stream->getNullableBool()    // fail_if_not_exists
        );
    }
}