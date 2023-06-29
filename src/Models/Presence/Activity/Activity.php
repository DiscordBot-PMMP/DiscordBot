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
final class Activity implements \JsonSerializable{

    /** @link https://discord.com/developers/docs/topics/gateway-events#activity-object-activity-flags */
    public const FLAGS = [
        "INSTANCE" => (1 << 0),
        "JOIN" => (1 << 1),
        "SPECTATE" => (1 << 2),
        "JOIN_REQUEST" => (1 << 3),
        "SYNC" => (1 << 4),
        "PLAY" => (1 << 5),
        "PARTY_PRIVACY_FRIENDS" => (1 << 6),
        "PARTY_PRIVACY_VOICE_CHANNEL" => (1 << 7),
        "EMBEDDED" => (1 << 8)
    ];

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
    private ?int $flags_bitwise;

    /**
     * All the flags possible and their current state, or null if no bitwise present.
     * @var ?array<string, bool>
     */
    private ?array $flags;

    /**
     * Max 2 buttons.
     * @var ActivityButton[]
     */
    private array $buttons;

    /**
     * The only parameters required (and allowed) to be set on creation for bot activity.
     *
     * @see Api::updateBotPresence()
     */
    public static function create(string $name, ActivityType $type, ?string $url = null): self{
        return new self($name, $type, $url);
    }

    /** @param ActivityButton[] $buttons Max 2 buttons. */
    public function __construct(string  $name, ActivityType $type, ?string $url = null, ?int $created_at = null,
                                ?int $start_timestamp = null, ?int $end_timestamp = null, ?string $application_id = null,
                                ?string $details = null, ?string $state = null, ?Emoji $emoji = null, ?string $party_id = null,
                                ?int $party_size = null, ?int $party_max_size = null, ?string $asset_large_image = null,
                                ?string $asset_large_text = null, ?string $asset_small_image = null,
                                ?string $asset_small_text = null, ?string $secret_join = null,
                                ?string $secret_spectate = null, ?string $secret_match = null, ?bool $instance = null,
                                ?int $flags = null, array $buttons = []){
        $this->setName($name);
        $this->setType($type);
        $this->setUrl($url);
        $this->setCreatedAt($created_at ?? time());
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
        $this->setFlagsBitwise($flags);
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

    public function getFlagsBitwise(): ?int{
        return $this->flags_bitwise;
    }

    public function setFlagsBitwise(?int $flags_bitwise): void{
        $this->flags_bitwise = $flags_bitwise;
        if($flags_bitwise !== null){
            $this->recalculateFlags();
        }else{
            $this->flags = null;
        }
    }

    /**
     * Array of flags and their state, or null if flags_bitwise is null.
     * @return ?array<string, bool>
     */
    public function getFlags(): ?array{
        if($this->flags === null and $this->flags_bitwise !== null){
            $this->recalculateFlags();
        }
        return $this->flags;
    }

    public function getFlag(string $flag): ?bool{
        if($this->flags_bitwise !== null){
            if($this->flags === null){
                $this->recalculateFlags();
            }
            return $this->flags[$flag] ?? null;
        }
        return null;
    }

    public function setFlag(string $flag, bool $value): void{
        if(!in_array($flag, array_keys(self::FLAGS), true)){
            throw new \AssertionError("Invalid flag '{$flag}'.");
        }

        if($this->flags_bitwise === null){
            $this->flags_bitwise = self::FLAGS[$flag] ?? 0;
            $this->recalculateFlags();
            return;
        }

        if(($this->flags[$flag] ?? null) !== $value){
            $this->flags_bitwise ^= self::FLAGS[$flag];
        }

        if($this->flags !== null){
            $this->flags[$flag] = $value;
        }else{
            $this->recalculateFlags();
        }
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

    /**
     * Recalculate the flags from the bitwise value.
     * @internal
     */
    private function recalculateFlags(): void{
        $this->flags = [];
        foreach(self::FLAGS as $flag => $bitwise){
            $this->flags[$flag] = ($this->flags_bitwise & $bitwise) !== 0;
        }
    }

    //----- Serialization -----//

    public function jsonSerialize(): array{
        return [
            "name" => $this->name,
            "type" => $this->type->jsonSerialize(),
            "url" => $this->url,
            "created_at" => $this->created_at,
            "timestamps" => [
                "start" => $this->start_timestamp,
                "end" => $this->end_timestamp
            ],
            "application_id" => $this->application_id,
            "details" => $this->details,
            "state" => $this->state,
            "emoji" => $this->emoji?->jsonSerialize(),
            "party" => [
                "id" => $this->party_id,
                "size" => $this->party_size,
                "max_size" => $this->party_max_size
            ],
            "assets" => [
                "large_image" => $this->asset_large_image,
                "large_text" => $this->asset_large_text,
                "small_image" => $this->asset_small_image,
                "small_text" => $this->asset_small_text
            ],
            "secrets" => [
                "join" => $this->secret_join,
                "spectate" => $this->secret_spectate,
                "match" => $this->secret_match
            ],
            "instance" => $this->instance,
            "flags" => $this->flags_bitwise,
            "buttons" => array_map(fn(ActivityButton $button) => $button->jsonSerialize(), $this->buttons)
        ];
    }

    public static function fromJson(array $json): self{
        return new self(
            $json["name"],
            ActivityType::fromJson($json["type"]),
            $json["url"] ?? null,
            $json["created_at"],
            $json["timestamps"]["start"] ?? null,
            $json["timestamps"]["end"] ?? null,
            $json["application_id"] ?? null,
            $json["details"] ?? null,
            $json["state"] ?? null,
            ($json["emoji"] ?? null) !== null ? Emoji::fromJson($json["emoji"]) : null,
            $json["party"]["id"] ?? null,
            $json["party"]["size"] ?? null,
            $json["party"]["max_size"] ?? null,
            $json["assets"]["large_image"] ?? null,
            $json["assets"]["large_text"] ?? null,
            $json["assets"]["small_image"] ?? null,
            $json["assets"]["small_text"] ?? null,
            $json["secrets"]["join"] ?? null,
            $json["secrets"]["spectate"] ?? null,
            $json["secrets"]["match"] ?? null,
            $json["instance"] ?? null,
            $json["flags"] ?? null,
            array_map(fn(array $button) => ActivityButton::fromJson($button), $json["buttons"] ?? [])
        );
    }
}