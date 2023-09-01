<?php

/*
 * DiscordBot, PocketMine-MP Plugin.
 *
 * Licensed under the Open Software License version 3.0 (OSL-3.0)
 * Copyright (C) 2020-present JaxkDev
 *
 * Discord :: JaxkDev
 * Email   :: JaxkDev@gmail.com
 */

namespace JaxkDev\DiscordBot\Models\Messages\Embed;

use JaxkDev\DiscordBot\Communication\BinarySerializable;
use JaxkDev\DiscordBot\Communication\BinaryStream;
use function strlen;

/**
 * @implements BinarySerializable<Field>
 * @link https://discord.com/developers/docs/resources/channel#embed-object-embed-field-structure
 */
final class Field implements BinarySerializable{

    /** 256 characters */
    private string $name;

    /** 1024 characters */
    private string $value;

    private bool $inline;

    public function __construct(string $name, string $value, bool $inline = false){
        $this->setName($name);
        $this->setValue($value);
        $this->setInline($inline);
    }

    public function getName(): string{
        return $this->name;
    }

    public function setName(string $name): void{
        if(strlen($name) > 256){
            throw new \AssertionError("Embed field name can only have up to 256 characters.");
        }
        $this->name = $name;
    }

    public function getValue(): string{
        return $this->value;
    }

    public function setValue(string $value): void{
        if(strlen($value) > 1024){
            throw new \AssertionError("Embed field value can only have up to 1024 characters.");
        }
        $this->value = $value;
    }

    public function getInline(): bool{
        return $this->inline;
    }

    public function setInline(bool $inline): void{
        $this->inline = $inline;
    }

    public function binarySerialize(): BinaryStream{
        $stream = new BinaryStream();
        $stream->putString($this->name);
        $stream->putString($this->value);
        $stream->putBool($this->inline);
        return $stream;
    }

    public static function fromBinary(BinaryStream $stream): self{
        return new self(
            $stream->getString(), // name
            $stream->getString(), // value
            $stream->getBool()    // inline
        );
    }
}