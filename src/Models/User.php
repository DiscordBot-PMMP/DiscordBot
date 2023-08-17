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
use JaxkDev\DiscordBot\Plugin\Utils;
use function array_keys;
use function ctype_digit;
use function in_array;
use function sizeof;
use function str_contains;
use function strlen;

/**
 * @implements BinarySerializable<User>
 */
class User implements BinarySerializable{

    /**
     * @link https://discord.com/developers/docs/resources/user#user-object-user-flags
     * @var Array<string, int>
     */
    public const FLAGS = [
        "STAFF" => (1 << 0),                    // Discord Employee
        "PARTNER" => (1 << 1),                  // Partnered Server Owner
        "HYPESQUAD" => (1 << 2),                // HypeSquad Events Member
        "BUG_HUNTER_LEVEL_1" => (1 << 3),       // Bug Hunter Level 1
        "HYPESQUAD_ONLINE_HOUSE_1" => (1 << 6), // House Bravery Member
        "HYPESQUAD_ONLINE_HOUSE_2" => (1 << 7), // House Brilliance Member
        "HYPESQUAD_ONLINE_HOUSE_3" => (1 << 8), // House Balance Member
        "PREMIUM_EARLY_SUPPORTER" => (1 << 9),  // Early Nitro Supporter
        "TEAM_PSEUDO_USER" => (1 << 10),        // User is a team
        "BUG_HUNTER_LEVEL_2" => (1 << 14),      // Bug Hunter Level 2
        "VERIFIED_BOT" => (1 << 16),            // Verified Bot
        "VERIFIED_DEVELOPER" => (1 << 17),      // Early Verified Bot Developer
        "CERTIFIED_MODERATOR" => (1 << 18),     // Moderator Programs Alumni
        "BOT_HTTP_INTERACTIONS" => (1 << 19),   // Bot uses only HTTP interactions and is shown in the online member list
        "ACTIVE_DEVELOPER" => (1 << 22),        // User is an Active Developer
    ];

    private string $id;

    /**
     * The user's username, not unique across the platform
     * @link https://discord.com/developers/docs/resources/user#usernames-and-nicknames
     */
    private string $username;

    /** If user has chosen a unique username this will be 0000 */
    private string $discriminator;

    /** The user's display name, if it is set. For bots, this is the application name */
    private ?string $global_name;

    /** The user's avatar */
    private ?string $avatar;

    /** Whether the user belongs to an OAuth2 application */
    private ?bool $bot;

    /** Whether the user is an Official Discord System user (part of the urgent message system) */
    private ?bool $system;

    /** Whether the user has two factor enabled on their account */
    private ?bool $mfa_enabled;

    /** The user's banner URL */
    private ?string $banner;

    /** The user's banner color encoded as an integer representation of hexadecimal color code */
    private ?int $accent_colour;

    /**
     * The user's chosen language option
     * @link https://discord.com/developers/docs/reference#locales
     */
    private ?string $locale;

    /**
     * The flags on a user's account
     * @see User::FLAGS
     */
    private int $flags_bitwise;

    /** @var Array<string, bool> */
    private array $flags = [];

    /** The type of Nitro subscription on a user's account */
    private ?UserPremiumType $premium_type;

    /**
     * The public flags on a user's account
     * @see User::FLAGS
     */
    private int $public_flags_bitwise;

    /** @var Array<string, bool> */
    private array $public_flags = [];

    //No create method, this is read only.

    public function __construct(string $id, string $username, string $discriminator, ?string $global_name, ?string $avatar,
                                ?bool $bot, ?bool $system, ?bool $mfa_enabled, ?string $banner, ?int $accent_colour,
                                ?string $locale, int $flags_bitwise, ?UserPremiumType $premium_type, ?int $public_flags){
        $this->setId($id);
        $this->setUsername($username);
        $this->setDiscriminator($discriminator);
        $this->setGlobalName($global_name);
        $this->setAvatar($avatar);
        $this->setBot($bot);
        $this->setSystem($system);
        $this->setMfaEnabled($mfa_enabled);
        $this->setBanner($banner);
        $this->setAccentColour($accent_colour);
        $this->setLocale($locale);
        $this->setFlagsBitwise($flags_bitwise, false);
        $this->setPremiumType($premium_type);
        $this->setPublicFlagsBitwise($public_flags ?? 0, false);
    }

