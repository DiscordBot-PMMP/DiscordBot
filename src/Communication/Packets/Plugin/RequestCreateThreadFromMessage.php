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

namespace JaxkDev\DiscordBot\Communication\Packets\Plugin;

use JaxkDev\DiscordBot\Communication\BinaryStream;
use JaxkDev\DiscordBot\Communication\Packets\Packet;

/**
 * @link https://discord.com/developers/docs/resources/channel#start-thread-from-message
 */
final class RequestCreateThreadFromMessage extends Packet{

    public const SERIALIZE_ID = 84;

    private string $guild_id;

    private string $channel_id;

    private string $message_id;

    /** 1-100 character thread name */
    private string $name;

    /**
     * The thread will stop showing in the channel list after auto_archive_duration minutes of inactivity,
     * can be set to: 60, 1440, 4320, 10080
     */
    private ?int $auto_archive_duration;

    /** Amount of seconds a user has to wait before sending another message (0-21600) */
    private ?int $rate_limit_per_user;

    private ?string $reason;

    public function __construct(string $guild_id, string $channel_id, string $message_id, string $name,
                                ?int $auto_archive_duration, ?int $rate_limit_per_user, ?string $reason,
                                ?int $uid = null){
        parent::__construct($uid);
        $this->guild_id = $guild_id;
        $this->channel_id = $channel_id;
        $this->message_id = $message_id;
        $this->name = $name;
        $this->auto_archive_duration = $auto_archive_duration;
        $this->rate_limit_per_user = $rate_limit_per_user;
        $this->reason = $reason;
    }

    public function getGuildId(): string{
        return $this->guild_id;
    }

    public function getChannelId(): string{
        return $this->channel_id;
    }

    public function getMessageId(): string{
        return $this->message_id;
    }

    public function getName(): string{
        return $this->name;
    }

    public function getAutoArchiveDuration(): ?int{
        return $this->auto_archive_duration;
    }

    public function getRateLimitPerUser(): ?int{
        return $this->rate_limit_per_user;
    }

    public function getReason(): ?string{
        return $this->reason;
    }

    public function binarySerialize(): BinaryStream{
        $stream = new BinaryStream();
        $stream->putInt($this->getUID());
        $stream->putString($this->guild_id);
        $stream->putString($this->channel_id);
        $stream->putString($this->message_id);
        $stream->putString($this->name);
        $stream->putNullableInt($this->auto_archive_duration);
        $stream->putNullableInt($this->rate_limit_per_user);
        $stream->putNullableString($this->reason);
        return $stream;
    }

    public static function fromBinary(BinaryStream $stream): self{
        $uid = $stream->getInt();
        return new self(
            $stream->getString(),         // guild_id
            $stream->getString(),         // channel_id
            $stream->getString(),         // message_id
            $stream->getString(),         // name
            $stream->getNullableInt(),    // auto_archive_duration
            $stream->getNullableInt(),    // rate_limit_per_user
            $stream->getNullableString(), // reason
            $uid
        );
    }
}