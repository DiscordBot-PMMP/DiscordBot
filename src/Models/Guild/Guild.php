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

namespace JaxkDev\DiscordBot\Models\Guild;

use JaxkDev\DiscordBot\Models\Emoji;
use JaxkDev\DiscordBot\Plugin\Utils;

/** @link https://discord.com/developers/docs/resources/guild#guild-resource */
class Guild implements \JsonSerializable{

    /**
     * @link https://discord.com/developers/docs/resources/guild#guild-object-system-channel-flags
     * @var array<string, int>
     */
    public const SYSTEM_FLAGS = [
        "SUPPRESS_JOIN_NOTIFICATIONS" => (1 << 0),
        "SUPPRESS_PREMIUM_SUBSCRIPTIONS" => (1 << 1),
        "SUPPRESS_GUILD_REMINDER_NOTIFICATIONS" => (1 << 2),
        "SUPPRESS_JOIN_NOTIFICATION_REPLIES" => (1 << 3),
        "SUPPRESS_ROLE_SUBSCRIPTION_PURCHASE_NOTIFICATIONS" => (1 << 4),
        "SUPPRESS_ROLE_SUBSCRIPTION_PURCHASE_NOTIFICATION_REPLIES" => (1 << 5)
    ];

    /**
     * @link https://discord.com/developers/docs/resources/guild#guild-object-guild-features
     * @var string[]
     */
    public const FEATURES = [
        "ANIMATED_BANNER",
        "ANIMATED_ICON",
        "APPLICATION_COMMAND_PERMISSIONS_V2",
        "AUTO_MODERATION",
        "BANNER",
        "COMMUNITY",
        "CREATOR_MONETIZABLE_PROVISIONAL",
        "CREATOR_STORE_PAGE",
        "DEVELOPER_SUPPORT_SERVER",
        "DISCOVERABLE",
        "FEATURABLE",
        "INVITES_DISABLED",
        "INVITE_SPLASH",
        "MEMBER_VERIFICATION_GATE_ENABLED",
        "MORE_STICKERS",
        "NEWS",
        "PARTNERED",
        "PREVIEW_ENABLED",
        "RAID_ALERTS_DISABLED",
        "ROLE_ICONS",
        "ROLE_SUBSCRIPTIONS_AVAILABLE_FOR_PURCHASE",
        "ROLE_SUBSCRIPTIONS_ENABLED",
        "TICKETED_EVENTS_ENABLED",
        "VANITY_URL",
        "VERIFIED",
        "VIP_REGIONS",
        "WELCOME_SCREEN_ENABLED"
    ];

    /** Guild id */
    private string $id;

    /** Guild name (2-100 characters, excluding trailing and leading whitespace) */
    private string $name;

    /** Icon hash */
    private ?string $icon;

    /** Splash hash */
    private ?string $splash;

    /** Discovery splash hash; only present for guilds with the "DISCOVERABLE" feature */
    private ?string $discovery_splash;

    /** ID of owner */
    private ?string $owner_id;

    /** ID of afk channel */
    private ?string $afk_channel_id;

    /** AFK timeout in seconds. */
    private int $afk_timeout;

    /** True if the server widget is enabled */
    private ?bool $widget_enabled;

    /** The channel id that the widget will generate an invite to, or null if set to no invite */
    private ?string $widget_channel_id;

    /** Verification level required for the guild */
    private VerificationLevel $verification_level;

    /** Default message notifications level */
    private DefaultMessageNotificationLevel $default_message_notifications;

    /** Explicit content filter level */
    private ExplicitContentFilterLevel $explicit_content_filter;

    /**
     * Custom guild emojis
     * @var Emoji[]
     */
    private array $emojis;

    /**
     * @link https://discord.com/developers/docs/resources/guild#guild-object-guild-features
     * @var string[]
     */
    private array $features;

    /** Required MFA level for the guild */
    private MfaLevel $mfa_level;

