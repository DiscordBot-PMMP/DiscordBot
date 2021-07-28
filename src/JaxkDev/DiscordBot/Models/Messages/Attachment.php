<?php
/*
 * DiscordBot, PocketMine-MP Plugin.
 *
 * Licensed under the Open Software License version 3.0 (OSL-3.0)
 * Copyright (C) 2020-2021 JaxkDev
 *
 * Twitter :: @JaxkDev
 * Discord :: JaxkDev#2698
 * Email   :: JaxkDev@gmail.com
 */

namespace JaxkDev\DiscordBot\Models\Messages;

use JaxkDev\DiscordBot\Plugin\Utils;

class Attachment implements \Serializable{

    /** @var string */
    private $id;

    /** @var string */
    private $file_name;

    /** @var string https://en.wikipedia.org/wiki/Media_type */
    private $content_type;

    /** @var int Size of the resource in bytes */
    private $size;

    /** @var string Is this always a discord cdn url? */
    private $url;

    /** @var int|null Image width, null if not an image. */
    private $width;

    /** @var int|null Image height, null if not an image. */
    private $height;

    public function __construct(string $id, string $file_name, string $content_type, int $size, string $url,
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

    public function getContentType(): string{
        return $this->content_type;
    }

    public function setContentType(string $content_type): void{
        $this->content_type = $content_type;
    }

    public function getSize(): int{
        return $this->size;
    }

    public function setSize(int $size): void{
        if($size <= 0){
            throw new \AssertionError("Size '$size bytes' is invalid.");
        }
        $this->size = $size;
    }

    public function getUrl(): string{
        return $this->url;
    }

    public function setUrl(string $url): void{
        if(strpos($url, "https://") !== 0){
            throw new \AssertionError("URL '$url' is invalid, must be prefixed 'https://'.");
        }
        $this->url = $url;
    }

    public function getWidth(): ?int{
        return $this->width;
    }

    public function setWidth(?int $width): void{
        if($width !== null and $width <= 0){
            throw new \AssertionError("Width '$width' is invalid.");
        }
        $this->width = $width;
    }

    public function getHeight(): ?int{
        return $this->height;
    }

    public function setHeight(?int $height): void{
        if($height !== null and $height <= 0){
            throw new \AssertionError("Height '$height' is invalid.");
        }
        $this->height = $height;
    }

    public function isSpoiler(): bool{
        return (strpos($this->file_name, "SPOILER_") === 0);
    }

    //----- Serialization -----//

    public function serialize(): ?string{
        return serialize([
            $this->id,
            $this->file_name,
            $this->content_type,
            $this->size,
            $this->url,
            $this->width,
            $this->height
        ]);
    }

    public function unserialize($data): void{
        [
            $this->id,
            $this->file_name,
            $this->content_type,
            $this->size,
            $this->url,
            $this->width,
            $this->height
        ] = unserialize($data);
    }
}