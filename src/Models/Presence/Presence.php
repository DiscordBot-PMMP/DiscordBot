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

namespace JaxkDev\DiscordBot\Models\Presence;

use JaxkDev\DiscordBot\Communication\BinarySerializable;
use JaxkDev\DiscordBot\Communication\BinaryStream;
use JaxkDev\DiscordBot\Models\Presence\Activity\Activity;
use JaxkDev\DiscordBot\Plugin\Api;

/**
 * A simple class to hold all presence data for members.
 * @implements BinarySerializable<Presence>
 */
class Presence implements BinarySerializable{

    /** Current status */
    private Status $status;

    /**
     * User's current activities
     * @var Activity[]
     */
    private array $activities;

    /** User's platform-dependent status */
    private ?ClientStatus $client_status;

    /**
     * @param Activity[] $activities
     * @internal
     * @see Api::updateBotPresence() To update bot presence.
     */
    public function __construct(Status $status, array $activities, ?ClientStatus $client_status){
        $this->setStatus($status);
        $this->setActivities($activities);
        $this->setClientStatus($client_status);
    }

    public function getStatus(): Status{
        return $this->status;
    }

    public function setStatus(Status $status): void{
        $this->status = $status;
    }

    /** @return Activity[] */
    public function getActivities(): array{
        return $this->activities;
    }

    /** @param Activity[] $activities */
    public function setActivities(array $activities): void{
        $this->activities = $activities;
    }

    public function getClientStatus(): ?ClientStatus{
        return $this->client_status;
    }

    public function setClientStatus(?ClientStatus $client_status): void{
        $this->client_status = $client_status;
    }

    //----- Serialization -----//

    public function binarySerialize(): BinaryStream{
        $stream = new BinaryStream();
        $stream->putString($this->status->value);
        $stream->putSerializableArray($this->activities);
        $stream->putNullableSerializable($this->client_status);
        return $stream;
    }

    public static function fromBinary(BinaryStream $stream): self{
        return new self(
            Status::from($stream->getString()),                     // status
            $stream->getSerializableArray(Activity::class),         // activities
            $stream->getNullableSerializable(ClientStatus::class)   // client_status
        );
    }
}