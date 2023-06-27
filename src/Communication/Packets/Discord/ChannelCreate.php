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

use JaxkDev\DiscordBot\Communication\Packets\Packet;
use JaxkDev\DiscordBot\Models\Channels\GuildChannel;

class ChannelCreate extends Packet{

    private GuildChannel $channel;

    public function __construct(GuildChannel $channel){
        parent::__construct();
        $this->channel = $channel;
    }

    public function getChannel(): GuildChannel{
        return $this->channel;
    }

    public function __serialize(): array{
        return [
            $this->UID,
            $this->channel
        ];
    }

    public function __unserialize(array $data): void{
        [
            $this->UID,
            $this->channel
        ] = $data;
    }
}