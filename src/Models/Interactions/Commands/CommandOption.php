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
use JaxkDev\DiscordBot\Models\Channels\ChannelType;
use function array_map;
use function is_double;
use function is_int;
use function sizeof;
use function strlen;

/**
 * @implements BinarySerializable<CommandOption>
 * @link https://discord.com/developers/docs/interactions/application-commands#application-command-object-application-command-option-structure
 */
final class CommandOption implements BinarySerializable{

    /** Type of option */
    private CommandOptionType $type;

    /** 1-32 character name */
    private string $name;

    /**
     * Localization dictionary for the name field. Values follow the same restrictions as name
     * Key is the locale code, value is the localized name
     * @link https://discord.com/developers/docs/reference#locales
     * @var array<string, string>|null
     */
    private ?array $name_localizations;

    /** 1-100 character description */
    private string $description;

    /**
     * Localization dictionary for the description field. Values follow the same restrictions as description
     * Key is the locale code, value is the localized description
     * @link https://discord.com/developers/docs/reference#locales
     * @var array<string, string>|null
     */
    private ?array $description_localizations;

    /** If the parameter is required or optional, default false */
    private ?bool $required;

    /**
     * Choices for string, int and number types for the user to pick from (max 25)
     * @var CommandOptionChoice[]|null $choices
     */
    private ?array $choices;

    /**
     * If the option is a subcommand or subcommand group type, this nested options will be the parameters
     * @var CommandOption[]|null $options
     */
    private ?array $options;

    /**
     * If the option is a channel type, the channels shown will be restricted to these types
     * @var ChannelType[]|null $channel_types
     */
    private ?array $channel_types;

    /**
     * If the option is an INTEGER or NUMBER type, the minimum value permitted.
     * type matches the type of the option (integer for INTEGER options, double/float for NUMBER options)
     */
    private int|float|null $min_value;

    /**
     * If the option is an INTEGER or NUMBER type, the maximum value permitted.
     * type matches the type of the option (integer for INTEGER options, double/float for NUMBER options)
     */
    private int|float|null $max_value;

    /** If the option is a STRING type, the minimum length of the string (minimum of 0, maximum of 6000) */
    private ?int $min_length;

    /** If the option is a STRING type, the maximum length of the string (minimum of 1, maximum of 6000) */
    private ?int $max_length;

    /** If autocomplete interactions are enabled for this STRING, INTEGER, or NUMBER type option */
    private ?bool $autocomplete;

    /**
     * @param CommandOptionChoice[]|null $choices
     * @param CommandOption[]|null       $options
     * @param ChannelType[]|null         $channel_types
     */
    public function __construct(CommandOptionType $type, string $name, ?array $name_localizations,
                                string $description, ?array $description_localizations, ?bool $required, ?array $choices,
                                ?array $options, ?array $channel_types, int|float|null $min_value,
                                int|float|null $max_value, ?int $min_length, ?int $max_length, ?bool $autocomplete){
        $this->setType($type);
        $this->setName($name);
        $this->setNameLocalizations($name_localizations);
        $this->setDescription($description);
        $this->setDescriptionLocalizations($description_localizations);
        $this->setRequired($required);
        $this->setChoices($choices);
        $this->setOptions($options);
        $this->setChannelTypes($channel_types);
        $this->setMinValue($min_value);
        $this->setMaxValue($max_value);
        $this->setMinLength($min_length);
        $this->setMaxLength($max_length);
        $this->setAutocomplete($autocomplete);
    }

    public function getType(): CommandOptionType{
        return $this->type;
    }

