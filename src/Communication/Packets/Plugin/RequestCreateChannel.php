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

namespace JaxkDev\DiscordBot\Communication\Packets\Plugin;

use JaxkDev\DiscordBot\Communication\BinaryStream;
use JaxkDev\DiscordBot\Communication\Packets\Packet;
use JaxkDev\DiscordBot\Models\Channels\ChannelType;
use JaxkDev\DiscordBot\Models\Channels\ForumTag;
use JaxkDev\DiscordBot\Models\Channels\Overwrite;
use JaxkDev\DiscordBot\Models\Channels\VideoQualityMode;

/**
 * @link https://discord.com/developers/docs/resources/guild#create-guild-channel-json-params
 */
final class RequestCreateChannel extends Packet{

    public const SERIALIZE_ID = 405;

    private string $guild_id;

    private string $name;

    private ChannelType $type;

    private ?string $topic;

    private ?int $bitrate;

    private ?int $user_limit;

    private ?int $rate_limit_per_user;

    private ?int $position;

    /** @var Overwrite[] */
    private array $permission_overwrites;

    private ?string $parent_id;

    private ?bool $nsfw;

    private ?string $rtc_region;

    private ?VideoQualityMode $video_quality_mode;

    /** @var ForumTag[]|null */
    private ?array $available_tags;

    private ?string $reason;

    public function __construct(string $guild_id, string $name, ChannelType $type, ?string $topic, ?int $bitrate,
                                ?int $user_limit, ?int $rate_limit_per_user, ?int $position, array $permission_overwrites,
                                ?string $parent_id, ?bool $nsfw, ?string $rtc_region,
                                ?VideoQualityMode $video_quality_mode, ?array $available_tags, ?string $reason,
                                ?int $uid = null){
        parent::__construct($uid);
        $this->guild_id = $guild_id;
        $this->name = $name;
        $this->type = $type;
        $this->topic = $topic;
        $this->bitrate = $bitrate;
        $this->user_limit = $user_limit;
        $this->rate_limit_per_user = $rate_limit_per_user;
        $this->position = $position;
        $this->permission_overwrites = $permission_overwrites;
        $this->parent_id = $parent_id;
        $this->nsfw = $nsfw;
        $this->rtc_region = $rtc_region;
        $this->video_quality_mode = $video_quality_mode;
        $this->available_tags = $available_tags;
        $this->reason = $reason;
    }

    public function getGuildId(): string{
        return $this->guild_id;
    }

    public function getName(): string{
        return $this->name;
    }

    public function getType(): ChannelType{
        return $this->type;
    }

    public function getTopic(): ?string{
        return $this->topic;
    }

    public function getBitrate(): ?int{
        return $this->bitrate;
    }

    public function getUserLimit(): ?int{
        return $this->user_limit;
    }

    public function getRateLimitPerUser(): ?int{
        return $this->rate_limit_per_user;
    }

    public function getPosition(): ?int{
        return $this->position;
    }

    /** @return Overwrite[] */
    public function getPermissionOverwrites(): array{
        return $this->permission_overwrites;
    }

    public function getParentId(): ?string{
        return $this->parent_id;
    }

    public function getNsfw(): ?bool{
        return $this->nsfw;
    }

    public function getRtcRegion(): ?string{
        return $this->rtc_region;
    }

    public function getVideoQualityMode(): ?VideoQualityMode{
        return $this->video_quality_mode;
    }

    /** @return ForumTag[]|null */
    public function getAvailableTags(): ?array{
        return $this->available_tags;
    }

    public function getReason(): ?string{
        return $this->reason;
    }

    public function binarySerialize(): BinaryStream{
        $stream = new BinaryStream();
        $stream->putInt($this->getUID());
        $stream->putString($this->guild_id);
        $stream->putString($this->name);
        $stream->putByte($this->type->value);
        $stream->putNullableString($this->topic);
        $stream->putNullableInt($this->bitrate);
        $stream->putNullableInt($this->user_limit);
        $stream->putNullableInt($this->rate_limit_per_user);
        $stream->putNullableInt($this->position);
        $stream->putSerializableArray($this->permission_overwrites);
        $stream->putNullableString($this->parent_id);
        $stream->putNullableBool($this->nsfw);
        $stream->putNullableString($this->rtc_region);
        $stream->putNullableByte($this->video_quality_mode?->value);
        $stream->putNullableSerializableArray($this->available_tags);
        $stream->putNullableString($this->reason);
        return $stream;
    }

    public static function fromBinary(BinaryStream $stream): self{
        $uid = $stream->getInt();
        return new self(
            $stream->getString(),                                        // guild_id
            $stream->getString(),                                        // name
            ChannelType::from($stream->getByte()),                       // type
            $stream->getNullableString(),                                // topic
            $stream->getNullableInt(),                                   // bitrate
            $stream->getNullableInt(),                                   // user_limit
            $stream->getNullableInt(),                                   // rate_limit_per_user
            $stream->getNullableInt(),                                   // position
            $stream->getSerializableArray(Overwrite::class),             // permission_overwrites
            $stream->getNullableString(),                                // parent_id
            $stream->getNullableBool(),                                  // nsfw
            $stream->getNullableString(),                                // rtc_region
            VideoQualityMode::tryFrom($stream->getNullableByte() ?? -1), // video_quality_mode
            $stream->getNullableSerializableArray(ForumTag::class),      // available_tags
            $stream->getNullableString(),                                // reason
            $uid
        );
    }
}