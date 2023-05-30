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

use JaxkDev\DiscordBot\Communication\Packets\Packet;
use JaxkDev\DiscordBot\Models\Channels\ServerChannel;

class RequestCreateChannel extends Packet{

    /** @var ServerChannel */
    private $channel;

    public function __construct(ServerChannel $channel){
        parent::__construct();
        $this->channel = $channel;
    }

    public function getChannel(): ServerChannel{
        return $this->channel;
    }

    public function __serialize(): array{
        return [
            $this->UID,
            $this->channel
        ];
    }

    public function __unserialize($data): void{
        try{
            [
                $this->UID,
                $this->channel
            ] = $data;
        }catch(\Throwable $e){
            throw new \AssertionError("Failed to unserialize '".get_parent_class($this)."'", 0, $e);
        }
    }
}