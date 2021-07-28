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

namespace JaxkDev\DiscordBot\Plugin\Events;

use JaxkDev\DiscordBot\Models\Messages\Message;
use pocketmine\plugin\Plugin;

/**
 * Emitted when a message has been deleted.
 *
 * If message was made/updated before bot started it will only have message id, channel id and server id.
 * If it was made/updated after bot started it will have the full message model.
 *
 * @see MessageUpdated
 * @see MessageSent
 */
class MessageDeleted extends DiscordBotEvent{

    /**
     * @var Message|array{"message_id": string, "channel_id": string, "server_id": string}
     */
    private $message;

    /**
     * @param Plugin                                                                            $plugin
     * @param Message|array{"message_id": string, "channel_id": string, "server_id": string}    $message
     */
    public function __construct(Plugin $plugin, $message){
        parent::__construct($plugin);
        $this->message = $message;
    }

    /**
     * @return Message|array{"message_id": string, "channel_id": string, "server_id": string}
     */
    public function getMessage(){
        return $this->message;
    }
}