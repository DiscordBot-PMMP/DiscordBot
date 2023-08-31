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

namespace JaxkDev\DiscordBot\Models\Messages\Component;

use JaxkDev\DiscordBot\Communication\BinaryStream;
use function strlen;

/**
 * @link https://discord.com/developers/docs/interactions/message-components#text-inputs-text-input-structure
 */
final class TextInput extends Component{

    public const SERIALIZE_ID = 17;

    /** Max 100 characters. */
    private string $custom_id;

    private TextInputStyle $style;

    /** Max 45 characters. */
    private string $label;

    /** Minimum input length for a text input; min 0, max 4000 */
    private int $min_length;

    /** Maximum input length for a text input; min 1, max 4000 */
    private int $max_length;

    /** Whether this component is required to be filled (defaults to true) */
    private bool $required;

    /** Pre-filled value for this component; max 4000 characters */
    private ?string $value;

    /** Custom placeholder text if the input is empty; max 100 characters */
    private ?string $placeholder;

    public function __construct(string $custom_id, TextInputStyle $style, string $label, int $min_length,
                                int $max_length, bool $required = false, ?string $value = null,
                                ?string $placeholder = null){
        parent::__construct(ComponentType::TEXT_INPUT);
        $this->setCustomId($custom_id);
        $this->setStyle($style);
        $this->setLabel($label);
        $this->setMinLength($min_length);
        $this->setMaxLength($max_length);
        $this->setRequired($required);
        $this->setValue($value);
        $this->setPlaceholder($placeholder);
    }

    public function getCustomId(): string{
        return $this->custom_id;
    }

    public function setCustomId(string $custom_id): void{
        if(strlen($custom_id) > 100){
            throw new \AssertionError("Custom ID cannot be longer than 100 characters.");
        }
        $this->custom_id = $custom_id;
    }

    public function getStyle(): TextInputStyle{
        return $this->style;
    }

    public function setStyle(TextInputStyle $style): void{
        $this->style = $style;
    }

    public function getLabel(): string{
        return $this->label;
    }

    public function setLabel(string $label): void{
        if(strlen($label) > 45){
            throw new \AssertionError("Label cannot be longer than 45 characters.");
        }
        $this->label = $label;
    }

    public function getMinLength(): int{
        return $this->min_length;
    }

    public function setMinLength(int $min_length): void{
        if($min_length < 0 || $min_length > 4000){
            throw new \AssertionError("Min length must be between 0 and 4000.");
        }
        $this->min_length = $min_length;
    }

    public function getMaxLength(): int{
        return $this->max_length;
    }

    public function setMaxLength(int $max_length): void{
        if($max_length < 1 || $max_length > 4000){
            throw new \AssertionError("Max length must be between 1 and 4000.");
        }
        $this->max_length = $max_length;
    }

    public function getRequired(): bool{
        return $this->required;
    }

    public function setRequired(bool $required): void{
        $this->required = $required;
    }

    public function getValue(): ?string{
        return $this->value;
    }

    public function setValue(?string $value): void{
        if($value !== null && strlen($value) > 4000){
            throw new \AssertionError("Value cannot be longer than 4000 characters.");
        }
        $this->value = $value;
    }

    public function getPlaceholder(): ?string{
        return $this->placeholder;
    }

    public function setPlaceholder(?string $placeholder): void{
        if($placeholder !== null && strlen($placeholder) > 100){
            throw new \AssertionError("Placeholder cannot be longer than 100 characters.");
        }
        $this->placeholder = $placeholder;
    }

    public function binarySerialize(): BinaryStream{
        $stream = new BinaryStream();
        $stream->putString($this->custom_id);
        $stream->putByte($this->style->value);
        $stream->putString($this->label);
        $stream->putInt($this->min_length);
        $stream->putInt($this->max_length);
        $stream->putBool($this->required);
        $stream->putNullableString($this->value);
        $stream->putNullableString($this->placeholder);
        return $stream;
    }

    public static function fromBinary(BinaryStream $stream): self{
        return new self(
            $stream->getString(),                     // custom_id
            TextInputStyle::from($stream->getByte()), // style
            $stream->getString(),                     // label
            $stream->getInt(),                        // min_length
            $stream->getInt(),                        // max_length
            $stream->getBool(),                       // required
            $stream->getNullableString(),             // value
            $stream->getNullableString()              // placeholder
        );
    }
}