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

use JaxkDev\DiscordBot\Communication\Packets\Packet;

class ServerLeave extends Packet{

    /** @var string */
    private $server_id;

    public function __construct(string $server_id){
        parent::__construct();
        $this->server_id = $server_id;
    }

    public function getServerId(): string{
        return $this->server_id;
    }

    public function __serialize(): array{
        return [
            $this->UID,
            $this->server_id
        ];
    }

    public function __unserialize($data): void{
        try{
            [
                $this->UID,
                $this->server_id
            ] = $data;
        }catch(\Throwable $e){
            throw new \AssertionError("Failed to unserialize '".get_parent_class($this)."'", 0, $e);
        }
    }
}