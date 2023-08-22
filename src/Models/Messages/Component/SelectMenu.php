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

namespace JaxkDev\DiscordBot\Models\Messages\Component;

use JaxkDev\DiscordBot\Communication\BinaryStream;
use JaxkDev\DiscordBot\Models\Channels\ChannelType;
use function array_map;
use function count;
use function strlen;

/**
 * @link https://discord.com/developers/docs/interactions/message-components#select-menu-object-select-menu-structure
 */
final class SelectMenu extends Component{

    public const SERIALIZE_ID = 16;

    /** Max 100 characters. */
    private string $custom_id;

    /** @var SelectOption[] max 25, Required and only available for STRING_SELECT. */
    private array $options;

    /** @var ChannelType[] List of channel types to list, only available for CHANNEL_SELECT. */
    private array $channel_types;

    /** Max 150 characters. */
    private ?string $placeholder;

    /** Minimum number of items that must be chosen (defaults to 1); min 0, max 25 */
    private int $min_values;

    /** Maximum number of items that can be chosen (defaults to 1); max 25 */
    private int $max_values;

    /** Whether select menu is disabled (defaults to false) */
    private bool $disabled;

    /**
     * @param SelectOption[] $options
     * @param ChannelType[]  $channel_types
     */
    public function __construct(ComponentType $type, string $custom_id, array $options, array $channel_types = [],
                                ?string $placeholder = null, int $min_values = 1, int $max_values = 1,
                                bool $disabled = false){
        parent::__construct($type);
        $this->setCustomId($custom_id);
        $this->setOptions($options);
        $this->setChannelTypes($channel_types);
        $this->setPlaceholder($placeholder);
        $this->setMinValues($min_values);
        $this->setMaxValues($max_values);
        $this->setDisabled($disabled);
    }

    public function getCustomId(): string{
        return $this->custom_id;
    }

    public function setCustomId(string $custom_id): void{
        $this->custom_id = $custom_id;
    }

    /** @return SelectOption[] */
    public function getOptions(): array{
        return $this->options;
    }

    /** @param SelectOption[] $options */
    public function setOptions(array $options): void{
        if(count($options) !== 0){
            if($this->getType() !== ComponentType::STRING_SELECT){
                throw new \AssertionError("Options are only available for STRING_SELECT.");
            }
            if(count($options) > 25){
                throw new \AssertionError("Max 25 options per select menu.");
            }
            foreach($options as $option){
                if(!$option instanceof SelectOption){
                    throw new \AssertionError("Options must be an array of SelectOption.");
                }
            }
        }
        $this->options = $options;
    }

    /** @return ChannelType[] */
    public function getChannelTypes(): array{
        return $this->channel_types;
    }

    /** @param ChannelType[] $channel_types */
    public function setChannelTypes(array $channel_types): void{
        if(count($channel_types) !== 0){
            if($this->getType() !== ComponentType::CHANNEL_SELECT){
                throw new \AssertionError("Channel types are only available for CHANNEL_SELECT.");
            }
            foreach($channel_types as $channel_type){
                if(!$channel_type instanceof ChannelType){
                    throw new \AssertionError("Channel types must be an array of ChannelType.");
                }
            }
        }
        $this->channel_types = $channel_types;
    }

    public function getPlaceholder(): ?string{
        return $this->placeholder;
    }

    public function setPlaceholder(?string $placeholder): void{
        if($placeholder !== null && strlen($placeholder) > 150){
            throw new \AssertionError("Max 150 characters for placeholder.");
        }
        $this->placeholder = $placeholder;
    }

    public function getMinValues(): int{
        return $this->min_values;
    }

    public function setMinValues(int $min_values): void{
        if($min_values < 0 || $min_values > 25){
            throw new \AssertionError("Min values must be between 0 and 25.");
        }
        $this->min_values = $min_values;
    }

    public function getMaxValues(): int{
        return $this->max_values;
    }

    public function setMaxValues(int $max_values): void{
        if($max_values < 1 || $max_values > 25){
            throw new \AssertionError("Max values must be between 1 and 25.");
        }
        $this->max_values = $max_values;
    }

    public function getDisabled(): bool{
        return $this->disabled;
    }

    public function setDisabled(bool $disabled): void{
        $this->disabled = $disabled;
    }

    public function binarySerialize(): BinaryStream{
        $stream = new BinaryStream();
        $stream->putByte($this->type->value);
        $stream->putString($this->custom_id);
        $stream->putSerializableArray($this->options);
        $stream->putByteArray(array_map(fn(ChannelType $type) => $type->value, $this->channel_types));
        $stream->putNullableString($this->placeholder);
        $stream->putInt($this->min_values);
        $stream->putInt($this->max_values);
        $stream->putBool($this->disabled);
        return $stream;
    }

    public static function fromBinary(BinaryStream $stream): self{
        $type = ComponentType::from($stream->getByte());
        $custom_id = $stream->getString();
        $options = $stream->getSerializableArray(SelectOption::class);
        $channel_types = array_map(fn(int $type) => ChannelType::from($type), $stream->getByteArray());
        $placeholder = $stream->getNullableString();
        $min_values = $stream->getInt();
        $max_values = $stream->getInt();
        $disabled = $stream->getBool();
        return new self($type, $custom_id, $options, $channel_types, $placeholder, $min_values, $max_values, $disabled);
    }
}