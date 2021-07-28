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

namespace JaxkDev\DiscordBot\Plugin\Events;

use JaxkDev\DiscordBot\Models\Activity;
use JaxkDev\DiscordBot\Models\Member;
use pocketmine\plugin\Plugin;

/**
 * Emitted when a members presence is updated.
 */
class PresenceUpdated extends DiscordBotEvent{

    /** @var Member */
    private $member;

    /** @var string */
    private $new_status;

    /** @var array{"mobile": string|null, "desktop": string|null, "web": string|null} */
    private $new_client_status;

    /** @var Activity[] */
    private $new_activities;

    /**
     * @param Plugin                                                                    $plugin
     * @param Member                                                                    $member
     * @param string                                                                    $new_status
     * @param array{"mobile": string|null, "desktop": string|null, "web": string|null}  $new_client_status
     * @param Activity[]                                                                $new_activities
     */
    public function __construct(Plugin $plugin, Member $member, string $new_status, array $new_client_status, array $new_activities){
        parent::__construct($plugin);
        $this->member = $member;
        $this->new_status = $new_status;
        $this->new_client_status = $new_client_status;
        $this->new_activities = $new_activities;
    }

    public function getMember(): Member{
        return $this->member;
    }

    public function getNewStatus(): string{
        return $this->new_status;
    }

    /** @return array{"mobile": string|null, "desktop": string|null, "web": string|null} */
    public function getNewClientStatus(): array{
        return $this->new_client_status;
    }

    /** @return Activity[] */
    public function getNewActivities(): array{
        return $this->new_activities;
    }
}