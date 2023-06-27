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

class RequestRevokeInvite extends Packet{

    private string $guild_id;

    private string $invite_code;

    public function __construct(string $guild_id, string $invite_code){
        parent::__construct();
        $this->guild_id = $guild_id;
        $this->invite_code = $invite_code;
    }

    public function getGuildId(): string{
        return $this->guild_id;
    }

    public function getInviteCode(): string{
        return $this->invite_code;
    }

    public function __serialize(): array{
        return [
            $this->UID,
            $this->guild_id,
            $this->invite_code
        ];
    }

    public function __unserialize(array $data): void{
        [
            $this->UID,
            $this->guild_id,
            $this->invite_code
        ] = $data;
    }
}