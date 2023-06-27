<?php
/*
 * DiscordBot, PocketMine-MP Plugin.
 *
 * Licensed under the Open Software License version 3.0 (OSL-3.0)
 * Copyright (C) 2020-present JaxkDev
 *
 * Twitter :: @JaxkDev
 * Discord :: JaxkDev#2698
 * Email   :: JaxkDev@gmail.com
 */

namespace JaxkDev\DiscordBot\Communication\Packets\Plugin;

use JaxkDev\DiscordBot\Models\Activity;
use JaxkDev\DiscordBot\Communication\Packets\Packet;

class RequestUpdatePresence extends Packet{

    private Activity $activity;

    private string $status;

    public function __construct(Activity $activity, string $status){
        parent::__construct();
        $this->activity = $activity;
        $this->status = $status;
    }

    public function getActivity(): Activity{
        return $this->activity;
    }

    public function getStatus(): string{
        return $this->status;
    }

    public function __serialize(): array{
        return [
            $this->UID,
            $this->activity,
            $this->status
        ];
    }

    public function __unserialize(array $data): void{
        [
            $this->UID,
            $this->activity,
            $this->status
        ] = $data;
    }
}