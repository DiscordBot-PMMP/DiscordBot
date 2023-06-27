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

// https://discord.com/developers/docs/resources/channel#embed-object-embed-image-structure
class Image{

    /** Must be prefixed with `https` */
    private ?string $url;

    private ?int $width;

    private ?int $height;

    public function __construct(?string $url = null, ?int $width = null, ?int $height = null){
        $this->setUrl($url);
        $this->setWidth($width);
        $this->setHeight($height);
    }

    public function getUrl(): ?string{
        return $this->url;
    }

    public function setUrl(?string $url): void{
        if($url !== null and strpos($url , "https" ) !== 0){
            throw new \AssertionError("URL '$url' must start with https.");
        }
        $this->url = $url;
    }

    public function getWidth(): ?int{
        return $this->width;
    }

    public function setWidth(?int $width): void{
        $this->width = $width;
    }

    public function getHeight(): ?int{
        return $this->height;
    }

    public function setHeight(?int $height): void{
        $this->height = $height;
    }

    //----- Serialization -----//

    public function __serialize(): array{
        return [
            $this->url,
            $this->width,
            $this->height
        ];
    }

    public function __unserialize(array $data): void{
        [
            $this->url,
            $this->width,
            $this->height
        ] = $data;
    }
}