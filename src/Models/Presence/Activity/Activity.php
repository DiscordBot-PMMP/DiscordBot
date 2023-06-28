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

namespace JaxkDev\DiscordBot\Models\Presence\Activity;

use JaxkDev\DiscordBot\Models\Emoji;
use JaxkDev\DiscordBot\Plugin\Api;

/** @link https://github.com/discord/discord-api-docs/blob/master/docs/topics/Gateway.md#activity-object */
final class Activity{

    /** @link https://discord.com/developers/docs/topics/gateway-events#activity-object-activity-flags */
    public const
        FLAG_INSTANCE = (1 << 0),
        FLAG_JOIN = (1 << 1),
        FLAG_SPECTATE = (1 << 2),
        FLAG_JOIN_REQUEST = (1 << 3),
        FLAG_SYNC = (1 << 4),
        FLAG_PLAY = (1 << 5),
        FLAG_PARTY_PRIVACY_FRIENDS = (1 << 6),
        FLAG_PARTY_PRIVACY_VOICE_CHANNEL = (1 << 7),
        FLAG_EMBEDDED = (1 << 8);

    /** Activity's Name */
    private string $name;

    /** Activity Type */
    private ActivityType $type;

    /** Stream URL, is validated when type is STREAMING. */
    private ?string $url;

    /** Unix timestamp (in ms) of when the activity was added to the user's session */
    private int $created_at;

    /**
     * Unix time (in milliseconds) of when the activity started/ends
     * @link https://discord.com/developers/docs/topics/gateway-events#activity-object-activity-timestamps
     */
    private ?int $start_timestamp;
    private ?int $end_timestamp;

    /** Application id for the game */
    private ?string $application_id;

    /** What the player is currently doing */
    private ?string $details;

    /** User's current party status */
    private ?string $state;

    /**
     * Emoji used for custom status (only name, id and animated fields are used)
     * @link https://discord.com/developers/docs/topics/gateway-events#activity-object-activity-emoji
     */
    private ?Emoji $emoji;

    /**
     * Information for the current party of the player
     * @link https://discord.com/developers/docs/topics/gateway-events#activity-object-activity-party
     */
    private ?string $party_id;
    private ?int    $party_size;
    private ?int    $party_max_size;

    /**
     * Images for the presence and their hover texts
     * @link https://discord.com/developers/docs/topics/gateway-events#activity-object-activity-assets
     */
    private ?string $asset_large_image;
    private ?string $asset_large_text;
    private ?string $asset_small_image;
    private ?string $asset_small_text;

    /**
     * Secrets for Rich Presence joining and spectating
     * @link https://discord.com/developers/docs/topics/gateway-events#activity-object-activity-secrets
     */
    private ?string $secret_join;
    private ?string $secret_spectate;
    private ?string $secret_match;

    /** Whether the activity is an instanced game session */
    private ?bool $instance;

    /**
     * @see Activity::FLAG_* constants
     * @link https://discord.com/developers/docs/topics/gateway-events#activity-object-activity-flags
     */
    private ?int $flags;

    /**
     * Max 2 buttons.
     * @var ActivityButton[]
     */
    private array $buttons;

    /**
     * The only parameters required (and allowed) to be set on creation for bot activity.
     *
     * @see Api::updateBotPresence()
     * @param ActivityButton[] $buttons Max 2 buttons.
     */
    public static function create(string $name, ActivityType $type, ?string $url = null, array $buttons = []): self{
        return new self($name, $type, $url, buttons: $buttons);
    }

