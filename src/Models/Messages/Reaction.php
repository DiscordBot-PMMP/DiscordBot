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
use JaxkDev\DiscordBot\Models\Emoji;

/**
 * @implements BinarySerializable<Reaction>
 * @link https://discord.com/developers/docs/resources/channel#reaction-object-reaction-structure
 */
final class Reaction implements BinarySerializable{

    private int $count;

    private bool $me;

    private Emoji $emoji;

    public function __construct(int $count, bool $me, Emoji $emoji){
        $this->setCount($count);
        $this->setMe($me);
        $this->setEmoji($emoji);
    }

    public function getCount(): int{
        return $this->count;
    }

    public function setCount(int $count): void{
        $this->count = $count;
    }

    public function getMe(): bool{
        return $this->me;
    }

    public function setMe(bool $me): void{
        $this->me = $me;
    }

    public function getEmoji(): Emoji{
        return $this->emoji;
    }

    public function setEmoji(Emoji $emoji): void{
        $this->emoji = $emoji;
    }

    public function binarySerialize(): BinaryStream{
        $stream = new BinaryStream();
        $stream->putInt($this->count);
        $stream->putBool($this->me);
        $stream->putSerializable($this->emoji);
        return $stream;
    }

    public static function fromBinary(BinaryStream $stream): self{
        return new self(
            $stream->getInt(),                     // count
            $stream->getBool(),                    // me
            $stream->getSerializable(Emoji::class) // emoji
        );
    }
}