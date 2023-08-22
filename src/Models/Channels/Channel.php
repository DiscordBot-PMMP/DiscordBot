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
 * @implements BinarySerializable<Channel>
 * @link https://discord.com/developers/docs/resources/channel#channel-object-channel-structure
 */
final class Channel implements BinarySerializable{

    public const SERIALIZE_ID = 13;

    /** @link https://discord.com/developers/docs/resources/channel#channel-object-channel-flags */
    public const FLAGS = [
        "PINNED" => (1 << 1), // This thread is pinned to the top of its parent GUILD_FORUM or GUILD_MEDIA channel
        "REQUIRE_TAG" => (1 << 4), // Whether a tag is required to be specified when creating a thread in a GUILD_FORUM or a GUILD_MEDIA channel. Tags are specified in the applied_tags field.
        "HIDE_MEDIA_DOWNLOAD_OPTIONS" => (1 << 15) // When set hides the embedded media download options. Available only for media channels
    ];

    private string $id;

    private ChannelType $type;

    /** ID of the guild channel resides in. */
    private ?string $guild_id;

    /** Sorting position of the channel. */
    private ?int $position;

    /**
     * Explicit permission overwrites for members and roles.
     * @var Overwrite[]|null
     */
    private ?array $permission_overwrites;

    /** 1-100 Characters. */
    private ?string $name;

    /** 0-4096 characters for GUILD_FORUM and GUILD_MEDIA channels, 0-1024 characters for all others. */
    private ?string $topic;

    /** Whether the channel is nsfw. */
    private ?bool $nsfw;

    /**
     * ID of the last message sent in this channel (or thread for GUILD_FORUM or GUILD_MEDIA channels)
     * (may not point to an existing or valid message or thread)
     */
    private ?string $last_message_id;

    /** The bitrate (in bits) of the voice channel. */
    private ?int $bitrate;

    /** The user limit of the voice channel. */
    private ?int $user_limit;

    /**
     * Amount of seconds a user has to wait before sending another message (0-21600);
     * bots, as well as users with the permission manage_messages or manage_channel, are unaffected
     */
    private ?int $rate_limit_per_user;

    /**
     * The recipients of the DM.
     * @var string[] User IDs
     */
    private ?array $recipients;

    /** Icon hash for group DM Channels. */
    private ?string $icon;

    /** ID of the creator of the group DM or thread. */
    private ?string $owner_id;

    /** Application ID of the group DM creator if it is bot-created. */
    private ?string $application_id;

    /** For group DM channels: whether the channel is managed by an application via the gdm.join OAuth2 scope */
    private ?bool $managed;

    /**
     * ID of the parent category for a channel (each parent category can contain up to 50 channels).
     * ID of the parent channel for a thread.
     */
    private ?string $parent_id;

    /** When the last pinned message was pinned. */
    private ?int $last_pin_timestamp;

    /** Voice region ID for the voice channel, automatic when set to null. */
    private ?string $rtc_region;

    /** The camera video quality mode of the voice channel, 1 when not present. */
    private ?VideoQualityMode $video_quality_mode;

    /** Thread-specific fields not needed by other channels. */
    private ?ThreadMetadata $thread_metadata;

    /** @see Channel::FLAGS */
    private ?int $flags;

    /**
     * The set of tags that can be used in a GUILD_FORUM or a GUILD_MEDIA channel
     * @var ForumTag[]
     */
    private ?array $available_tags;

    /**
     * The tags that have been applied to a GUILD_FORUM or a GUILD_MEDIA channel
     * @var string[] Tag IDs
     */
    private ?array $applied_tags;

    /**
     * @param Overwrite[]|null $permission_overwrites
     * @param string[]|null    $recipients            User IDs
     * @param ForumTag[]|null  $available_tags
     * @param string[]|null    $applied_tags          Tag IDs
     */
    public function __construct(string $id, ChannelType $type, ?string $guild_id = null, ?int $position = null,
                                ?array $permission_overwrites = null, ?string $name = null, ?string $topic = null,
                                ?bool $nsfw = null, ?string $last_message_id = null, ?int $bitrate = null,
                                ?int $user_limit = null, ?int $rate_limit_per_user = null, ?array $recipients = null,
                                ?string $icon = null, ?string $owner_id = null, ?string $application_id = null,
                                ?bool $managed = null, ?string $parent_id = null, ?int $last_pin_timestamp = null,
                                ?string $rtc_region = null, ?VideoQualityMode $video_quality_mode = null,
                                ?ThreadMetadata $thread_metadata = null, ?int $flags = null,
                                ?array $available_tags = null, ?array $applied_tags = null){
        $this->setId($id);
        $this->setType($type);
        $this->setGuildId($guild_id);
        $this->setPosition($position);
        $this->setPermissionOverwrites($permission_overwrites);
        $this->setName($name);
        $this->setTopic($topic);
        $this->setNsfw($nsfw);
        $this->setLastMessageId($last_message_id);
        $this->setBitrate($bitrate);
        $this->setUserLimit($user_limit);
        $this->setRateLimitPerUser($rate_limit_per_user);
        $this->setRecipients($recipients);
        $this->setIcon($icon);
        $this->setOwnerId($owner_id);
        $this->setApplicationId($application_id);
        $this->setManaged($managed);
        $this->setParentId($parent_id);
        $this->setLastPinTimestamp($last_pin_timestamp);
        $this->setRtcRegion($rtc_region);
        $this->setVideoQualityMode($video_quality_mode);
        $this->setThreadMetadata($thread_metadata);
        $this->setFlags($flags);
        $this->setAvailableTags($available_tags);
        $this->setAppliedTags($applied_tags);
    }

