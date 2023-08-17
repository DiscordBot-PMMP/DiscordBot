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

namespace JaxkDev\DiscordBot\Models\Channels;

use JaxkDev\DiscordBot\Plugin\Utils;

abstract class Channel{

    protected ?string $id;

    public function __construct(?string $id = null){
        $this->setId($id);
    }

    public function getId(): ?string{
        return $this->id;
    }

    public function setId(?string $id): void{
        if($id !== null && !Utils::validDiscordSnowflake($id)){
            throw new \AssertionError("Channel ID '$id' is invalid.");
        }
        $this->id = $id;
    }
}