    /** Application id of the guild creator if it is bot-created */
    private ?string $application_id;

    /** The id of the channel where guild notices such as welcome messages and boost events are posted */
    private ?string $system_channel_id;

    /** @see Guild::SYSTEM_FLAG_SUPPRESS_* constants */
    private int $system_channel_flags;

    /** The id of the channel where Community guilds can display rules and/or guidelines */
    private ?string $rules_channel_id;

    /** The maximum number of presences for the guild (null is always returned, apart from the largest of guilds) */
    private ?int $max_presences;

    /** The maximum number of members for the guild */
    private ?int $max_members;

    /** The vanity url code for the guild */
    private ?string $vanity_url_code;

    /** The description of a guild */
    private ?string $description;

    /** Banner hash */
    private ?string $banner;

    /** Premium tier (Server Boost level) */
    private PremiumTier $premium_tier;

    /** The number of boosts this guild currently has */
    private ?int $premium_subscription_count;

    /** The preferred locale of a Community guild; used in server discovery and notices from Discord; defaults to "en-US" */
    private string $preferred_locale;

    /** The id of the channel where admins and moderators of Community guilds receive notices from Discord */
    private ?string $public_updates_channel_id;

    /** The maximum amount of users in a video channel */
    private ?int $max_video_channel_users;

    /** The maximum amount of users in a stage video channel */
    private ?int $max_stage_video_channel_users;

    /** Approximate number of members in this guild, returned from the GET /guild/<id> endpoint when with_counts is true */
    //private ?int $approximate_member_count;

    /** Approximate number of non-offline members in this guild, returned from the GET /guild/<id> endpoint when with_counts is true */
    //private ?int $approximate_presence_count;

    /** The welcome screen of a Community guild, shown to new members, returned in an Invite's guild object */
    //private $welcome_screen;

    /** The NSFW level of the guild */
    private NsfwLevel $nsfw_level;

    /*
     * Custom guild stickers
     * @var Sticker[]
     */
    //private array $stickers; TODO Sticker class

    /** Whether the guild has the boost progress bar enabled */
    private bool $premium_progress_bar_enabled;

    /** The id of the channel where admins and moderators of Community guilds receive safety alerts from Discord */
    private ?string $safety_alerts_channel_id;

    //No create method. This is a read-update-only object, guilds cannot be created by my API.

    //Only ModelConverter should create this object, so we don't need to pad it out with defaults and make it look nice.
    public function __construct(string                     $id, string $name, ?string $icon, ?string $splash, ?string $discovery_splash,
                                ?string                    $owner_id, ?string $afk_channel_id, int $afk_timeout, ?bool $widget_enabled, ?string $widget_channel_id,
                                VerificationLevel          $verification_level, DefaultMessageNotificationLevel $default_message_notifications,
                                ExplicitContentFilterLevel $explicit_content_filter, array $emojis, array $features, MfaLevel $mfa_level,
                                ?string                    $application_id, ?string $system_channel_id, int $system_channel_flags, ?string $rules_channel_id,
                                ?int                       $max_presences, ?int $max_members, ?string $vanity_url_code, ?string $description, ?string $banner,
                                PremiumTier                $premium_tier, ?int $premium_subscription_count, string $preferred_locale,
                                ?string                    $public_updates_channel_id, ?int $max_video_channel_users, ?int $max_stage_video_channel_users,
                                NsfwLevel                  $nsfw_level, /*array $stickers,*/ bool $premium_progress_bar_enabled, ?string $safety_alerts_channel_id
    ){
        $this->setId($id);
        $this->setName($name);
        $this->setIcon($icon);
        $this->setSplash($splash);
        $this->setDiscoverySplash($discovery_splash);
        $this->setOwnerId($owner_id);
        $this->setAfkChannelId($afk_channel_id);
        $this->setAfkTimeout($afk_timeout);
        $this->setWidgetEnabled($widget_enabled);
        $this->setWidgetChannelId($widget_channel_id);
        $this->setVerificationLevel($verification_level);
        $this->setDefaultMessageNotifications($default_message_notifications);
        $this->setExplicitContentFilter($explicit_content_filter);
        $this->setEmojis($emojis);
        $this->setFeatures($features);
        $this->setMfaLevel($mfa_level);
        $this->setApplicationId($application_id);
        $this->setSystemChannelId($system_channel_id);
        $this->setSystemChannelFlags($system_channel_flags);
        $this->setRulesChannelId($rules_channel_id);
        $this->setMaxPresences($max_presences);
        $this->setMaxMembers($max_members);
        $this->setVanityUrlCode($vanity_url_code);
        $this->setDescription($description);
        $this->setBanner($banner);
        $this->setPremiumTier($premium_tier);
        $this->setPremiumSubscriptionCount($premium_subscription_count);
        $this->setPreferredLocale($preferred_locale);
        $this->setPublicUpdatesChannelId($public_updates_channel_id);
        $this->setMaxVideoChannelUsers($max_video_channel_users);
        $this->setMaxStageVideoChannelUsers($max_stage_video_channel_users);
        $this->setNsfwLevel($nsfw_level);
        //$this->setStickers($stickers);
        $this->setPremiumProgressBarEnabled($premium_progress_bar_enabled);
        $this->setSafetyAlertsChannelId($safety_alerts_channel_id);
    }

