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

namespace JaxkDev\DiscordBot\Models\Interactions\Commands;

use JaxkDev\DiscordBot\Communication\BinarySerializable;
use JaxkDev\DiscordBot\Communication\BinaryStream;
use function is_double;
use function is_int;
use function is_string;
use function strlen;

/**
 * @implements BinarySerializable<CommandOptionChoice>
 * @link https://discord.com/developers/docs/interactions/application-commands#application-command-object-application-command-option-choice-structure
 */
final class CommandOptionChoice implements BinarySerializable{

    /** 1-100 character name */
    private string $name;

    /**
     * Localization dictionary for the name field. Values follow the same restrictions as name
     * Key is the locale code, value is the localized name
     * @link https://discord.com/developers/docs/reference#locales
     * @var array<string, string>|null
     */
    private ?array $name_localizations;

    /**
     * Value of the choice, up to 100 characters if string, -2^53 to 2^53 if int, -2^53 to 2^53 if double
     * type corresponds to the type of the Option the choice is for
     */
    private string|int|float $value;

    public function __construct(string $name, ?array $name_localizations, string|int|float $value){
        $this->setName($name);
        $this->setNameLocalizations($name_localizations);
        $this->setValue($value);
    }

    public function getName(): string{
        return $this->name;
    }

    public function setName(string $name): void{
        if(strlen($name) < 1 || strlen($name) > 100){
            throw new \AssertionError("Name must be between 1 and 100 characters.");
        }
        $this->name = $name;
    }

    /** @return array<string, string>|null */
    public function getNameLocalizations(): ?array{
        return $this->name_localizations;
    }

    /** @param array<string, string>|null $name_localizations */
    public function setNameLocalizations(?array $name_localizations): void{
        $this->name_localizations = $name_localizations;
    }

    public function getValue(): string|int|float{
        return $this->value;
    }

    public function setValue(string|int|float $value): void{
        $this->value = $value;
    }

    public function binarySerialize(): BinaryStream{
        $stream = new BinaryStream();
        $stream->putString($this->name);
        $stream->putNullableLocalizationDictionary($this->name_localizations);
        if(is_string($this->value)){
            $stream->putByte(1); //1 = string
            $stream->putString($this->value);
        }elseif(is_int($this->value)){
            $stream->putByte(2); //2 = int
            $stream->putLong($this->value);
        }elseif(is_double($this->value)){
            $stream->putByte(3); //3 = double
            $stream->putDouble($this->value);
        }else{
            throw new \AssertionError("Invalid value type.");
        }
        return $stream;
    }

    public static function fromBinary(BinaryStream $stream): self{
        return new self(
            $stream->getString(),
            $stream->getNullableLocalizationDictionary(),
            match($stream->getByte()){
                1 => $stream->getString(),
                2 => $stream->getLong(),
                3 => $stream->getDouble(),
                default => throw new \AssertionError("Invalid value type.")
            }
        );
    }
}