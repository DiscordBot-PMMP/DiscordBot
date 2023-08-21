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

namespace JaxkDev\DiscordBot\Models;

use JaxkDev\DiscordBot\Communication\BinarySerializable;
use JaxkDev\DiscordBot\Communication\BinaryStream;
use function JaxkDev\DiscordBot\Plugin\Utils\validDiscordSnowflake;

/**
 * @implements BinarySerializable<RoleTags>
 * @link https://discord.com/developers/docs/topics/permissions#role-object-role-tags-structure
 */
class RoleTags implements BinarySerializable{

    /** The id of the bot this role belongs to */
    private ?string $bot_id;

    /** The id of the integration this role belongs to */
    private ?string $integration_id;

    /** Whether this is the guild's Booster role */
    private ?bool $premium_subscriber;

    /** The id of this role's subscription sku and listing */
    private ?string $subscription_listing_id;

    /** Whether this role is available for purchase */
    private ?bool $available_for_purchase;

    /** Whether this role is a guild's linked role */
    private ?bool $guild_connections;

    //No create method, this is read only.

    public function __construct(?string $bot_id, ?string $integration_id, ?bool $premium_subscriber,
                                ?string $subscription_listing_id, ?bool $available_for_purchase,
                                ?bool $guild_connections){
        $this->setBotId($bot_id);
        $this->setIntegrationId($integration_id);
        $this->setPremiumSubscriber($premium_subscriber);
        $this->setSubscriptionListingId($subscription_listing_id);
        $this->setAvailableForPurchase($available_for_purchase);
        $this->setGuildConnections($guild_connections);
    }

    public function getBotId(): ?string{
        return $this->bot_id;
    }

    public function setBotId(?string $bot_id): void{
        if($bot_id !== null && !validDiscordSnowflake($bot_id)){
            throw new \InvalidArgumentException("Bot ID '$bot_id' is invalid.");
        }
        $this->bot_id = $bot_id;
    }

    public function getIntegrationId(): ?string{
        return $this->integration_id;
    }

    public function setIntegrationId(?string $integration_id): void{
        if($integration_id !== null && !validDiscordSnowflake($integration_id)){
            throw new \InvalidArgumentException("Integration ID '$integration_id' is invalid.");
        }
        $this->integration_id = $integration_id;
    }

    public function getPremiumSubscriber(): ?bool{
        return $this->premium_subscriber;
    }

    public function setPremiumSubscriber(?bool $premium_subscriber): void{
        $this->premium_subscriber = $premium_subscriber;
    }

    public function getSubscriptionListingId(): ?string{
        return $this->subscription_listing_id;
    }

    public function setSubscriptionListingId(?string $subscription_listing_id): void{
        if($subscription_listing_id !== null && !validDiscordSnowflake($subscription_listing_id)){
            throw new \InvalidArgumentException("Subscription listing ID '$subscription_listing_id' is invalid.");
        }
        $this->subscription_listing_id = $subscription_listing_id;
    }

    public function getAvailableForPurchase(): ?bool{
        return $this->available_for_purchase;
    }

    public function setAvailableForPurchase(?bool $available_for_purchase): void{
        $this->available_for_purchase = $available_for_purchase;
    }

    public function getGuildConnections(): ?bool{
        return $this->guild_connections;
    }

    public function setGuildConnections(?bool $guild_connections): void{
        $this->guild_connections = $guild_connections;
    }

    //----- Serialization -----//

    public function binarySerialize(): BinaryStream{
        $stream = new BinaryStream();
        $stream->putNullableString($this->bot_id);
        $stream->putNullableString($this->integration_id);
        $stream->putNullableBool($this->premium_subscriber);
        $stream->putNullableString($this->subscription_listing_id);
        $stream->putNullableBool($this->available_for_purchase);
        $stream->putNullableBool($this->guild_connections);
        return $stream;
    }

    public static function fromBinary(BinaryStream $stream): self{
        return new self(
            $stream->getNullableString(),   // bot_id
            $stream->getNullableString(),   // integration_id
            $stream->getNullableBool(),     // premium_subscriber
            $stream->getNullableString(),   // subscription_listing_id
            $stream->getNullableBool(),     // available_for_purchase
            $stream->getNullableBool()      // guild_connections
        );
    }
}