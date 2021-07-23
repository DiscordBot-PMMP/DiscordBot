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

namespace JaxkDev\DiscordBot\Communication\Packets\Discord;

use JaxkDev\DiscordBot\Communication\Packets\Packet;
use JaxkDev\DiscordBot\Models\Activity;

class PresenceUpdate extends Packet{

    /** @var string */
    private $member_id;

    /** @var string */
    private $status;

    /** @var array{"mobile": string|null, "desktop": string|null, "web": string|null} */
    private $client_status;

    /** @var Activity[] */
    private $activities;

    /**
     * PresenceUpdate constructor.
     *
     * @param string                                                                   $member_id
     * @param string                                                                   $status
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

    public function serialize(): ?string{
        return serialize([
            $this->UID,
            $this->member_id,
            $this->status,
            $this->client_status,
            $this->activities
        ]);
    }

    public function unserialize($data): void{
        [
            $this->UID,
            $this->member_id,
            $this->status,
            $this->client_status,
            $this->activities
        ] = unserialize($data);
    }
}