    public function getId(): string{
        return $this->id;
    }

    public function setId(string $id): void{
        if(!Utils::validDiscordSnowflake($id)){
            throw new \AssertionError("User ID '$id' is invalid.");
        }
        $this->id = $id;
    }

    public function getUniqueUsername(): string{
        return $this->username . ($this->discriminator !== "0000" ? ("#" . $this->discriminator) : "");
    }

    public function getUsername(): string{
        return $this->username;
    }

    public function setUsername(string $username): void{
        if(strlen($username) < 2 || strlen($username) > 32){
            throw new \AssertionError("Username '$username' is invalid, must be between 2 and 32 characters.");
        }
        if(in_array($username, ["everyone", "here"], true)){
            throw new \AssertionError("Username '$username' is invalid, cannot be 'everyone' or 'here'.");
        }
        if(str_contains($username, "@") || str_contains($username, "#") || str_contains($username, ":") || str_contains($username, "```")){
            throw new \AssertionError("Username '$username' is invalid, cannot contain '@', '#', ':' or '```'.");
        }
        $this->username = $username;
    }

    public function getDiscriminator(): string{
        return $this->discriminator;
    }

    public function setDiscriminator(string $discriminator): void{
        if(strlen($discriminator) !== 4 || !ctype_digit($discriminator)){
            throw new \AssertionError("Discriminator '$discriminator' is invalid, must be 4 digits.");
        }
        $this->discriminator = $discriminator;
    }

    public function getGlobalName(): ?string{
        return $this->global_name;
    }

    public function setGlobalName(?string $global_name): void{
        $this->global_name = $global_name;
    }

    public function getAvatarUrl(): string{
        if($this->avatar === null){
            $index = ((int)$this->discriminator === 0) ? (((int)$this->id >> 22) % 6) : ((int)$this->discriminator % 5);
            return "https://cdn.discordapp.com/embed/avatars/{$index}.png";
        }
        return "https://cdn.discordapp.com/avatars/{$this->id}/{$this->avatar}.png";
    }

    public function getAvatar(): ?string{
        return $this->avatar;
    }

    public function setAvatar(?string $avatar): void{
        $this->avatar = $avatar;
    }

    public function getBot(): ?bool{
        return $this->bot;
    }

    public function setBot(?bool $bot): void{
        $this->bot = $bot;
    }

    public function getSystem(): ?bool{
        return $this->system;
    }

    public function setSystem(?bool $system): void{
        $this->system = $system;
    }

    public function getMfaEnabled(): ?bool{
        return $this->mfa_enabled;
    }

    public function setMfaEnabled(?bool $mfa_enabled): void{
        $this->mfa_enabled = $mfa_enabled;
    }

    public function getBanner(): ?string{
        return $this->banner;
    }

    public function setBanner(?string $banner): void{
        $this->banner = $banner;
    }

    public function getAccentColour(): ?int{
        return $this->accent_colour;
    }

    public function setAccentColour(?int $accent_colour): void{
        if($accent_colour !== null && ($accent_colour < 0 || $accent_colour > 0xFFFFFF)){
            throw new \AssertionError("Accent colour '$accent_colour' is invalid, must be between 0 and 0xFFFFFF.");
        }
        $this->accent_colour = $accent_colour;
    }

    /** @see https://discord.com/developers/docs/reference#locales */
    public function getLocale(): ?string{
        return $this->locale;
    }

    /** @see https://discord.com/developers/docs/reference#locales */
    public function setLocale(?string $locale): void{
        $this->locale = $locale;
    }

    public function getFlagsBitwise(): int{
        return $this->flags_bitwise;
    }

    public function setFlagsBitwise(int $flags_bitwise, bool $recalculate = true): void{
        $this->flags_bitwise = $flags_bitwise;
        if($recalculate){
            $this->recalculateFlags();
        }
    }

    /**
     * Returns all the flags possible and their current state.
     * @return Array<string, bool>
     */
    public function getFlags(): array{
        if(sizeof($this->flags) === 0){
            $this->recalculateFlags();
        }
        return $this->flags;
    }

    public function getFlag(string $flag): ?bool{
        if(sizeof($this->flags) === 0){
            $this->recalculateFlags();
        }
        return $this->flags[$flag] ?? null;
    }