    /** @param ActivityButton[] $buttons Max 2 buttons. */
    public function __construct(string  $name, ActivityType $type, ?string $url = null, ?int $created_at = null,
                                ?int    $start_timestamp = null, ?int $end_timestamp = null, ?string $application_id = null,
                                ?string $details = null, ?string $state = null, ?Emoji $emoji = null, ?string $party_id = null,
                                ?int    $party_size = null, ?int $party_max_size = null, ?string $asset_large_image = null,
                                ?string $asset_large_text = null, ?string $asset_small_image = null,
                                ?string $asset_small_text = null, ?string $secret_join = null,
                                ?string $secret_spectate = null, ?string $secret_match = null, ?bool $instance = null,
                                ?int    $flags = null, array $buttons = []){
        $this->setName($name);
        $this->setType($type);
        $this->setUrl($url);
        $this->setCreatedAt($created_at??time());
        $this->setStartTimestamp($start_timestamp);
        $this->setEndTimestamp($end_timestamp);
        $this->setApplicationId($application_id);
        $this->setDetails($details);
        $this->setState($state);
        $this->setEmoji($emoji);
        $this->setPartyId($party_id);
        $this->setPartySize($party_size);
        $this->setPartyMaxSize($party_max_size);
        $this->setAssetLargeImage($asset_large_image);
        $this->setAssetLargeText($asset_large_text);
        $this->setAssetSmallImage($asset_small_image);
        $this->setAssetSmallText($asset_small_text);
        $this->setSecretJoin($secret_join);
        $this->setSecretSpectate($secret_spectate);
        $this->setSecretMatch($secret_match);
        $this->setInstance($instance);
        $this->setFlags($flags);
        $this->setButtons($buttons);
    }

    public function getName(): string{
        return $this->name;
    }

    public function setName(string $name): void{
        $this->name = $name;
    }

    public function getType(): ActivityType{
        return $this->type;
    }

    public function setType(ActivityType $type): void{
        $this->type = $type;
    }

    public function getUrl(): ?string{
        return $this->url;
    }

    public function setUrl(?string $url): void{
        /*
        // Discord says it checks when type is streaming, but it seems it doesn't, both inbound and outbound...
        if($url !== null and $this->type === self::TYPE_STREAMING){
            if(!str_starts_with($url, "https://twitch.tv/") and !str_starts_with($url, "https://youtube.com/")){
                throw new \AssertionError("Invalid url '$url'.");
            }
        }
        */
        if($url !== null and !str_starts_with($url, "https://") and !str_starts_with($url, "http://")){
            throw new \AssertionError("Invalid url '$url'.");
        }
        $this->url = $url;
    }

    public function getCreatedAt(): int{
        return $this->created_at;
    }

    public function setCreatedAt(int $timestamp): void{
        if($timestamp < 0){
            throw new \AssertionError("Invalid created_at timestamp '$timestamp'.");
        }
        $this->created_at = $timestamp;
    }

    public function getStartTimestamp(): ?int{
        return $this->start_timestamp;
    }

    public function setStartTimestamp(?int $timestamp): void{
        if($timestamp !== null and $timestamp < 0){
            throw new \AssertionError("Invalid start timestamp '$timestamp'.");
        }
        $this->start_timestamp = $timestamp;
    }

    public function getEndTimestamp(): ?int{
        return $this->end_timestamp;
    }

    public function setEndTimestamp(?int $timestamp): void{
        if($timestamp !== null and $timestamp < 0){
            throw new \AssertionError("Invalid end timestamp '$timestamp'.");
        }
        $this->end_timestamp = $timestamp;
    }

    public function getApplicationId(): ?string{
        return $this->application_id;
    }

    public function setApplicationId(?string $application_id): void{
        $this->application_id = $application_id;
    }

    public function getDetails(): ?string{
        return $this->details;
    }

    public function setDetails(?string $details): void{
        $this->details = $details;
    }

    public function getState(): ?string{
        return $this->state;
    }

    public function setState(?string $state): void{
        $this->state = $state;
    }

    public function getEmoji(): ?Emoji{
        return $this->emoji;
    }

    public function setEmoji(?Emoji $emoji): void{
        $this->emoji = $emoji;
    }

    public function getPartyId(): ?string{
        return $this->party_id;
    }

    public function setPartyId(?string $party_id): void{
        $this->party_id = $party_id;
    }

    public function getPartySize(): ?int{
        return $this->party_size;
    }

