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

namespace JaxkDev\DiscordBot\Models\Channels;

use JaxkDev\DiscordBot\Communication\BinarySerializable;
use JaxkDev\DiscordBot\Communication\BinaryStream;
use JaxkDev\DiscordBot\Plugin\Utils;
use function strlen;

/**
 * @implements BinarySerializable<ForumTag>
 * @link https://discord.com/developers/docs/resources/channel#forum-tag-object-forum-tag-structure
 */
class ForumTag implements BinarySerializable{

    /** The ID of the tag. */
    private string $id;

    /** The name of the tag (0-20 characters). */
    private string $name;

    /** Whether this tag can only be added to or removed from threads by a member with the MANAGE_THREADS permission. */
    private ?bool $moderated;

    /** The id of a guild's custom emoji. (One of ID/Name MUST be set) */
    private ?string $emoji_id;

    /** The unicode character of the emoji. (One of ID/Name MUST be set) */
    private ?string $emoji_name;

    public function __construct(string $id, string $name, ?bool $moderated = null, ?string $emoji_id = null, ?string $emoji_name = null){
        $this->id = $id;
        $this->name = $name;
        $this->moderated = $moderated;
        $this->emoji_id = $emoji_id;
        $this->emoji_name = $emoji_name;
    }

    public function getId(): string{
        return $this->id;
    }

    public function setId(string $id): void{
        if(!Utils::validDiscordSnowflake($id)){
            throw new \InvalidArgumentException("Invalid ID given.");
        }
        $this->id = $id;
    }

    public function getName(): string{
        return $this->name;
    }

    public function setName(string $name): void{
        if(strlen($name) < 1 || strlen($name) > 20){
            throw new \InvalidArgumentException("Name must be between 1 and 20 characters.");
        }
        $this->name = $name;
    }

    public function getModerated(): ?bool{
        return $this->moderated;
    }

    public function setModerated(?bool $moderated): void{
        $this->moderated = $moderated;
    }

    public function getEmojiId(): ?string{
        return $this->emoji_id;
    }

    public function setEmojiId(?string $emoji_id): void{
        if($emoji_id !== null && !Utils::validDiscordSnowflake($emoji_id)){
            throw new \InvalidArgumentException("Invalid emoji ID given.");
        }
        $this->emoji_id = $emoji_id;
    }

    public function getEmojiName(): ?string{
        return $this->emoji_name;
    }

    public function setEmojiName(?string $emoji_name): void{
        $this->emoji_name = $emoji_name;
    }

    public function binarySerialize(): BinaryStream{
        $stream = new BinaryStream();
        $stream->putString($this->id);
        $stream->putString($this->name);
        $stream->putNullableBool($this->moderated);
        $stream->putNullableString($this->emoji_id);
        $stream->putNullableString($this->emoji_name);
        return $stream;
    }

    public static function fromBinary(BinaryStream $stream): self{
        return new self(
            $stream->getString(),         // id
            $stream->getString(),         // name
            $stream->getNullableBool(),   // moderated
            $stream->getNullableString(), // emoji_id
            $stream->getNullableString()  // emoji_name
        );
    }
}