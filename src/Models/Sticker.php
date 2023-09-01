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

namespace JaxkDev\DiscordBot\Models;

use JaxkDev\DiscordBot\Communication\BinarySerializable;
use JaxkDev\DiscordBot\Communication\BinaryStream;
use JaxkDev\DiscordBot\Plugin\Utils;

/**
 * Partial sticker object.
 *
 * @implements BinarySerializable<Sticker>
 * @link https://discord.com/developers/docs/resources/sticker#sticker-object-sticker-structure
 */
final class Sticker implements BinarySerializable{

    /** id of the sticker */
    private string $id;

    /** for standard stickers, id of the pack the sticker is from */
    private ?string $pack_id;

    /** name of the sticker */
    private string $name;

    /** description of the sticker */
    private ?string $description;

    /**
     * autocomplete/suggestion tags for the sticker (max 200 characters)
     * Comma seperated list of keywords. (eg. "hello, hi, hey")
     */
    private ?string $tags;

    /** type of sticker */
    private StickerType $type;

    /** type of sticker format */
    private StickerFormatType $format_type;

    /** whether this guild sticker can be used, may be false due to loss of Server Boosts */
    private ?bool $available;

    /** id of the guild that owns this sticker */
    private ?string $guild_id;

    /** the user that uploaded the guild sticker */
    private ?string $user_id;

    /** a sticker's sort order within a pack */
    private ?int $sort_value;

    public function __construct(string $id, ?string $pack_id, string $name, ?string $description, ?string $tags,
                                StickerType $type, StickerFormatType $format_type, ?bool $available, ?string $guild_id,
                                ?string $user_id, ?int $sort_value){
        $this->setId($id);
        $this->setPackId($pack_id);
        $this->setName($name);
        $this->setDescription($description);
        $this->setTags($tags);
        $this->setType($type);
        $this->setFormatType($format_type);
        $this->setAvailable($available);
        $this->setGuildId($guild_id);
        $this->setUserId($user_id);
        $this->setSortValue($sort_value);
    }

    public function getId(): string{
        return $this->id;
    }

    public function setId(string $id): void{
        if(!Utils::validDiscordSnowflake($id)){
            throw new \AssertionError("Sticker ID '$id' is invalid.");
        }
        $this->id = $id;
    }

    public function getPackId(): ?string{
        return $this->pack_id;
    }

    public function setPackId(?string $pack_id): void{
        if($pack_id !== null && !Utils::validDiscordSnowflake($pack_id)){
            throw new \AssertionError("Sticker pack ID '$pack_id' is invalid.");
        }
        $this->pack_id = $pack_id;
    }

    public function getName(): string{
        return $this->name;
    }

    public function setName(string $name): void{
        $this->name = $name;
    }

    public function getDescription(): ?string{
        return $this->description;
    }

    public function setDescription(?string $description): void{
        $this->description = $description;
    }

    public function getTags(): ?string{
        return $this->tags;
    }

    public function setTags(?string $tags): void{
        $this->tags = $tags;
    }

    public function getType(): StickerType{
        return $this->type;
    }

    public function setType(StickerType $type): void{
        $this->type = $type;
    }

    public function getFormatType(): StickerFormatType{
        return $this->format_type;
    }

    public function setFormatType(StickerFormatType $format_type): void{
        $this->format_type = $format_type;
    }

    public function getAvailable(): ?bool{
        return $this->available;
    }

    public function setAvailable(?bool $available): void{
        $this->available = $available;
    }

    public function getGuildId(): ?string{
        return $this->guild_id;
    }

    public function setGuildId(?string $guild_id): void{
        if($guild_id !== null && !Utils::validDiscordSnowflake($guild_id)){
            throw new \AssertionError("Sticker guild ID '$guild_id' is invalid.");
        }
        $this->guild_id = $guild_id;
    }

    public function getUserId(): ?string{
        return $this->user_id;
    }

    public function setUserId(?string $user_id): void{
        if($user_id !== null && !Utils::validDiscordSnowflake($user_id)){
            throw new \AssertionError("Sticker user ID '$user_id' is invalid.");
        }
        $this->user_id = $user_id;
    }

    public function getSortValue(): ?int{
        return $this->sort_value;
    }

    public function setSortValue(?int $sort_value): void{
        $this->sort_value = $sort_value;
    }

    public function binarySerialize(): BinaryStream{
        $stream = new BinaryStream();
        $stream->putString($this->id);
        $stream->putNullableString($this->pack_id);
        $stream->putString($this->name);
        $stream->putNullableString($this->description);
        $stream->putNullableString($this->tags);
        $stream->putByte($this->type->value);
        $stream->putByte($this->format_type->value);
        $stream->putNullableBool($this->available);
        $stream->putNullableString($this->guild_id);
        $stream->putNullableString($this->user_id);
        $stream->putNullableInt($this->sort_value);
        return $stream;
    }

    public static function fromBinary(BinaryStream $stream): self{
        return new self(
            $stream->getString(),                       // id
            $stream->getNullableString(),               // pack_id
            $stream->getString(),                       // name
            $stream->getNullableString(),               // description
            $stream->getNullableString(),               // tags
            StickerType::from($stream->getByte()),      // type
            StickerFormatType::from($stream->getByte()),// format_type
            $stream->getNullableBool(),                 // available
            $stream->getNullableString(),               // guild_id
            $stream->getNullableString(),               // user_id
            $stream->getNullableInt()                   // sort_value
        );
    }
}