    public function getId(): string{
        return $this->id;
    }

    public function setId(string $id): void{
        if(!Utils::validDiscordSnowflake($id)){
            throw new \AssertionError("ID '$id' is invalid.");
        }
        $this->id = $id;
    }

    public function getName(): string{
        return $this->name;
    }

    public function setName(string $name): void{
        $this->name = $name;
    }

    public function getIcon(): ?string{
        return $this->icon;
    }

    public function setIcon(?string $icon): void{
        $this->icon = $icon;
    }

    public function getSplash(): ?string{
        return $this->splash;
    }

    public function setSplash(?string $splash): void{
        $this->splash = $splash;
    }

    public function getDiscoverySplash(): ?string{
        return $this->discovery_splash;
    }

    public function setDiscoverySplash(?string $discovery_splash): void{
        $this->discovery_splash = $discovery_splash;
    }

    public function getOwnerId(): ?string{
        return $this->owner_id;
    }

    public function setOwnerId(?string $owner_id): void{
        if($owner_id !== null && !Utils::validDiscordSnowflake($owner_id)){
            throw new \AssertionError("Owner ID '$owner_id' is invalid.");
        }
        $this->owner_id = $owner_id;
    }

    public function getAfkChannelId(): ?string{
        return $this->afk_channel_id;
    }

    public function setAfkChannelId(?string $afk_channel_id): void{
        if($afk_channel_id !== null && !Utils::validDiscordSnowflake($afk_channel_id)){
            throw new \AssertionError("AFK channel ID '$afk_channel_id' is invalid.");
        }
        $this->afk_channel_id = $afk_channel_id;
    }

    public function getAfkTimeout(): int{
        return $this->afk_timeout;
    }

    public function setAfkTimeout(int $afk_timeout): void{
        if($afk_timeout < 0){
            throw new \AssertionError("AFK timeout '$afk_timeout' is invalid.");
        }
        $this->afk_timeout = $afk_timeout;
    }

    public function getWidgetEnabled(): ?bool{
        return $this->widget_enabled;
    }

    public function setWidgetEnabled(?bool $widget_enabled): void{
        $this->widget_enabled = $widget_enabled;
    }

    public function getWidgetChannelId(): ?string{
        return $this->widget_channel_id;
    }

