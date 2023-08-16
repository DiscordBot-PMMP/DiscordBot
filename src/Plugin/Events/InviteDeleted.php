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

use pocketmine\plugin\Plugin;

/**
 * Emitted when a invite gets deleted/revoked/expires.
 * 
 * @see InviteCreated
 */
class InviteDeleted extends DiscordBotEvent{

    private ?string $guild_id;

    private ?string $channel_id;

    private string $invite_code;

    public function __construct(Plugin $plugin, ?string $guild_id, ?string $channel_id, string $invite_code){
        parent::__construct($plugin);
        $this->guild_id = $guild_id;
        $this->channel_id = $channel_id;
        $this->invite_code = $invite_code;
    }

    public function getGuildId(): ?string{
        return $this->guild_id;
    }

    public function getChannelId(): ?string{
        return $this->channel_id;
    }

    public function getInviteCode(): string{
        return $this->invite_code;
    }
}