    public function setFlag(string $flag, bool $state = true): void{
        if(sizeof($this->flags) === 0){
            $this->recalculateFlags();
        }
        if(!in_array($flag, array_keys(self::FLAGS), true)){
            throw new \AssertionError("Invalid flag '{$flag}' for a 'User'");
        }

        if($this->flags[$flag] === $state) return;
        $this->flags[$flag] = $state;
        $this->flags_bitwise ^= self::FLAGS[$flag];
        return;
    }

    public function getPremiumType(): ?UserPremiumType{
        return $this->premium_type;
    }

    public function setPremiumType(?UserPremiumType $premium_type): void{
        $this->premium_type = $premium_type;
    }

    public function getPublicFlagsBitwise(): int{
        return $this->public_flags_bitwise;
    }

    public function setPublicFlagsBitwise(int $public_flags_bitwise, bool $recalculate = true): void{
        $this->public_flags_bitwise = $public_flags_bitwise;
        if($recalculate){
            $this->recalculatePublicFlags();
        }
    }

    /**
     * Returns all the public flags possible and their current state.
     * @return Array<string, bool>
     */
    public function getPublicFlags(): array{
        if(sizeof($this->public_flags) === 0){
            $this->recalculatePublicFlags();
        }
        return $this->public_flags;
    }

    public function getPublicFlag(string $public_flag): ?bool{
        if(sizeof($this->public_flags) === 0){
            $this->recalculatePublicFlags();
        }
        return $this->public_flags[$public_flag] ?? null;
    }

    public function setPublicFlag(string $public_flag, bool $state = true): void{
        if(sizeof($this->public_flags) === 0){
            $this->recalculatePublicFlags();
        }
        if(!in_array($public_flag, array_keys(self::FLAGS), true)){
            throw new \AssertionError("Invalid public flag '{$public_flag}' for a 'user'");
        }

        if($this->public_flags[$public_flag] === $state) return;
        $this->public_flags[$public_flag] = $state;
        $this->public_flags_bitwise ^= self::FLAGS[$public_flag];
        return;
    }

    /**
     * Using current flags_bitwise recalculate flags.
     * @internal
     */
    private function recalculateFlags(): void{
        $this->flags = [];
        foreach(self::FLAGS as $name => $v){
            $this->flags[$name] = (($this->flags_bitwise & $v) !== 0);
        }
    }

    /**
     * Using current public_flags_bitwise recalculate flags.
     * @internal
     */
    private function recalculatePublicFlags(): void{
        $this->public_flags = [];
        foreach(self::FLAGS as $name => $v){
            $this->public_flags[$name] = (($this->public_flags_bitwise & $v) !== 0);
        }
    }

    //----- Serialization -----//

    public function binarySerialize(): BinaryStream{
        $stream = new BinaryStream();
        $stream->putString($this->id);
        $stream->putString($this->username);
        $stream->putString($this->discriminator);
        $stream->putNullableString($this->global_name);
        $stream->putNullableString($this->avatar);
        $stream->putNullableBool($this->bot);
        $stream->putNullableBool($this->system);
        $stream->putNullableBool($this->mfa_enabled);
        $stream->putNullableString($this->banner);
        $stream->putNullableInt($this->accent_colour);
        $stream->putNullableString($this->locale);
        $stream->putInt($this->flags_bitwise);
        $stream->putNullableByte($this->premium_type?->value);
        $stream->putInt($this->public_flags_bitwise);
        return $stream;
    }

    public static function fromBinary(BinaryStream $stream): self{
        return new self(
            $stream->getString(),                                       // id
            $stream->getString(),                                       // username
            $stream->getString(),                                       // discriminator
            $stream->getNullableString(),                               // global_name
            $stream->getNullableString(),                               // avatar
            $stream->getNullableBool(),                                 // bot
            $stream->getNullableBool(),                                 // system
            $stream->getNullableBool(),                                 // mfa_enabled
            $stream->getNullableString(),                               // banner
            $stream->getNullableInt(),                                  // accent_colour
            $stream->getNullableString(),                               // locale
            $stream->getInt(),                                          // flags_bitwise
            UserPremiumType::tryFrom($stream->getNullableByte() ?? -1), // premium_type
            $stream->getInt()                                           // public_flags_bitwise
        );
    }
}