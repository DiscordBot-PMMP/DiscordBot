<?php
/*
 * DiscordBot, PocketMine-MP Plugin.
 *
 * Licensed under the Open Software License version 3.0 (OSL-3.0)
 * Copyright (C) 2020-2021 JaxkDev
 *
 * Twitter :: @JaxkDev
 * Discord :: JaxkDev#2698
 * Email   :: JaxkDev@gmail.com
 */

namespace JaxkDev\DiscordBot\Communication\Packets\Plugin;

use JaxkDev\DiscordBot\Communication\Packets\Packet;
use JaxkDev\DiscordBot\Models\Webhook;

class RequestCreateWebhook extends Packet{

    /** @var Webhook */
    private $webhook;

    public function __construct(Webhook $webhook){
        parent::__construct();
        $this->webhook = $webhook;
    }

    public function getWebhook(): Webhook{
        return $this->webhook;
    }

    public function serialize(): ?string{
        return serialize([
            $this->UID,
            $this->webhook
        ]);
    }

    public function unserialize($data): void{
        [
            $this->UID,
            $this->webhook
        ] = unserialize($data);
    }
}