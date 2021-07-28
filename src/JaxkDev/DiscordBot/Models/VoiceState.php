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

namespace JaxkDev\DiscordBot\Models;

use JaxkDev\DiscordBot\Plugin\Utils;

class VoiceState implements \Serializable{

    /** @var string */
    private $session_id;

    /** @var string|null */
    private $channel_id;

    /** @var bool */
    private $deaf;
    /** @var bool */
    private $mute;

    /** @var bool */
    private $self_deaf;
    /** @var bool */
    private $self_mute;
    /** @var bool */
    private $self_stream;
    /** @var bool */
    private $self_video;

    /** @var bool */
    private $suppress;

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

    public function serialize(): ?string{
        return serialize([
            $this->session_id,
            $this->channel_id,
            $this->deaf,
            $this->mute,
            $this->self_deaf,
            $this->self_mute,
            $this->self_stream,
            $this->self_video,
            $this->suppress
        ]);
    }

    public function unserialize($data): void{
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
        ] = unserialize($data);
    }
}