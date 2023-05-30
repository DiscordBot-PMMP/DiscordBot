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

namespace JaxkDev\DiscordBot\Communication\Packets\Discord;

use JaxkDev\DiscordBot\Communication\Packets\Packet;

class InviteDelete extends Packet{

    /** @var string */
    private $invite_code;

    public function __construct(string $invite_code){
        parent::__construct();
        $this->invite_code = $invite_code;
    }

    public function getInviteCode(): string{
        return $this->invite_code;
    }

    public function __serialize(): array{
        return [
            $this->UID,
            $this->invite_code
        ];
    }

    public function __unserialize($data): void{
        try{
            [
                $this->UID,
                $this->invite_code
            ] = $data;
        }catch(\Throwable $e){
            throw new \AssertionError("Failed to unserialize '".get_parent_class($this)."'", 0, $e);
        }
    }
}