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

use JaxkDev\DiscordBot\Models\Guild;
use JaxkDev\DiscordBot\Communication\Packets\Packet;

class GuildUpdate extends Packet{

    private Guild $guild;

    public function __construct(Guild $guild){
        parent::__construct();
        $this->guild = $guild;
    }

    public function getGuild(): Guild{
        return $this->guild;
    }

    public function __serialize(): array{
        return [
            $this->UID,
            $this->guild
        ];
    }

    public function __unserialize(array $data): void{
        [
            $this->UID,
            $this->guild
        ] = $data;
    }
}