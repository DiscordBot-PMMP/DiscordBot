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

/**
 * @implements BinarySerializable<ThreadMetadata>
 * @link https://discord.com/developers/docs/resources/channel#thread-metadata-object-thread-metadata-structure
 */
final class ThreadMetadata implements BinarySerializable{

    /** Whether the thread is archived */
    private bool $archived;

    /** The thread will stop showing in the channel list after auto_archive_duration minutes of inactivity, can be set to: 60, 1440, 4320, 10080 */
    private int $auto_archive_duration;

    /** Timestamp when the thread's archive status was last changed, used for calculating recent activity */
    private int $archive_timestamp;

    /** Whether the thread is locked; when a thread is locked, only users with MANAGE_THREADS can unarchive it */
    private bool $locked;

    /** Whether non-moderators can add other non-moderators to a thread; only available on private threads */
    private ?bool $invitable;

    /** Timestamp when the thread was created; only populated for threads created after 2022-01-09 */
    private ?int $create_timestamp;

    public function __construct(bool $archived, int $auto_archive_duration, int $archive_timestamp, bool $locked,
                                ?bool $invitable = null, ?int $create_timestamp = null){
        $this->archived = $archived;
        $this->auto_archive_duration = $auto_archive_duration;
        $this->archive_timestamp = $archive_timestamp;
        $this->locked = $locked;
        $this->invitable = $invitable;
        $this->create_timestamp = $create_timestamp;
    }

    public function getArchived(): bool{
        return $this->archived;
    }

    public function setArchived(bool $archived): void{
        $this->archived = $archived;
    }

    public function getAutoArchiveDuration(): int{
        return $this->auto_archive_duration;
    }

    public function setAutoArchiveDuration(int $auto_archive_duration): void{
        $this->auto_archive_duration = $auto_archive_duration;
    }

    public function getArchiveTimestamp(): int{
        return $this->archive_timestamp;
    }

    public function setArchiveTimestamp(int $archive_timestamp): void{
        $this->archive_timestamp = $archive_timestamp;
    }

    public function getLocked(): bool{
        return $this->locked;
    }

    public function setLocked(bool $locked): void{
        $this->locked = $locked;
    }

    public function getInvitable(): ?bool{
        return $this->invitable;
    }

    public function setInvitable(?bool $invitable): void{
        $this->invitable = $invitable;
    }

    public function getCreateTimestamp(): ?int{
        return $this->create_timestamp;
    }

    public function setCreateTimestamp(?int $create_timestamp): void{
        $this->create_timestamp = $create_timestamp;
    }

    public function binarySerialize(): BinaryStream{
        $stream = new BinaryStream();
        $stream->putBool($this->archived);
        $stream->putInt($this->auto_archive_duration);
        $stream->putLong($this->archive_timestamp);
        $stream->putBool($this->locked);
        $stream->putNullableBool($this->invitable);
        $stream->putNullableLong($this->create_timestamp);
        return $stream;
    }

    public static function fromBinary(BinaryStream $stream): self{
        return new self(
            $stream->getBool(),         // archived
            $stream->getInt(),          // auto_archive_duration
            $stream->getLong(),         // archive_timestamp
            $stream->getBool(),         // locked
            $stream->getNullableBool(), // invitable
            $stream->getNullableLong()  // create_timestamp
        );
    }
}