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
use function strlen;

/**
 * @implements BinarySerializable<Author>
 * @link https://discord.com/developers/docs/resources/channel#embed-object-embed-author-structure
 */
final class Author implements BinarySerializable{

    /** 256 characters */
    private string $name;

    private ?string $url;

    private ?string $icon_url;

    private ?string $proxy_icon_url;

    public function __construct(string $name, ?string $url = null, ?string $icon_url = null, ?string $proxy_icon_url = null){
        $this->setName($name);
        $this->setUrl($url);
        $this->setIconUrl($icon_url);
        $this->setProxyIconUrl($proxy_icon_url);
    }

    public function getName(): string{
        return $this->name;
    }

    public function setName(string $name): void{
        if(strlen($name) > 256){
            throw new \AssertionError("Embed author name can only have up to 256 characters.");
        }
        $this->name = $name;
    }

    public function getUrl(): ?string{
        return $this->url;
    }

    public function setUrl(?string $url): void{
        $this->url = $url;
    }

    public function getIconUrl(): ?string{
        return $this->icon_url;
    }

    public function setIconUrl(?string $icon_url): void{
        $this->icon_url = $icon_url;
    }

    public function getProxyIconUrl(): ?string{
        return $this->proxy_icon_url;
    }

    public function setProxyIconUrl(?string $proxy_icon_url): void{
        $this->proxy_icon_url = $proxy_icon_url;
    }

    //----- Serialization -----//

    public function binarySerialize(): BinaryStream{
        $stream = new BinaryStream();
        $stream->putString($this->name);
        $stream->putNullableString($this->url);
        $stream->putNullableString($this->icon_url);
        $stream->putNullableString($this->proxy_icon_url);
        return $stream;
    }

    public static function fromBinary(BinaryStream $stream): self{
        return new self(
            $stream->getString(),         // name
            $stream->getNullableString(), // url
            $stream->getNullableString(), // icon_url
            $stream->getNullableString()  // proxy_icon_url
        );
    }
}