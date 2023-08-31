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

namespace JaxkDev\DiscordBot\Models\Messages\Component;

use JaxkDev\DiscordBot\Communication\BinarySerializable;

/**
 * @implements BinarySerializable<self>
 */
abstract class Component implements BinarySerializable{

    protected ComponentType $type;

    public function __construct(ComponentType $type){
        $this->type = $type;
    }

    public function getType(): ComponentType{
        return $this->type;
    }

    //no setter for type, it is immutable.
}