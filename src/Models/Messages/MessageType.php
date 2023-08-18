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

namespace JaxkDev\DiscordBot\Models\Messages;

use function in_array;

/**
 * @link https://discord.com/developers/docs/resources/channel#message-object-message-types
 */
enum MessageType: int{

    case DEFAULT = 0;
    case RECIPIENT_ADD = 1;
    case RECIPIENT_REMOVE = 2;
    case CALL = 3;
    case CHANNEL_NAME_CHANGE = 4;
    case CHANNEL_ICON_CHANGE = 5;
    case CHANNEL_PINNED_MESSAGE = 6;
    case USER_JOIN = 7;
    case GUILD_BOOST = 8;
    case GUILD_BOOST_TIER_1 = 9;
    case GUILD_BOOST_TIER_2 = 10;
    case GUILD_BOOST_TIER_3 = 11;
    case CHANNEL_FOLLOW_ADD = 12;
    case GUILD_DISCOVERY_DISQUALIFIED = 14;
    case GUILD_DISCOVERY_REQUALIFIED = 15;
    case GUILD_DISCOVERY_GRACE_PERIOD_INITIAL_WARNING = 16;
    case GUILD_DISCOVERY_GRACE_PERIOD_FINAL_WARNING = 17;
    case THREAD_CREATED = 18;
    case REPLY = 19;
    case CHAT_INPUT_COMMAND = 20;
    case THREAD_STARTER_MESSAGE = 21;
    case GUILD_INVITE_REMINDER = 22;
    case CONTEXT_MENU_COMMAND = 23;
    case AUTO_MODERATION_ACTION = 24;
    case ROLE_SUBSCRIPTION_PURCHASE = 25;
    case INTERACTION_PREMIUM_UPSELL = 26;
    case STAGE_START = 27;
    case STAGE_END = 28;
    case STAGE_SPEAKER = 29;
    case STAGE_TOPIC = 31;
    case GUILD_APPLICATION_PREMIUM_SUBSCRIPTION = 32;

    public function deletable(): bool{
        return in_array($this->value, [
            self::DEFAULT,
            self::CHANNEL_PINNED_MESSAGE,
            self::USER_JOIN,
            self::GUILD_BOOST,
            self::GUILD_BOOST_TIER_1,
            self::GUILD_BOOST_TIER_2,
            self::GUILD_BOOST_TIER_3,
            self::CHANNEL_FOLLOW_ADD,
            self::THREAD_CREATED,
            self::REPLY,
            self::CHAT_INPUT_COMMAND,
            self::GUILD_INVITE_REMINDER,
            self::CONTEXT_MENU_COMMAND,
            self::AUTO_MODERATION_ACTION,
            self::ROLE_SUBSCRIPTION_PURCHASE,
            self::INTERACTION_PREMIUM_UPSELL,
            self::STAGE_START,
            self::STAGE_END,
            self::STAGE_SPEAKER,
            self::STAGE_TOPIC,
        ], true);
    }
}