    public function setWidgetChannelId(?string $widget_channel_id): void{
        if($widget_channel_id !== null && !Utils::validDiscordSnowflake($widget_channel_id)){
            throw new \AssertionError("Widget channel ID '$widget_channel_id' is invalid.");
        }
        $this->widget_channel_id = $widget_channel_id;
    }

    /** @return Emoji[] */
    public function getEmojis(): array{
        return $this->emojis;
    }

    /** @param Emoji[] $emojis */
    public function setEmojis(array $emojis): void{
        foreach($emojis as $emoji){
            if(!($emoji instanceof Emoji)){
                throw new \TypeError("All emojis must be instances of Emoji.");
            }
        }
        $this->emojis = $emojis;
    }

    public function getVerificationLevel(): VerificationLevel{
        return $this->verification_level;
    }

    public function setVerificationLevel(VerificationLevel $verification_level): void{
        $this->verification_level = $verification_level;
    }

    public function getDefaultMessageNotifications(): DefaultMessageNotificationLevel{
        return $this->default_message_notifications;
    }

    public function setDefaultMessageNotifications(DefaultMessageNotificationLevel $default_message_notifications): void{
        $this->default_message_notifications = $default_message_notifications;
    }

    public function getExplicitContentFilter(): ExplicitContentFilterLevel{
        return $this->explicit_content_filter;
    }

    public function setExplicitContentFilter(ExplicitContentFilterLevel $explicit_content_filter): void{
        $this->explicit_content_filter = $explicit_content_filter;
    }

    /**
     * @return Emoji[]
     */
    /*public function getEmojis(): array{
        return $this->emojis;
    }*/

    /**
     * @param Emoji[] $emojis
     */
    /*public function setEmojis(array $emojis): void{
        $this->emojis = $emojis;
    }*/

    /** @return string[] */
    public function getFeatures(): array{
        return $this->features;
    }

    /** @param string[] $features */
    public function setFeatures(array $features): void{
        foreach($features as $feature){
            if(!in_array($feature, self::FEATURES, true)){
                throw new \AssertionError("Feature '$feature' is invalid.");
            }
        }
        $this->features = $features;
    }

    public function getMfaLevel(): MfaLevel{
        return $this->mfa_level;
    }

    public function setMfaLevel(MfaLevel $mfa_level): void{
        $this->mfa_level = $mfa_level;
    }

    public function getApplicationId(): ?string{
        return $this->application_id;
    }

    public function setApplicationId(?string $application_id): void{
        if($application_id !== null && !Utils::validDiscordSnowflake($application_id)){
            throw new \AssertionError("Application ID '$application_id' is invalid.");
        }
        $this->application_id = $application_id;
    }

    public function getSystemChannelId(): ?string{
        return $this->system_channel_id;
    }

    public function setSystemChannelId(?string $system_channel_id): void{
        if($system_channel_id !== null && !Utils::validDiscordSnowflake($system_channel_id)){
            throw new \AssertionError("System channel ID '$system_channel_id' is invalid.");
        }
        $this->system_channel_id = $system_channel_id;
    }

    public function getSystemChannelFlags(): int{
        //TODO Decide on this
        return $this->system_channel_flags;
    }

    public function setSystemChannelFlags(int $system_channel_flags): void{
        //TODO Decide on this
        $this->system_channel_flags = $system_channel_flags;
    }

    public function getRulesChannelId(): ?string{
        return $this->rules_channel_id;
    }

    public function setRulesChannelId(?string $rules_channel_id): void{
        if($rules_channel_id !== null && !Utils::validDiscordSnowflake($rules_channel_id)){
            throw new \AssertionError("Rules channel ID '$rules_channel_id' is invalid.");
        }
        $this->rules_channel_id = $rules_channel_id;
    }

    public function getMaxPresences(): ?int{
        return $this->max_presences;
    }

    public function setMaxPresences(?int $max_presences): void{
        if($max_presences !== null && $max_presences < 0){
            throw new \AssertionError("Max presences '$max_presences' is invalid.");
        }
        $this->max_presences = $max_presences;
    }

