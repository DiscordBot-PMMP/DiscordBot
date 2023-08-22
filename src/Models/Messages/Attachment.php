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

use JaxkDev\DiscordBot\Communication\BinarySerializable;
use JaxkDev\DiscordBot\Communication\BinaryStream;

use JaxkDev\DiscordBot\Plugin\Utils;
use function str_starts_with;
use function strlen;
use function substr;

/**
 * @implements BinarySerializable<Attachment>
 * @link https://discord.com/developers/docs/resources/channel#attachment-object-attachment-structure
 */
final class Attachment implements BinarySerializable{

    private string $id;

    private string $file_name;

    /** Max 1024 chars */
    private ?string $description;

    /** https://en.wikipedia.org/wiki/Media_type */
    private ?string $content_type;

    /** Size of file in bytes */
    private int $size;

    private string $url;

    private string $proxy_url;

    /** Image height, null if not an image. */
    private ?int $height;

    /** Image width, null if not an image. */
    private ?int $width;

    /** Whether this attachment is ephemeral. */
    private ?bool $ephemeral;

    //No support for voice messages, still in development (Aug 23).

    public function __construct(string $id, string $file_name, ?string $description, ?string $content_type, int $size,
                                string $url, string $proxy_url, ?int $height = null, ?int $width = null,
                                ?bool $ephemeral = null){
        $this->setId($id);
        $this->setFileName($file_name);
        $this->setDescription($description);
        $this->setContentType($content_type);
        $this->setSize($size);
        $this->setUrl($url);
        $this->setProxyUrl($proxy_url);
        $this->setHeight($height);
        $this->setWidth($width);
        $this->setEphemeral($ephemeral);
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

    public function getDescription(): ?string{
        return $this->description;
    }

    public function setDescription(?string $description): void{
        if($description !== null && strlen($description) > 1024){
            throw new \AssertionError("Description '$description' is invalid.");
        }
        $this->description = $description;
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
        $this->url = $url;
    }

    public function getProxyUrl(): string{
        return $this->proxy_url;
    }

    public function setProxyUrl(string $proxy_url): void{
        $this->proxy_url = $proxy_url;
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

    public function getWidth(): ?int{
        return $this->width;
    }

    public function setWidth(?int $width): void{
        if($width !== null && $width < 0){
            throw new \AssertionError("Width '$width' is invalid.");
        }
        $this->width = $width;
    }

    public function getEphemeral(): ?bool{
        return $this->ephemeral;
    }

    public function setEphemeral(?bool $ephemeral): void{
        $this->ephemeral = $ephemeral;
    }

    public function getSpoiler(): bool{
        return str_starts_with($this->file_name, "SPOILER_");
    }

    public function setSpoiler(bool $spoiler): void{
        if($this->getSpoiler()){
            if(!$spoiler){
                $this->file_name = substr($this->file_name, strlen("SPOILER_"));
            }
        }elseif($spoiler){
            $this->file_name = "SPOILER_{$this->file_name}";
        }
    }

    public function binarySerialize(): BinaryStream{
        $stream = new BinaryStream();
        $stream->putString($this->getId());
        $stream->putString($this->getFileName());
        $stream->putNullableString($this->getDescription());
        $stream->putNullableString($this->getContentType());
        $stream->putInt($this->getSize());
        $stream->putString($this->getUrl());
        $stream->putString($this->getProxyUrl());
        $stream->putNullableInt($this->getHeight());
        $stream->putNullableInt($this->getWidth());
        $stream->putNullableBool($this->getEphemeral());
        return $stream;
    }

    public static function fromBinary(BinaryStream $stream): self{
        return new self(
            $stream->getString(),         // id
            $stream->getString(),         // file_name
            $stream->getNullableString(), // description
            $stream->getNullableString(), // content_type
            $stream->getInt(),            // size
            $stream->getString(),         // url
            $stream->getString(),         // proxy_url
            $stream->getNullableInt(),    // height
            $stream->getNullableInt(),    // width
            $stream->getNullableBool()    // ephemeral
        );
    }
}