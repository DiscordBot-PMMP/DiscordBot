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

    //Nullables

    public function putNullable(?string $value): void{
        $this->putBool($value !== null);
        if($value !== null){
            $this->put($value);
        }
    }

    public function putNullableBool(?bool $value): void{
        $this->putBool($value !== null);
        if($value !== null){
            $this->putBool($value);
        }
    }

    public function getNullableBool(): ?bool{
        return $this->getBool() ? $this->getBool() : null;
    }

    public function putNullableByte(?int $value): void{
        $this->putBool($value !== null);
        if($value !== null){
            $this->putByte($value);
        }
    }

    public function getNullableByte(): ?int{
        return $this->getBool() ? $this->getByte() : null;
    }

    public function putNullableInt(?int $value): void{
        $this->putBool($value !== null);
        if($value !== null){
            $this->putInt($value);
        }
    }

    public function getNullableInt(): ?int{
        return $this->getBool() ? $this->getInt() : null;
    }

    public function putNullableString(?string $value): void{
        $this->putBool($value !== null);
        if($value !== null){
            $this->putString($value);
        }
    }

    public function getNullableString(): ?string{
        return $this->getBool() ? $this->getString() : null;
    }
}