    public function getMaxMembers(): ?int{
        return $this->max_members;
    }

    public function setMaxMembers(?int $max_members): void{
        if($max_members !== null and $max_members < 0){
            throw new \AssertionError("Max members '$max_members' is invalid.");
        }
        $this->max_members = $max_members;
    }

    public function getVanityUrlCode(): ?string{
        return $this->vanity_url_code;
    }

    public function setVanityUrlCode(?string $vanity_url_code): void{
        $this->vanity_url_code = $vanity_url_code;
    }

    public function getDescription(): ?string{
        return $this->description;
    }

    public function setDescription(?string $description): void{
        $this->description = $description;
    }

    public function getBanner(): ?string{
        return $this->banner;
    }

    public function setBanner(?string $banner): void{
        $this->banner = $banner;
    }

    public function getPremiumTier(): PremiumTier{
        return $this->premium_tier;
    }

    public function setPremiumTier(PremiumTier $premium_tier): void{
        $this->premium_tier = $premium_tier;
    }

    public function getPremiumSubscriptionCount(): ?int{
        return $this->premium_subscription_count;
    }

    public function setPremiumSubscriptionCount(?int $premium_subscription_count): void{
        if($premium_subscription_count !== null && $premium_subscription_count < 0){
            throw new \AssertionError("Premium subscription count '$premium_subscription_count' is invalid.");
        }
        $this->premium_subscription_count = $premium_subscription_count;
    }

    public function getPreferredLocale(): string{
        return $this->preferred_locale;
    }

    public function setPreferredLocale(string $preferred_locale): void{
        $this->preferred_locale = $preferred_locale;
    }

    public function getPublicUpdatesChannelId(): ?string{
        return $this->public_updates_channel_id;
    }

    public function setPublicUpdatesChannelId(?string $public_updates_channel_id): void{
        if($public_updates_channel_id !== null && !Utils::validDiscordSnowflake($public_updates_channel_id)){
            throw new \AssertionError("Public updates channel ID '$public_updates_channel_id' is invalid.");
        }
        $this->public_updates_channel_id = $public_updates_channel_id;
    }

    public function getMaxVideoChannelUsers(): ?int{
        return $this->max_video_channel_users;
    }

    public function setMaxVideoChannelUsers(?int $max_video_channel_users): void{
        if($max_video_channel_users !== null && $max_video_channel_users < 0){
            throw new \AssertionError("Max video channel users '$max_video_channel_users' is invalid.");
        }
        $this->max_video_channel_users = $max_video_channel_users;
    }

    public function getMaxStageVideoChannelUsers(): ?int{
        return $this->max_stage_video_channel_users;
    }

    public function setMaxStageVideoChannelUsers(?int $max_stage_video_channel_users): void{
        if($max_stage_video_channel_users !== null && $max_stage_video_channel_users < 0){
            throw new \AssertionError("Max stage video channel users '$max_stage_video_channel_users' is invalid.");
        }
        $this->max_stage_video_channel_users = $max_stage_video_channel_users;
    }

    public function getNsfwLevel(): NsfwLevel{
        return $this->nsfw_level;
    }

    public function setNsfwLevel(NsfwLevel $nsfw_level): void{
        $this->nsfw_level = $nsfw_level;
    }

    public function getPremiumProgressBarEnabled(): bool{
        return $this->premium_progress_bar_enabled;
    }

    public function setPremiumProgressBarEnabled(bool $premium_progress_bar_enabled): void{
        $this->premium_progress_bar_enabled = $premium_progress_bar_enabled;
    }

    public function getSafetyAlertsChannelId(): ?string{
        return $this->safety_alerts_channel_id;
    }

