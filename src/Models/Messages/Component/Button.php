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
use JaxkDev\DiscordBot\Models\Emoji;
use function strlen;

/**
 * @link https://discord.com/developers/docs/interactions/message-components#button-object-button-structure
 */
class Button extends Component{

    private ButtonStyle $style;

    /** Max 80 characters. */
    private ?string $label;

    private ?Emoji $emoji;

    /** Max 100 characters, Required for non-link style, cannot be used in conjunction with url. */
    private ?string $custom_id;

    /** Required for link style, cannot be used in conjunction with custom_id. */
    private ?string $url;

    private bool $disabled;

    public function __construct(ButtonStyle $style, ?string $label = null, ?Emoji $emoji = null,
                                ?string $custom_id = null, ?string $url = null, bool $disabled = false){
        parent::__construct(ComponentType::BUTTON);
        $this->setStyle($style);
        $this->setLabel($label);
        $this->setEmoji($emoji);
        $this->setCustomId($custom_id);
        $this->setUrl($url);
        $this->setDisabled($disabled);
    }

    public function getStyle(): ButtonStyle{
        return $this->style;
    }

    public function setStyle(ButtonStyle $style): void{
        $this->style = $style;
    }

    public function getLabel(): ?string{
        return $this->label;
    }

    public function setLabel(?string $label): void{
        if($label !== null && strlen($label) > 80){
            throw new \AssertionError("Max 80 characters for button label.");
        }
        $this->label = $label;
    }

    public function getEmoji(): ?Emoji{
        return $this->emoji;
    }

    public function setEmoji(?Emoji $emoji): void{
        $this->emoji = $emoji;
    }

    public function getCustomId(): ?string{
        return $this->custom_id;
    }

    public function setCustomId(?string $custom_id): void{
        if($this->style === ButtonStyle::LINK){
            throw new \AssertionError("Custom ID only allowed on non-link style.");
        }
        if($custom_id !== null && strlen($custom_id) > 100){
            throw new \AssertionError("Max 100 characters for button custom_id.");
        }
        $this->custom_id = $custom_id;
    }

    public function getUrl(): ?string{
        return $this->url;
    }

    public function setUrl(?string $url): void{
        if($this->style !== ButtonStyle::LINK){
            throw new \AssertionError("URL only allowed on link style.");
        }
        $this->url = $url;
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
        $stream->putByte($this->style->value);
        $stream->putNullableString($this->label);
        $stream->putNullableSerializable($this->emoji);
        $stream->putNullableString($this->custom_id);
        $stream->putNullableString($this->url);
        $stream->putBool($this->disabled);
        return $stream;
    }

    public static function fromBinary(BinaryStream $stream): Button{
        return new self(
            ButtonStyle::from($stream->getByte()),          // style
            $stream->getNullableString(),                   // label
            $stream->getNullableSerializable(Emoji::class), // emoji
            $stream->getNullableString(),                   // custom_id
            $stream->getNullableString(),                   // url
            $stream->getBool()                              // disabled
        );
    }
}