    public function getId(): string{
        return $this->id;
    }

    public function setId(string $id): void{
        if(!Utils::validDiscordSnowflake($id)){
            throw new \AssertionError("Channel ID '$id' is invalid.");
        }
        $this->id = $id;
    }

    public function getType(): ChannelType{
        return $this->type;
    }

    public function setType(ChannelType $type): void{
        $this->type = $type;
    }

    public function getGuildId(): ?string{
        return $this->guild_id;
    }

    public function setGuildId(?string $guild_id): void{
        if($guild_id !== null && !Utils::validDiscordSnowflake($guild_id)){
            throw new \AssertionError("Guild ID '$guild_id' is invalid.");
        }
        $this->guild_id = $guild_id;
    }

    public function getPosition(): ?int{
        return $this->position;
    }

    public function setPosition(?int $position): void{
        $this->position = $position;
    }

    /** @return Overwrite[]|null */
    public function getPermissionOverwrites(): ?array{
        return $this->permission_overwrites;
    }

    /** @param Overwrite[]|null $permission_overwrites */
    public function setPermissionOverwrites(?array $permission_overwrites): void{
        $this->permission_overwrites = $permission_overwrites;
    }

    public function getName(): ?string{
        return $this->name;
    }

    public function setName(?string $name): void{
        if($name !== null && (strlen($name) < 1 || strlen($name) > 100)){
            throw new \AssertionError("Channel name must be between 1 and 100 characters.");
        }
        $this->name = $name;
    }

    public function getTopic(): ?string{
        return $this->topic;
    }

    public function setTopic(?string $topic): void{
        if($topic !== null && strlen($topic) > 4096){
            throw new \AssertionError("Channel topic must be under 4096 characters.");
        }
        $this->topic = $topic;
    }

    public function getNsfw(): ?bool{
        return $this->nsfw;
    }

    public function setNsfw(?bool $nsfw): void{
        $this->nsfw = $nsfw;
    }

    public function getLastMessageId(): ?string{
        return $this->last_message_id;
    }

    public function setLastMessageId(?string $last_message_id): void{
        if($last_message_id !== null && !Utils::validDiscordSnowflake($last_message_id)){
            throw new \AssertionError("Last message ID '$last_message_id' is invalid.");
        }
        $this->last_message_id = $last_message_id;
    }

    public function getBitrate(): ?int{
        return $this->bitrate;
    }

    public function setBitrate(?int $bitrate): void{
        $this->bitrate = $bitrate;
    }

    public function getUserLimit(): ?int{
        return $this->user_limit;
    }

    public function setUserLimit(?int $user_limit): void{
        $this->user_limit = $user_limit;
    }

    public function getRateLimitPerUser(): ?int{
        return $this->rate_limit_per_user;
    }

    public function setRateLimitPerUser(?int $rate_limit_per_user): void{
        $this->rate_limit_per_user = $rate_limit_per_user;
    }

    /** @return string[]|null */
    public function getRecipients(): ?array{
        return $this->recipients;
    }

    /** @param string[]|null $recipients User IDs */
    public function setRecipients(?array $recipients): void{
        foreach(($recipients ?? []) as $recipient){
            if(!Utils::validDiscordSnowflake($recipient)){
                throw new \AssertionError("Recipient ID '$recipient' is invalid.");
            }
        }
        $this->recipients = $recipients;
    }

    public function getIcon(): ?string{
        return $this->icon;
    }

    public function setIcon(?string $icon): void{
        $this->icon = $icon;
    }

    public function getOwnerId(): ?string{
        return $this->owner_id;
    }

    public function setOwnerId(?string $owner_id): void{
        if($owner_id !== null && !Utils::validDiscordSnowflake($owner_id)){
            throw new \AssertionError("Owner ID '$owner_id' is invalid.");
        }
        $this->owner_id = $owner_id;
    }

    public function getApplicationId(): ?string{
        return $this->application_id;
    }

    public function setApplicationId(?string $application_id): void{
        if($application_id !== null && !Utils::validDiscordSnowflake($application_id)){
            throw new \AssertionError("Application ID '$application_id' is invalid.");
        }
        $this->application_id = $application_id;
    }

    public function getManaged(): ?bool{
        return $this->managed;
    }

    public function setManaged(?bool $managed): void{
        $this->managed = $managed;
    }

    public function getParentId(): ?string{
        return $this->parent_id;
    }

