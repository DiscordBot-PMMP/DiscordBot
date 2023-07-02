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

namespace JaxkDev\DiscordBot\Communication;

class BinaryStream extends \pocketmine\utils\BinaryStream{

    public function putString(string $value): void{
        $this->putInt(strlen($value));
        $this->put($value);
    }

    public function getString(): string{
        return $this->get($this->getInt());
    }
}