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

namespace JaxkDev\DiscordBot\Models\Messages\Embed;

// https://discord.com/developers/docs/resources/channel#embed-object-embed-field-structure
class Field{

    /** 256 characters */
    private string $name;

    /** 2048 characters */
    private string $value;

    private bool $inline = false;

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
        if(strlen($value) > 2048){
            throw new \AssertionError("Embed field value can only have up to 2048 characters.");
        }
        $this->value = $value;
    }

    public function isInline(): bool{
        return $this->inline;
    }

    public function setInline(bool $inline): void{
        $this->inline = $inline;
    }

    //----- Serialization -----//

    public function __serialize(): array{
        return [
            $this->name,
            $this->value,
            $this->inline
        ];
    }

    public function __unserialize(array $data): void{
        [
            $this->name,
            $this->value,
            $this->inline
        ] = $data;
    }
}