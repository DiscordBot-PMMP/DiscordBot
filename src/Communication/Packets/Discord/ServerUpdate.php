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

namespace JaxkDev\DiscordBot\Communication\Packets\Discord;

use JaxkDev\DiscordBot\Models\Server;
use JaxkDev\DiscordBot\Communication\Packets\Packet;

class ServerUpdate extends Packet{

    /** @var Server */
    private $server;

    public function __construct(Server $server){
        parent::__construct();
        $this->server = $server;
    }

    public function getServer(): Server{
        return $this->server;
    }

    public function __serialize(): array{
        return [
            $this->UID,
            $this->server
        ];
    }

    public function __unserialize($data): void{
        try{
            [
                $this->UID,
                $this->server
            ] = $data;
        }catch(\Throwable $e){
            throw new \AssertionError("Failed to unserialize '".get_parent_class($this)."'", 0, $e);
        }
    }
}