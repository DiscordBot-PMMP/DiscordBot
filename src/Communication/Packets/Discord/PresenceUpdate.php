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

namespace JaxkDev\DiscordBot\Communication\Packets\Discord;

use JaxkDev\DiscordBot\Communication\Packets\Packet;
use JaxkDev\DiscordBot\Models\Activity\Activity;

class PresenceUpdate extends Packet{

    private string $member_id;

    private string $status;

    /** @var array{"mobile": string|null, "desktop": string|null, "web": string|null} */
    private array $client_status;

    /** @var Activity[] */
    private array $activities;

    /**
     * @param array{"mobile": string|null, "desktop": string|null, "web": string|null} $client_status
     * @param Activity[]                                                               $activities
     */
    public function __construct(string $member_id, string $status, array $client_status, array $activities){
        parent::__construct();
        $this->member_id = $member_id;
        $this->status = $status;
        $this->client_status = $client_status;
        $this->activities = $activities;
    }

    public function getMemberId(): string{
        return $this->member_id;
    }

    public function getStatus(): string{
        return $this->status;
    }

    /** @return array{"mobile": string|null, "desktop": string|null, "web": string|null} */
    public function getClientStatus(): array{
        return $this->client_status;
    }

    /** @return Activity[] */
    public function getActivities(): array{
        return $this->activities;
    }

    public function __serialize(): array{
        return [
            $this->UID,
            $this->member_id,
            $this->status,
            $this->client_status,
            $this->activities
        ];
    }

    public function __unserialize(array $data): void{
        [
            $this->UID,
            $this->member_id,
            $this->status,
            $this->client_status,
            $this->activities
        ] = $data;
    }
}