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

use JaxkDev\DiscordBot\Communication\BinarySerializable;
use JaxkDev\DiscordBot\Communication\BinaryStream;

/**
 * @implements BinarySerializable<Provider>
 * @link https://discord.com/developers/docs/resources/channel#embed-object-embed-provider-structure
 */
final class Provider implements BinarySerializable{

    private ?string $name;

    private ?string $url;

    public function __construct(?string $name, ?string $url){
        $this->setName($name);
        $this->setUrl($url);
    }

    public function getName(): ?string{
        return $this->name;
    }

    public function setName(?string $name): void{
        $this->name = $name;
    }

    public function getUrl(): ?string{
        return $this->url;
    }

    public function setUrl(?string $url): void{
        $this->url = $url;
    }

    public function binarySerialize(): BinaryStream{
        $stream = new BinaryStream();
        $stream->putNullableString($this->getName());
        $stream->putNullableString($this->getUrl());
        return $stream;
    }

    public static function fromBinary(BinaryStream $stream): self{
        return new self(
            $stream->getNullableString(), // name
            $stream->getNullableString()  // url
        );
    }
}