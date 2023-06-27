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

namespace JaxkDev\DiscordBot\Communication\Packets\Plugin;

use JaxkDev\DiscordBot\Communication\Packets\Packet;

class RequestDeleteWebhook extends Packet{

    private string $webhook_id;

    private string $channel_id;

    public function __construct(string $channel_id, string $webhook_id){
        parent::__construct();
        $this->webhook_id = $webhook_id;
        $this->channel_id = $channel_id;
    }

    public function getWebhookId(): string{
        return $this->webhook_id;
    }

    public function getChannelId(): string{
        return $this->channel_id;
    }

    public function __serialize(): array{
        return [
            $this->UID,
            $this->webhook_id,
            $this->channel_id
        ];
    }

    public function __unserialize(array $data): void{
        [
            $this->UID,
            $this->webhook_id,
            $this->channel_id
        ] = $data;
    }
}