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

namespace JaxkDev\DiscordBot\Models\Interactions;

use JaxkDev\DiscordBot\Communication\BinarySerializable;
use JaxkDev\DiscordBot\Communication\BinaryStream;
use JaxkDev\DiscordBot\Models\Interactions\Commands\CommandOptionType;
use function is_bool;
use function is_float;
use function is_int;
use function is_string;

/**
 * 200th File in this plugin.
 * @implements BinarySerializable<ApplicationCommandDataOption>
 * @link https://discord.com/developers/docs/interactions/receiving-and-responding#interaction-object-application-command-interaction-data-option-structure
 */
final class ApplicationCommandDataOption implements BinarySerializable{

    //value and options are mutually exclusive.

    /** Name of the parameter */
    private string $name;

    /** Value type */
    private CommandOptionType $type;

    /** Value of the option resulting from user input */
    private string|int|float|bool|null $value;

    /**
     * Present if this option is a group or subcommand
     *
     * @var ApplicationCommandDataOption[]|null $options
     */
    private array|null $options;

    /** true if this option is the currently focused option for autocomplete */
    private ?bool $focused;

    /**
     * @param ApplicationCommandDataOption[]|null $options
     */
    public function __construct(string $name, CommandOptionType $type, string|int|float|bool|null $value,
                                array|null $options, ?bool $focused){
        $this->setName($name);
        $this->setType($type);
        $this->setValue($value);
        $this->setOptions($options);
        $this->setFocused($focused);
    }

    public function getName(): string{
        return $this->name;
    }

    public function setName(string $name): void{
        $this->name = $name;
    }

    public function getType(): CommandOptionType{
        return $this->type;
    }

    public function setType(CommandOptionType $type): void{
        $this->type = $type;
    }

    public function getValue(): string|int|float|bool|null{
        return $this->value;
    }

    public function setValue(string|int|float|bool|null $value): void{
        $this->value = $value;
    }

    /** @return ApplicationCommandDataOption[]|null */
    public function getOptions(): ?array{
        return $this->options;
    }

    /** @param ApplicationCommandDataOption[]|null $options */
    public function setOptions(?array $options): void{
        $this->options = $options;
    }

    public function getFocused(): ?bool{
        return $this->focused;
    }

    public function setFocused(?bool $focused): void{
        $this->focused = $focused;
    }

    public function binarySerialize(): BinaryStream{
        $stream = new BinaryStream();
        $stream->putString($this->name);
        $stream->putByte($this->type->value);
        $stream->putBool($this->value !== null);
        if(is_string($this->value) && $this->type->value === CommandOptionType::STRING){
            $stream->putString($this->value);
        }elseif(is_int($this->value) && $this->type->value === CommandOptionType::INTEGER){
            $stream->putLong($this->value);
        }elseif(is_float($this->value) && $this->type->value === CommandOptionType::NUMBER){
            $stream->putDouble($this->value);
        }elseif(is_bool($this->value) && $this->type->value === CommandOptionType::BOOLEAN){
            $stream->putBool($this->value);
        }elseif($this->value !== null){
            throw new \AssertionError("Invalid value type for option type {$this->type->value}");
        }
        $stream->putNullableSerializableArray($this->options);
        $stream->putNullableBool($this->focused);
        return $stream;
    }

    public static function fromBinary(BinaryStream $stream): self{
        return new self(
            $stream->getString(),                                                       // name
            ($t = CommandOptionType::from($stream->getByte())),                         // type
            match($t) {                                                                 // value
                CommandOptionType::STRING => $stream->getNullableString(),
                CommandOptionType::INTEGER => $stream->getNullableLong(),
                CommandOptionType::NUMBER => $stream->getNullableDouble(),
                CommandOptionType::BOOLEAN => $stream->getNullableBool(),
                default => $stream->getNull()
            },
            $stream->getNullableSerializableArray(ApplicationCommandDataOption::class), // options
            $stream->getNullableBool()                                                  // focused
        );
    }
}