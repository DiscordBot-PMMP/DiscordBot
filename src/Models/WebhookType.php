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

namespace JaxkDev\DiscordBot\Models;

/**
 * @link https://discord.com/developers/docs/resources/webhook#webhook-object-webhook-types
 */
enum WebhookType: int{

    /**
     * Standard webhook
     * "Incoming Webhooks can post messages to channels with a generated token"
     */
    case INCOMING = 1;

    /**
     * Receiving 'news' from another channel.
     * "Channel Follower Webhooks are internal webhooks used with Channel Following to post new messages into channels"
     */
    case CHANNEL_FOLLOWER = 2;

    /**
     * "Application webhooks are webhooks used with Interactions"
     */
    case APPLICATION = 3;
}