    public function setParentId(?string $parent_id): void{
        if($parent_id !== null && !Utils::validDiscordSnowflake($parent_id)){
            throw new \AssertionError("Parent ID '$parent_id' is invalid.");
        }
        $this->parent_id = $parent_id;
    }

    public function getLastPinTimestamp(): ?int{
        return $this->last_pin_timestamp;
    }

    public function setLastPinTimestamp(?int $last_pin_timestamp): void{
        $this->last_pin_timestamp = $last_pin_timestamp;
    }

    public function getRtcRegion(): ?string{
        return $this->rtc_region;
    }

    public function setRtcRegion(?string $rtc_region): void{
        $this->rtc_region = $rtc_region;
    }

    public function getVideoQualityMode(): ?VideoQualityMode{
        return $this->video_quality_mode;
    }

    public function setVideoQualityMode(?VideoQualityMode $video_quality_mode): void{
        $this->video_quality_mode = $video_quality_mode;
    }

    public function getThreadMetadata(): ?ThreadMetadata{
        return $this->thread_metadata;
    }

    public function setThreadMetadata(?ThreadMetadata $thread_metadata): void{
        $this->thread_metadata = $thread_metadata;
    }

    public function getFlags(): ?int{
        return $this->flags;
    }

    public function setFlags(?int $flags): void{
        $this->flags = $flags;
    }

    /** @return ForumTag[]|null */
    public function getAvailableTags(): ?array{
        return $this->available_tags;
    }

    /** @param ForumTag[]|null $available_tags */
    public function setAvailableTags(?array $available_tags): void{
        foreach(($available_tags ?? []) as $tag){
            if(!($tag instanceof ForumTag)){
                throw new \AssertionError("Available tag is not an instance of ForumTag.");
            }
        }
        $this->available_tags = $available_tags;
    }

    /** @return string[]|null */
    public function getAppliedTags(): ?array{
        return $this->applied_tags;
    }

    /** @param string[]|null $applied_tags Tag IDs */
    public function setAppliedTags(?array $applied_tags): void{
        foreach(($applied_tags ?? []) as $tag){
            if(!Utils::validDiscordSnowflake($tag)){
                throw new \AssertionError("Applied tag ID '$tag' is invalid.");
            }
        }
        $this->applied_tags = $applied_tags;
    }

    public function binarySerialize(): BinaryStream{
        $stream = new BinaryStream();
        $stream->putString($this->id);
        $stream->putByte($this->type->value);
        $stream->putNullableString($this->guild_id);
        $stream->putNullableInt($this->position);
        $stream->putNullableSerializableArray($this->permission_overwrites);
        $stream->putNullableString($this->name);
        $stream->putNullableString($this->topic);
        $stream->putNullableBool($this->nsfw);
        $stream->putNullableString($this->last_message_id);
        $stream->putNullableInt($this->bitrate);
        $stream->putNullableInt($this->user_limit);
        $stream->putNullableInt($this->rate_limit_per_user);
        $stream->putNullableStringArray($this->recipients);
        $stream->putNullableString($this->icon);
        $stream->putNullableString($this->owner_id);
        $stream->putNullableString($this->application_id);
        $stream->putNullableBool($this->managed);
        $stream->putNullableString($this->parent_id);
        $stream->putNullableLong($this->last_pin_timestamp);
        $stream->putNullableString($this->rtc_region);
        $stream->putNullableByte($this->video_quality_mode?->value);
        $stream->putNullableSerializable($this->thread_metadata);
        $stream->putNullableInt($this->flags);
        $stream->putNullableSerializableArray($this->available_tags);
        $stream->putNullableStringArray($this->applied_tags);
        return $stream;
    }

    public static function fromBinary(BinaryStream $stream): self{
        return new self(
            $stream->getString(),                                        // id
            ChannelType::from($stream->getByte()),                       // type
            $stream->getNullableString(),                                // guild_id
            $stream->getNullableInt(),                                   // position
            $stream->getNullableSerializableArray(Overwrite::class),     // permission_overwrites
            $stream->getNullableString(),                                // name
            $stream->getNullableString(),                                // topic
            $stream->getNullableBool(),                                  // nsfw
            $stream->getNullableString(),                                // last_message_id
            $stream->getNullableInt(),                                   // bitrate
            $stream->getNullableInt(),                                   // user_limit
            $stream->getNullableInt(),                                   // rate_limit_per_user
            $stream->getNullableStringArray(),                           // recipients
            $stream->getNullableString(),                                // icon
            $stream->getNullableString(),                                // owner_id
            $stream->getNullableString(),                                // application_id
            $stream->getNullableBool(),                                  // managed
            $stream->getNullableString(),                                // parent_id
            $stream->getNullableLong(),                                  // last_pin_timestamp
            $stream->getNullableString(),                                // rtc_region
            VideoQualityMode::tryFrom($stream->getNullableByte() ?? -1), // video_quality_mode
            $stream->getNullableSerializable(ThreadMetadata::class),     // thread_metadata
            $stream->getNullableInt(),                                   // flags
            $stream->getNullableSerializableArray(ForumTag::class),      // available_tags
            $stream->getNullableStringArray()                            // applied_tags
        );
    }
}