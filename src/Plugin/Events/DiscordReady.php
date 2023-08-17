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

namespace JaxkDev\DiscordBot\Plugin\Events;

use JaxkDev\DiscordBot\Models\Presence\Activity\Activity;
use JaxkDev\DiscordBot\Models\Presence\Status;
use JaxkDev\DiscordBot\Models\User;
use pocketmine\plugin\Plugin;

/**
 * DiscordBot has connected, and we are now in contact with discord.
 * You can now use the API.
 *
 * @see DiscordClosed Emitted when DiscordBot disconnects.
 */
class DiscordReady extends DiscordBotEvent{

    private User $bot_user;

    private Activity $activity;

    private Status $status;

    public function __construct(Plugin $plugin, User $bot_user, Activity $activity, Status $status){
        parent::__construct($plugin);
        $this->bot_user = $bot_user;
        $this->activity = $activity;
        $this->status = $status;
    }

    public function getBotUser(): User{
        return $this->bot_user;
    }

    public function getActivity(): Activity{
        return $this->activity;
    }

    public function setActivity(Activity $activity): void{
        $this->activity = $activity;
    }

    public function getStatus(): Status{
        return $this->status;
    }

    public function setStatus(Status $status): void{
        $this->status = $status;
    }
}