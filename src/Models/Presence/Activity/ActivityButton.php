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

namespace JaxkDev\DiscordBot\Models\Presence\Activity;

use JaxkDev\DiscordBot\Communication\BinarySerializable;
use JaxkDev\DiscordBot\Communication\BinaryStream;

/** @link https://discord.com/developers/docs/topics/gateway-events#activity-object-activity-buttons */
class ActivityButton implements \JsonSerializable, BinarySerializable{

    /** Text shown on the button (1-32 characters) */
    private string $label;

    /**
     * URL opened when clicking the button (1-512 characters)
     * Note, Bot will not be sent users URLs. this is here only for bot sending its own activity buttons.
     */
    private ?string $url;

    /** The only parameters required (and allowed) to be set on creation for bot activity buttons. */
    public static function create(string $label, ?string $url = null): self{
        return new self($label, $url);
    }

    public function __construct(string $label, ?string $url = null){
        $this->setLabel($label);
        $this->setUrl($url);
    }

    public function getLabel(): string{
        return $this->label;
    }

    public function setLabel(string $label): void{
        if(strlen($label) < 1 || strlen($label) > 32){
            throw new \AssertionError("Label must be between 1 and 32 characters.");
        }
        $this->label = $label;
    }

    public function getUrl(): ?string{
        return $this->url;
    }

    public function setUrl(?string $url): void{
        if($url !== null && (strlen($url) < 1 || strlen($url) > 512)){
            throw new \AssertionError("URL must be between 1 and 512 characters.");
        }
        $this->url = $url;
    }

    //----- Serialization -----//

    public function binarySerialize(): BinaryStream{
        $stream = new BinaryStream();
        $stream->putString($this->label);
        $stream->putNullableString($this->url);
        return $stream;
    }

    public static function fromBinary(BinaryStream $stream): self{
        return new self(
            $stream->getString(),           // label
            $stream->getNullableString()    // url
        );
    }

    public function jsonSerialize(): array{
        return [
            "label" => $this->label,
            "url" => $this->url,
        ];
    }

    public static function fromJson(array $json): self{
        return new self(
            $json["label"],
            $json["url"] ?? null
        );
    }
}