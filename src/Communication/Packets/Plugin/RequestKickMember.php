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

class RequestKickMember extends Packet{

    /** @var string */
    private $server_id;

    /** @var string */
    private $user_id;

    public function __construct(string $server_id, string $user_id){
        parent::__construct();
        $this->server_id = $server_id;
        $this->user_id = $user_id;
    }

    public function getServerId(): string{
        return $this->server_id;
    }

    public function getUserId(): string{
        return $this->user_id;
    }

    public function serialize(): ?string{
        return serialize([
            $this->UID,
            $this->server_id,
            $this->user_id
        ]);
    }

    public function unserialize($data): void{
        $data = unserialize($data);
        if(!is_array($data)){
            throw new \AssertionError("Failed to unserialize data to array, got '".gettype($data)."' instead.");
        }
        [
            $this->UID,
            $this->server_id,
            $this->user_id
        ] = $data;
    }
}