    public function setType(CommandOptionType $type): void{
        $this->type = $type;
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

    public function getDescription(): string{
        return $this->description;
    }

    public function setDescription(string $description): void{
        if(strlen($description) < 1 || strlen($description) > 100){
            throw new \AssertionError("Description must be between 1 and 100 characters.");
        }
        $this->description = $description;
    }

    /** @return array<string, string>|null */
    public function getDescriptionLocalizations(): ?array{
        return $this->description_localizations;
    }

    /** @param array<string, string>|null $description_localizations */
    public function setDescriptionLocalizations(?array $description_localizations): void{
        $this->description_localizations = $description_localizations;
    }

    public function getRequired(): ?bool{
        return $this->required;
    }

    public function setRequired(?bool $required): void{
        $this->required = $required;
    }

    /** @return CommandOptionChoice[]|null */
    public function getChoices(): ?array{
        return $this->choices;
    }

    /** @param CommandOptionChoice[]|null $choices */
    public function setChoices(?array $choices): void{
        if($choices !== null){
            if(sizeof($choices) > 25){
                throw new \AssertionError("Choices array cannot exceed 25 elements.");
            }
            foreach($choices as $choice){
                if(!($choice instanceof CommandOptionChoice)){
                    throw new \AssertionError("Choices array must contain only CommandOptionChoice objects.");
                }
            }
        }
        $this->choices = $choices;
    }

    /** @return CommandOption[]|null */
    public function getOptions(): ?array{
        return $this->options;
    }

    /** @param CommandOption[]|null $options */
    public function setOptions(?array $options): void{
        if($options !== null){
            foreach($options as $option){
                if(!($option instanceof CommandOption)){
                    throw new \AssertionError("Options array must contain only CommandOption objects.");
                }
            }
        }
        $this->options = $options;
    }

    /** @return ChannelType[]|null */
    public function getChannelTypes(): ?array{
        return $this->channel_types;
    }

    /** @param ChannelType[]|null $channel_types */
    public function setChannelTypes(?array $channel_types): void{
        if($channel_types !== null){
            foreach($channel_types as $channel_type){
                if(!($channel_type instanceof ChannelType)){
                    throw new \AssertionError("Channel types array must contain only ChannelType objects.");
                }
            }
        }
        $this->channel_types = $channel_types;
    }

    public function getMinValue(): int|float|null{
        return $this->min_value;
    }

    public function setMinValue(int|float|null $min_value): void{
        $this->min_value = $min_value;
    }

    public function getMaxValue(): int|float|null{
        return $this->max_value;
    }

    public function setMaxValue(int|float|null $max_value): void{
        $this->max_value = $max_value;
    }

    public function getMinLength(): ?int{
        return $this->min_length;
    }

    public function setMinLength(?int $min_length): void{
        $this->min_length = $min_length;
    }

    public function getMaxLength(): ?int{
        return $this->max_length;
    }

    public function setMaxLength(?int $max_length): void{
        $this->max_length = $max_length;
    }

    public function getAutocomplete(): ?bool{
        return $this->autocomplete;
    }

    public function setAutocomplete(?bool $autocomplete): void{
        $this->autocomplete = $autocomplete;
    }

    public function binarySerialize(): BinaryStream{
        $stream = new BinaryStream();
        $stream->putByte($this->type->value);
        $stream->putString($this->name);
        $stream->putNullableLocalizationDictionary($this->name_localizations);
        $stream->putString($this->description);
        $stream->putNullableLocalizationDictionary($this->description_localizations);
        $stream->putNullableBool($this->required);
        $stream->putNullableSerializableArray($this->choices);
        $stream->putNullableSerializableArray($this->options);
        $stream->putNullableByteArray($this->channel_types === null ? null : array_map(fn($v) => $v->value, $this->channel_types));
        if(is_int($this->min_value) && is_int($this->max_value) && $this->type->value === CommandOptionType::INTEGER){
            $stream->putNullableLong($this->min_value);
            $stream->putNullableLong($this->max_value);
        }elseif(is_double($this->min_value) && is_double($this->max_value) && $this->type->value === CommandOptionType::NUMBER){
            $stream->putNullableDouble($this->min_value);
            $stream->putNullableDouble($this->max_value);
        }else{
            $stream->putBool(false); //null
            $stream->putBool(false); //null
        }
        $stream->putNullableInt($this->min_length);
        $stream->putNullableInt($this->max_length);
        $stream->putNullableBool($this->autocomplete);
        return $stream;
    }

    public static function fromBinary(BinaryStream $stream): self{
        return new self(
            ($t = CommandOptionType::from($stream->getByte())),
            $stream->getString(),
            $stream->getNullableLocalizationDictionary(),
            $stream->getString(),
            $stream->getNullableLocalizationDictionary(),
            $stream->getNullableBool(),
            $stream->getNullableSerializableArray(CommandOptionChoice::class),
            $stream->getNullableSerializableArray(self::class),
            ($ty = $stream->getNullableByteArray()) === null ? null : array_map(fn($v) => ChannelType::from($v), $ty),
            match($t) { //min_value
                CommandOptionType::INTEGER => $stream->getNullableLong(),
                CommandOptionType::NUMBER => $stream->getNullableDouble(),
                default => $stream->getNull() //null
            },
            match($t) { //max_value
                CommandOptionType::INTEGER => $stream->getNullableLong(),
                CommandOptionType::NUMBER => $stream->getNullableDouble(),
                default => $stream->getNull() //null
            },
            $stream->getNullableInt(),
            $stream->getNullableInt(),
            $stream->getNullableBool()
        );
    }
}