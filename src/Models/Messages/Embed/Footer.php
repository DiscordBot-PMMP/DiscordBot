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
 * @implements BinarySerializable<Footer>
 * @link https://discord.com/developers/docs/resources/channel#embed-object-embed-footer-structure
 */
class Footer implements BinarySerializable{

    /** 2048 characters */
    private string $text;

    private ?string $icon_url;

    private ?string $proxy_icon_url;

    public function __construct(string $text, ?string $icon_url = null, ?string $proxy_icon_url = null){
        $this->setText($text);
        $this->setIconUrl($icon_url);
        $this->setProxyIconUrl($proxy_icon_url);
    }

    public function getText(): string{
        return $this->text;
    }

    public function setText(string $text): void{
        if(strlen($text) > 2048){
            throw new \AssertionError("Embed footer text can only have up to 2048 characters.");
        }
        $this->text = $text;
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

    public function binarySerialize(): BinaryStream{
        $stream = new BinaryStream();
        $stream->putString($this->getText());
        $stream->putNullableString($this->getIconUrl());
        $stream->putNullableString($this->getProxyIconUrl());
        return $stream;
    }

    public static function fromBinary(BinaryStream $stream): self{
        return new self(
            $stream->getString(),         // text
            $stream->getNullableString(), // icon_url
            $stream->getNullableString()  // proxy_icon_url
        );
    }
}