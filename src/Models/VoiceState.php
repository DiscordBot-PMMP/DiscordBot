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

use JaxkDev\DiscordBot\Communication\BinarySerializable;
use JaxkDev\DiscordBot\Communication\BinaryStream;
use JaxkDev\DiscordBot\Plugin\Utils;

/** @link https://discord.com/developers/docs/resources/voice#voice-state-object */
class VoiceState implements \JsonSerializable, BinarySerializable{

    /** The guild id this voice state is for, null for DMs. */
    private ?string $guild_id;

    /** The channel id this user is connected to, null when leaving etc. */
    private ?string $channel_id;

    /** The user id this voice state is for */
    private string $user_id;

    /** The session id for this voice state (Not a snowflake) */
    private ?string $session_id;

    /** Whether this user is deafened by the server */
    private bool $deaf;

    /** Whether this user is muted by the server */
    private bool $mute;

    /** Whether this user is locally deafened */
    private bool $self_deaf;

    /** Whether this user is locally muted */
    private bool $self_mute;

    /** Whether this user is streaming using "Go Live" */
    private ?bool $self_stream;

    /** Whether this user's camera is enabled */
    private bool $self_video;

    /** Whether this user's permission to speak is denied */
    private bool $suppress;

    /** The time at which the user requested to speak (unix timestamp, ms)*/
    private ?int $request_to_speak_timestamp;

    //No create method as this is not sent to the API, only received.

    public function __construct(?string $guild_id, ?string $channel_id, string $user_id, ?string $session_id, bool $deaf,
                                bool $mute, bool $self_deaf, bool $self_mute, ?bool $self_stream, bool $self_video,
                                bool $suppress, ?int $request_to_speak_timestamp){
        $this->setGuildId($guild_id);
        $this->setChannelId($channel_id);
        $this->setUserId($user_id);
        $this->setSessionId($session_id);
        $this->setDeaf($deaf);
        $this->setMute($mute);
        $this->setSelfDeaf($self_deaf);
        $this->setSelfMute($self_mute);
        $this->setSelfStream($self_stream);
        $this->setSelfVideo($self_video);
        $this->setSuppress($suppress);
        $this->setRequestToSpeakTimestamp($request_to_speak_timestamp);
    }

    public function getGuildId(): ?string{
        return $this->guild_id;
    }

    public function setGuildId(?string $guild_id): void{
        if($guild_id !== null and !Utils::validDiscordSnowflake($guild_id)){
            throw new \AssertionError("Guild ID '$guild_id' is invalid.");
        }
        $this->guild_id = $guild_id;
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

    public function getUserId(): string{
        return $this->user_id;
    }

    public function setUserId(string $user_id): void{
        if(!Utils::validDiscordSnowflake($user_id)){
            throw new \AssertionError("User ID '$user_id' is invalid.");
        }
        $this->user_id = $user_id;
    }

    public function getSessionId(): ?string{
        return $this->session_id;
    }

    public function setSessionId(?string $session_id): void{
        $this->session_id = $session_id;
    }

    public function getDeaf(): bool{
        return $this->deaf;
    }

    public function setDeaf(bool $deaf): void{
        $this->deaf = $deaf;
    }

    public function getMute(): bool{
        return $this->mute;
    }

    public function setMute(bool $mute): void{
        $this->mute = $mute;
    }

    public function getSelfDeaf(): bool{
        return $this->self_deaf;
    }

    public function setSelfDeaf(bool $self_deaf): void{
        $this->self_deaf = $self_deaf;
    }

    public function getSelfMute(): bool{
        return $this->self_mute;
    }

    public function setSelfMute(bool $self_mute): void{
        $this->self_mute = $self_mute;
    }

    public function getSelfStream(): ?bool{
        return $this->self_stream;
    }

    public function setSelfStream(?bool $self_stream): void{
        $this->self_stream = $self_stream;
    }

    public function getSelfVideo(): bool{
        return $this->self_video;
    }

    public function setSelfVideo(bool $self_video): void{
        $this->self_video = $self_video;
    }

    public function getSuppress(): bool{
        return $this->suppress;
    }

    public function setSuppress(bool $suppress): void{
        $this->suppress = $suppress;
    }

    /** @return ?int UNIX Timestamp */
    public function getRequestToSpeakTimestamp(): ?int{
        return $this->request_to_speak_timestamp;
    }

    /** @param ?int $request_to_speak_timestamp UNIX Timestamp */
    public function setRequestToSpeakTimestamp(?int $request_to_speak_timestamp): void{
        $this->request_to_speak_timestamp = $request_to_speak_timestamp;
    }

    //----- Serialization -----//

    public function binarySerialize(): BinaryStream{
        $stream = new BinaryStream();
        $stream->putNullableString($this->guild_id);
        $stream->putNullableString($this->channel_id);
        $stream->putString($this->user_id);
        $stream->putNullableString($this->session_id);
        $stream->putBool($this->deaf);
        $stream->putBool($this->mute);
        $stream->putBool($this->self_deaf);
        $stream->putBool($this->self_mute);
        $stream->putNullableBool($this->self_stream);
        $stream->putBool($this->self_video);
        $stream->putBool($this->suppress);
        $stream->putNullableInt($this->request_to_speak_timestamp);
        return $stream;
    }

    public static function fromBinary(BinaryStream $stream): self{
        return new self(
            $stream->getNullableString(),   // guild_id
            $stream->getNullableString(),   // channel_id
            $stream->getString(),           // user_id
            $stream->getNullableString(),   // session_id
            $stream->getBool(),             // deaf
            $stream->getBool(),             // mute
            $stream->getBool(),             // self_deaf
            $stream->getBool(),             // self_mute
            $stream->getNullableBool(),     // self_stream
            $stream->getBool(),             // self_video
            $stream->getBool(),             // suppress
            $stream->getNullableInt()       // request_to_speak_timestamp
        );
    }

    public function jsonSerialize(): array{
        return [
            "guild_id" => $this->guild_id,
            "channel_id" => $this->channel_id,
            "user_id" => $this->user_id,
            "session_id" => $this->session_id,
            "deaf" => $this->deaf,
            "mute" => $this->mute,
            "self_deaf" => $this->self_deaf,
            "self_mute" => $this->self_mute,
            "self_stream" => $this->self_stream,
            "self_video" => $this->self_video,
            "suppress" => $this->suppress,
            "request_to_speak_timestamp" => $this->request_to_speak_timestamp
        ];
    }

    public static function fromJson(array $data): self{
        return new self(
            $data["guild_id"] ?? null,
            $data["channel_id"] ?? null,
            $data["user_id"],
            $data["session_id"] ?? null,
            $data["deaf"],
            $data["mute"],
            $data["self_deaf"],
            $data["self_mute"],
            $data["self_stream"] ?? null,
            $data["self_video"],
            $data["suppress"],
            $data["request_to_speak_timestamp"] ?? null
        );
    }
}