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

namespace JaxkDev\DiscordBot\Models;

use JaxkDev\DiscordBot\Plugin\Utils;

class VoiceState{

    private string $session_id;

    private ?string $channel_id;

    private bool $deaf;
    private bool $mute;

    private bool $self_deaf;
    private bool $self_mute;
    private bool $self_stream;
    private bool $self_video;

    private bool $suppress;

    public function __construct(string $session_id, ?string $channel_id, bool $deaf, bool $mute, bool $self_deaf,
                                bool $self_mute, bool $self_stream, bool $self_video, bool $suppress){
        $this->setSessionId($session_id);
        $this->setChannelId($channel_id);
        $this->setDeaf($deaf);
        $this->setMute($mute);
        $this->setSelfDeaf($self_deaf);
        $this->setSelfMute($self_mute);
        $this->setSelfStream($self_stream);
        $this->setSelfVideo($self_video);
        $this->setSuppress($suppress);
    }

    public function getSessionId(): string{
        return $this->session_id;
    }

    public function setSessionId(string $session_id): void{
        $this->session_id = $session_id;
    }

    public function getChannelId(): ?string{
        return $this->channel_id;
    }

    public function setChannelId(?string $channel_id): void{
        if($channel_id !== null and !Utils::validDiscordSnowflake($channel_id)){
            throw new \AssertionError("Channel ID '$channel_id' is invalid.");
        }
        $this->channel_id = $channel_id;
    }

    public function isDeaf(): bool{
        return $this->deaf;
    }

    public function setDeaf(bool $deaf): void{
        $this->deaf = $deaf;
    }

    public function isMute(): bool{
        return $this->mute;
    }

    public function setMute(bool $mute): void{
        $this->mute = $mute;
    }

    public function isSelfDeaf(): bool{
        return $this->self_deaf;
    }

    public function setSelfDeaf(bool $self_deaf): void{
        $this->self_deaf = $self_deaf;
    }

    public function isSelfMute(): bool{
        return $this->self_mute;
    }

    public function setSelfMute(bool $self_mute): void{
        $this->self_mute = $self_mute;
    }

    public function isSelfStream(): bool{
        return $this->self_stream;
    }

    public function setSelfStream(bool $self_stream): void{
        $this->self_stream = $self_stream;
    }

    public function isSelfVideo(): bool{
        return $this->self_video;
    }

    public function setSelfVideo(bool $self_video): void{
        $this->self_video = $self_video;
    }

    public function isSuppress(): bool{
        return $this->suppress;
    }

    public function setSuppress(bool $suppress): void{
        $this->suppress = $suppress;
    }

    //----- Serialization -----//

    public function __serialize(): array{
        return [
            $this->session_id,
            $this->channel_id,
            $this->deaf,
            $this->mute,
            $this->self_deaf,
            $this->self_mute,
            $this->self_stream,
            $this->self_video,
            $this->suppress
        ];
    }

    public function __unserialize(array $data): void{
        [
            $this->session_id,
            $this->channel_id,
            $this->deaf,
            $this->mute,
            $this->self_deaf,
            $this->self_mute,
            $this->self_stream,
            $this->self_video,
            $this->suppress
        ] = $data;
    }
}