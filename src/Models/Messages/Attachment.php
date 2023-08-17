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

namespace JaxkDev\DiscordBot\Models\Messages;

use JaxkDev\DiscordBot\Plugin\Utils;
use function str_starts_with;

class Attachment{

    private string $id;

    private string $file_name;

    /** https://en.wikipedia.org/wiki/Media_type */
    private ?string $content_type;

    /** Size of the resource in bytes */
    private int $size;

    /** TODO Is this always a discord cdn url? */
    private string $url;

    /** Image width, null if not an image. */
    private ?int $width;

    /** Image height, null if not an image. */
    private ?int $height;

    public function __construct(string $id, string $file_name, ?string $content_type, int $size, string $url,
                                ?int $width = null, ?int $height = null){
        $this->setId($id);
        $this->setFileName($file_name);
        $this->setContentType($content_type);
        $this->setSize($size);
        $this->setUrl($url);
        $this->setWidth($width);
        $this->setHeight($height);
    }

    public function getId(): string{
        return $this->id;
    }

    public function setId(string $id): void{
        if(!Utils::validDiscordSnowflake($id)){
            throw new \AssertionError("ID '$id' is invalid.");
        }
        $this->id = $id;
    }

    public function getFileName(): string{
        return $this->file_name;
    }

    public function setFileName(string $file_name): void{
        $this->file_name = $file_name;
    }

    public function getContentType(): ?string{
        return $this->content_type;
    }

    public function setContentType(?string $content_type): void{
        $this->content_type = $content_type;
    }

    public function getSize(): int{
        return $this->size;
    }

    public function setSize(int $size): void{
        if($size < 0){
            throw new \AssertionError("Size '$size bytes' is invalid.");
        }
        $this->size = $size;
    }

    public function getUrl(): string{
        return $this->url;
    }

    public function setUrl(string $url): void{
        if(!str_starts_with($url, "https://")){
            //TODO Check again, can't see in docs now.
            throw new \AssertionError("URL '$url' is invalid, must be prefixed 'https://'.");
        }
        $this->url = $url;
    }

    public function getWidth(): ?int{
        return $this->width;
    }

    public function setWidth(?int $width): void{
        if($width !== null && $width < 0){
            throw new \AssertionError("Width '$width' is invalid.");
        }
        $this->width = $width;
    }

    public function getHeight(): ?int{
        return $this->height;
    }

    public function setHeight(?int $height): void{
        if($height !== null && $height < 0){
            throw new \AssertionError("Height '$height' is invalid.");
        }
        $this->height = $height;
    }

    public function isSpoiler(): bool{
        return str_starts_with($this->file_name, "SPOILER_");
    }

    //----- Serialization -----//

    public function __serialize(): array{
        return [
            $this->id,
            $this->file_name,
            $this->content_type,
            $this->size,
            $this->url,
            $this->width,
            $this->height
        ];
    }

    public function __unserialize(array $data): void{
        [
            $this->id,
            $this->file_name,
            $this->content_type,
            $this->size,
            $this->url,
            $this->width,
            $this->height
        ] = $data;
    }
}
