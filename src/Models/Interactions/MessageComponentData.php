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
use JaxkDev\DiscordBot\Models\Messages\Component\ComponentType;

/**
 * @implements BinarySerializable<MessageComponentData>
 * @link https://discord.com/developers/docs/interactions/receiving-and-responding#interaction-object-message-component-data-structure
 */
final class MessageComponentData implements BinarySerializable{

    /** The custom_id of the component */
    private string $custom_id;

    /** The type of the component */
    private ComponentType $component_type;

    /**
     * The values the user selected in a select menu component
     * @var string[]|null $values
     */
    private ?array $values;

    /** @param string[]|null $values */
    public function __construct(string $custom_id, ComponentType $component_type, ?array $values){
        $this->setCustomId($custom_id);
        $this->setComponentType($component_type);
        $this->setValues($values);
    }

    public function getCustomId(): string{
        return $this->custom_id;
    }

    public function setCustomId(string $custom_id): void{
        $this->custom_id = $custom_id;
    }

    public function getComponentType(): ComponentType{
        return $this->component_type;
    }

    public function setComponentType(ComponentType $component_type): void{
        $this->component_type = $component_type;
    }

    /** @return string[]|null */
    public function getValues(): ?array{
        return $this->values;
    }

    /** @param string[]|null $values */
    public function setValues(?array $values): void{
        $this->values = $values;
    }

    public function binarySerialize(): BinaryStream{
        $stream = new BinaryStream();
        $stream->putString($this->custom_id);
        $stream->putByte($this->component_type->value);
        $stream->putNullableStringArray($this->values);
        return $stream;
    }

    public static function fromBinary(BinaryStream $stream): self{
        return new self(
            $stream->getString(),                    // custom_id
            ComponentType::from($stream->getByte()), // component_type
            $stream->getNullableStringArray()        // values
        );
    }
}