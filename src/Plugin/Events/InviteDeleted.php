<?php

/*
 * DiscordBot, PocketMine-MP Plugin.
 *
 * Licensed under the Open Software License version 3.0 (OSL-3.0)
 * Copyright (C) 2020-present JaxkDev
 *
 * Discord :: JaxkDev
 * Email   :: JaxkDev@gmail.com
 */

namespace JaxkDev\DiscordBot\Plugin\Events;

use JaxkDev\DiscordBot\Plugin\Utils;
use pocketmine\plugin\Plugin;

/**
 * Emitted when a invite gets deleted/revoked/expires.
 *
 * @see InviteCreated
 */
final class InviteDeleted extends DiscordBotEvent{

    private ?string $guild_id;

    private ?string $channel_id;

    private string $invite_code;

    public function __construct(Plugin $plugin, ?string $guild_id, ?string $channel_id, string $invite_code){
        parent::__construct($plugin);
        if($guild_id !== null && !Utils::validDiscordSnowflake($guild_id)){
            throw new \AssertionError("Invalid guild_id given.");
        }
        if($channel_id !== null && !Utils::validDiscordSnowflake($channel_id)){
            throw new \AssertionError("Invalid channel_id given.");
        }
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