    public function setSafetyAlertsChannelId(?string $safety_alerts_channel_id): void{
        if($safety_alerts_channel_id !== null && !Utils::validDiscordSnowflake($safety_alerts_channel_id)){
            throw new \AssertionError("Safety alerts channel ID '$safety_alerts_channel_id' is invalid.");
        }
        $this->safety_alerts_channel_id = $safety_alerts_channel_id;
    }

    //----- Serialization -----//

    public function jsonSerialize(): array{
        return [
            'id' => $this->id,
            'name' => $this->name,
            'icon' => $this->icon,
            'splash' => $this->splash,
            'discovery_splash' => $this->discovery_splash,
            'owner_id' => $this->owner_id,
            'afk_channel_id' => $this->afk_channel_id,
            'afk_timeout' => $this->afk_timeout,
            'widget_enabled' => $this->widget_enabled,
            'widget_channel_id' => $this->widget_channel_id,
            'verification_level' => $this->verification_level->jsonSerialize(),
            'default_message_notifications' => $this->default_message_notifications->jsonSerialize(),
            'explicit_content_filter' => $this->explicit_content_filter->jsonSerialize(),
            'emojis' => array_map(fn(Emoji $e) => $e->jsonSerialize(), $this->emojis),
            'features' => $this->features,
            'mfa_level' => $this->mfa_level->jsonSerialize(),
            'application_id' => $this->application_id,
            'system_channel_id' => $this->system_channel_id,
            'system_channel_flags' => $this->system_channel_flags,
            'rules_channel_id' => $this->rules_channel_id,
            'max_presences' => $this->max_presences,
            'max_members' => $this->max_members,
            'vanity_url_code' => $this->vanity_url_code,
            'description' => $this->description,
            'banner' => $this->banner,
            'premium_tier' => $this->premium_tier->jsonSerialize(),
            'premium_subscription_count' => $this->premium_subscription_count,
            'preferred_locale' => $this->preferred_locale,
            'public_updates_channel_id' => $this->public_updates_channel_id,
            'max_video_channel_users' => $this->max_video_channel_users,
            'max_stage_video_channel_users' => $this->max_stage_video_channel_users,
            'nsfw_level' => $this->nsfw_level->jsonSerialize(),
            'premium_progress_bar_enabled' => $this->premium_progress_bar_enabled,
            'safety_alerts_channel_id' => $this->safety_alerts_channel_id
        ];
    }

    public static function fromJson(array $data): self{
        return new Guild(
            $data['id'],
            $data['name'],
            $data['icon'] ?? null,
            $data['splash'] ?? null,
            $data['discovery_splash'] ?? null,
            $data['owner_id'] ?? null,
            $data['afk_channel_id'] ?? null,
            $data['afk_timeout'],
            $data['widget_enabled'] ?? null,
            $data['widget_channel_id'] ?? null,
            VerificationLevel::fromJson($data['verification_level']),
            DefaultMessageNotificationLevel::fromJson($data['default_message_notifications']),
            ExplicitContentFilterLevel::fromJson($data['explicit_content_filter']),
            array_map(fn(array $e) => Emoji::fromJson($e), $data['emojis']),
            $data['features'],
            MfaLevel::fromJson($data['mfa_level']),
            $data['application_id'] ?? null,
            $data['system_channel_id'] ?? null,
            $data['system_channel_flags'],
            $data['rules_channel_id'] ?? null,
            $data['max_presences'] ?? null,
            $data['max_members'] ?? null,
            $data['vanity_url_code'] ?? null,
            $data['description'] ?? null,
            $data['banner'] ?? null,
            PremiumTier::fromJson($data['premium_tier']),
            $data['premium_subscription_count'] ?? null,
            $data['preferred_locale'],
            $data['public_updates_channel_id'] ?? null,
            $data['max_video_channel_users'] ?? null,
            $data['max_stage_video_channel_users'] ?? null,
            NsfwLevel::fromJson($data['nsfw_level']),
            $data['premium_progress_bar_enabled'],
            $data['safety_alerts_channel_id'] ?? null
        );
    }
}