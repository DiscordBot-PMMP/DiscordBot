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

use JaxkDev\DiscordBot\Communication\BinaryStream;
use JaxkDev\DiscordBot\Models\Presence\Activity\Activity;
use JaxkDev\DiscordBot\Plugin\Api;

/** A simple class to hold all presence data for members. */
class Presence implements \JsonSerializable{

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
     * Create a new presence instance for the bot.
     *
     * @see Api::updateBotPresence() To update bots presence.
     */
    public static function create(Status $status = Status::ONLINE, Activity $activity = null): self{
        return new self($status, $activity === null ? [] : [$activity], null);
    }

    /** @param Activity[] $activities */
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
        $stream->putSerializable($this->status);
        $stream->putSerializableArray($this->activities);
        $stream->putNullableSerializable($this->client_status);
        return $stream;
    }

    public static function fromBinary(BinaryStream $stream): self{
        return new self(
            $stream->getSerializable(Status::class),                // status
            $stream->getSerializableArray(Activity::class),         // activities
            $stream->getNullableSerializable(ClientStatus::class)   // client_status
        );
    }

    public function jsonSerialize(): array{
        return [
            "status" => $this->status->jsonSerialize(),
            "activities" => array_map(fn(Activity $activity) => $activity->jsonSerialize(), $this->activities),
            "client_status" => $this->client_status?->jsonSerialize()
        ];
    }

    public static function fromJson(array $json): self{
        return new self(
            Status::fromJson($json["status"]),
            array_map(fn(array $activity) => Activity::fromJson($activity), $json["activities"]),
            $json["client_status"] === null ? null : ClientStatus::fromJson($json["client_status"])
        );
    }
}