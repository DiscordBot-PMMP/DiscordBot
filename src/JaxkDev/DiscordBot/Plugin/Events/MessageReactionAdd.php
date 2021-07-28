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

use Discord\WebSockets\Events\MessageReactionRemove;
use JaxkDev\DiscordBot\Models\Channels\Channel;
use JaxkDev\DiscordBot\Models\Member;
use pocketmine\plugin\Plugin;

/**
 * Emitted when a reaction is added to a message.
 *
 * @see MessageReactionRemove
 * @see MessageReactionRemoveAll
 * @see MessageReactionRemoveEmoji
 */
class MessageReactionAdd extends DiscordBotEvent{

    /** @var string */
    private $emoji;

    /** @var string */
    private $message_id;

    /** @var Channel */
    private $channel;

    /** @var Member */
    private $member;

    public function __construct(Plugin $plugin, string $emoji, string $message_id, Channel $channel, Member $member){
        parent::__construct($plugin);
        $this->emoji = $emoji;
        $this->message_id = $message_id;
        $this->channel = $channel;
        $this->member = $member;
    }

    public function getEmoji(): string{
        return $this->emoji;
    }

    public function getMessageId(): string{
        return $this->message_id;
    }

    public function getChannel(): Channel{
        return $this->channel;
    }

    public function getMember(): Member{
        return $this->member;
    }
}