    public function setPartySize(?int $party_size): void{
        if($party_size !== null and $party_size < 0){
            throw new \AssertionError("Invalid party size '$party_size'.");
        }
        $this->party_size = $party_size;
    }

    public function getPartyMaxSize(): ?int{
        return $this->party_max_size;
    }

    public function setPartyMaxSize(?int $party_max_size): void{
        if($party_max_size !== null and $party_max_size < 0){
            throw new \AssertionError("Invalid party max size '$party_max_size'.");
        }
        $this->party_max_size = $party_max_size;
    }

    public function getAssetLargeImage(): ?string{
        return $this->asset_large_image;
    }

    public function setAssetLargeImage(?string $asset_large_image): void{
        $this->asset_large_image = $asset_large_image;
    }

    public function getAssetLargeText(): ?string{
        return $this->asset_large_text;
    }

    public function setAssetLargeText(?string $asset_large_text): void{
        $this->asset_large_text = $asset_large_text;
    }

    public function getAssetSmallImage(): ?string{
        return $this->asset_small_image;
    }

    public function setAssetSmallImage(?string $asset_small_image): void{
        $this->asset_small_image = $asset_small_image;
    }

    public function getAssetSmallText(): ?string{
        return $this->asset_small_text;
    }

    public function setAssetSmallText(?string $asset_small_text): void{
        $this->asset_small_text = $asset_small_text;
    }

    public function getSecretJoin(): ?string{
        return $this->secret_join;
    }

    public function setSecretJoin(?string $secret_join): void{
        $this->secret_join = $secret_join;
    }

    public function getSecretSpectate(): ?string{
        return $this->secret_spectate;
    }

    public function setSecretSpectate(?string $secret_spectate): void{
        $this->secret_spectate = $secret_spectate;
    }

    public function getSecretMatch(): ?string{
        return $this->secret_match;
    }

    public function setSecretMatch(?string $secret_match): void{
        $this->secret_match = $secret_match;
    }

    public function getInstance(): ?bool{
        return $this->instance;
    }

    public function setInstance(?bool $instance): void{
        $this->instance = $instance;
    }

    public function getFlags(): ?int{
        return $this->flags;
    }

    public function setFlags(?int $flags): void{
        $this->flags = $flags;
    }

    /** @return ActivityButton[] */
    public function getButtons(): array{
        return $this->buttons;
    }

    /** @param ActivityButton[] $buttons */
    public function setButtons(array $buttons): void{
        if(sizeof($buttons) > 2){
            throw new \AssertionError("Too many buttons (max 2).");
        }
        foreach($buttons as $button){
            if(!($button instanceof ActivityButton)){
                throw new \AssertionError("Invalid button provided, must be of type ".ActivityButton::class);
            }
        }
        $this->buttons = $buttons;
    }

    //----- Serialization -----//

    public function __serialize(): array{
        return [
            $this->name,
            $this->type,
            $this->url,
            $this->created_at,
            $this->start_timestamp,
            $this->end_timestamp,
            $this->application_id,
            $this->details,
            $this->state,
            $this->emoji,
            $this->party_id,
            $this->party_size,
            $this->party_max_size,
            $this->asset_large_image,
            $this->asset_large_text,
            $this->asset_small_image,
            $this->asset_small_text,
            $this->secret_join,
            $this->secret_spectate,
            $this->secret_match,
            $this->instance,
            $this->flags,
            $this->buttons
        ];
    }

    public function __unserialize(array $data): void{
        [
            $this->name,
            $this->type,
            $this->url,
            $this->created_at,
            $this->start_timestamp,
            $this->end_timestamp,
            $this->application_id,
            $this->details,
            $this->state,
            $this->emoji,
            $this->party_id,
            $this->party_size,
            $this->party_max_size,
            $this->asset_large_image,
            $this->asset_large_text,
            $this->asset_small_image,
            $this->asset_small_text,
            $this->secret_join,
            $this->secret_spectate,
            $this->secret_match,
            $this->instance,
            $this->flags,
            $this->buttons
        ] = $data;
    }
}