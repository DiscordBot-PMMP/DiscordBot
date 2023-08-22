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

namespace JaxkDev\DiscordBot\Models\Interactions\Commands;

use JaxkDev\DiscordBot\Communication\BinarySerializable;
use JaxkDev\DiscordBot\Communication\BinaryStream;
use JaxkDev\DiscordBot\Models\Permissions\RolePermissions;

use JaxkDev\DiscordBot\Plugin\Utils;
use function strlen;

/**
 * @implements BinarySerializable<Command>
 * @link https://discord.com/developers/docs/interactions/application-commands#application-command-object-application-command-structure
 */
final class Command implements BinarySerializable{

    /** Unique id of the command */
    private string $id;

    /** Type of command, defaults to CHAT_INPUT */
    private CommandType $type;

    /** Unique id of the parent application */
    private string $application_id;

    /** Guild ID of the command, if not global */
    private ?string $guild_id;

    /** 1-32 character name */
    private string $name;

    /**
     * Localization dictionary for name field. Values follow the same restrictions as name
     * Key is the locale code, value is the localized name
     *
     * @link https://discord.com/developers/docs/reference#locales
     * @var array<string, string>|null
     */
    private ?array $name_localizations;

    /** 1-100 character description for CHAT_INPUT commands, empty string for USER and MESSAGE commands */
    private string $description;

    /**
     * Localization dictionary for description field. Values follow the same restrictions as description
     * Key is the locale code, value is the localized description
     *
     * @link https://discord.com/developers/docs/reference#locales
     * @var array<string, string>|null
     */
    private ?array $description_localizations;

    /**
     * The parameters for the command (Only CHAT_INPUT), max 25.
     * @var CommandOption[]|null
     */
    private ?array $options;

    private ?RolePermissions $default_member_permissions;

    /** Indicates whether the command is available in DMs with the app, only for globally-scoped commands. By default, commands are visible. */
    private ?bool $dm_permission;

    /** Indicates whether the command is age-restricted, defaults to false */
    private ?bool $nsfw;

    /** Auto-incrementing version identifier updated during substantial record changes */
    private string $version;

    /**
     * @param array<string, string>|null $name_localizations
     * @param array<string, string>|null $description_localizations
     * @param CommandOption[]|null       $options
     */
    public function __construct(string $id, CommandType $type, string $application_id, ?string $guild_id, string $name,
                                ?array $name_localizations, string $description, ?array $description_localizations,
                                ?array $options, ?RolePermissions $default_member_permissions, ?bool $dm_permission,
                                ?bool $nsfw, string $version){
        $this->setId($id);
        $this->setType($type);
        $this->setApplicationId($application_id);
        $this->setGuildId($guild_id);
        $this->setName($name);
        $this->setNameLocalizations($name_localizations);
        $this->setDescription($description);
        $this->setDescriptionLocalizations($description_localizations);
        $this->setOptions($options);
        $this->setDefaultMemberPermissions($default_member_permissions);
        $this->setDmPermission($dm_permission);
        $this->setNsfw($nsfw);
        $this->setVersion($version);
    }

    public function getId(): string{
        return $this->id;
    }

    public function setId(string $id): void{
        if(!Utils::validDiscordSnowflake($id)){
            throw new \AssertionError("Command ID '{$id}' is invalid.");
        }
        $this->id = $id;
    }

    public function getType(): CommandType{
        return $this->type;
    }

    public function setType(CommandType $type): void{
        $this->type = $type;
    }

    public function getApplicationId(): string{
        return $this->application_id;
    }

    public function setApplicationId(string $application_id): void{
        if(!Utils::validDiscordSnowflake($application_id)){
            throw new \AssertionError("Application ID '{$application_id}' is invalid.");
        }
        $this->application_id = $application_id;
    }

    public function getGuildId(): ?string{
        return $this->guild_id;
    }

    public function setGuildId(?string $guild_id): void{
        if($guild_id !== null && !Utils::validDiscordSnowflake($guild_id)){
            throw new \AssertionError("Guild ID '{$guild_id}' is invalid.");
        }
        $this->guild_id = $guild_id;
    }

    public function getName(): string{
        return $this->name;
    }

    public function setName(string $name): void{
        if(strlen($name) < 1 || strlen($name) > 32){
            throw new \AssertionError("Name must be between 1 and 32 characters.");
        }
        $this->name = $name;
    }

    /** @return array<string, string>|null */
    public function getNameLocalizations(): ?array{
        return $this->name_localizations;
    }

    /** @param array<string, string>|null $name_localizations */
    public function setNameLocalizations(?array $name_localizations): void{
        $this->name_localizations = $name_localizations;
    }

    public function getDescription(): string{
        return $this->description;
    }

    public function setDescription(string $description): void{
        if(strlen($description) > 100){
            throw new \AssertionError("Description must be between 0 and 100 characters.");
        }
        $this->description = $description;
    }

    /** @return array<string, string>|null */
    public function getDescriptionLocalizations(): ?array{
        return $this->description_localizations;
    }

    /** @param array<string, string>|null $description_localizations */
    public function setDescriptionLocalizations(?array $description_localizations): void{
        $this->description_localizations = $description_localizations;
    }

    /** @return CommandOption[]|null */
    public function getOptions(): ?array{
        return $this->options;
    }

    /** @param CommandOption[]|null $options */
    public function setOptions(?array $options): void{
        $this->options = $options;
    }

    public function getDefaultMemberPermissions(): ?RolePermissions{
        return $this->default_member_permissions;
    }

    public function setDefaultMemberPermissions(?RolePermissions $default_member_permissions): void{
        $this->default_member_permissions = $default_member_permissions;
    }

    public function getDmPermission(): ?bool{
        return $this->dm_permission;
    }

    public function setDmPermission(?bool $dm_permission): void{
        $this->dm_permission = $dm_permission;
    }

    public function getNsfw(): ?bool{
        return $this->nsfw;
    }

    public function setNsfw(?bool $nsfw): void{
        $this->nsfw = $nsfw;
    }

    public function getVersion(): string{
        return $this->version;
    }

    public function setVersion(string $version): void{
        if(!Utils::validDiscordSnowflake($version)){
            //Yes version is a snowflake...
            throw new \AssertionError("Version '{$version}' is invalid.");
        }
        $this->version = $version;
    }

    public function binarySerialize(): BinaryStream{
        $stream = new BinaryStream();
        $stream->putString($this->id);
        $stream->putByte($this->type->value);
        $stream->putString($this->application_id);
        $stream->putNullableString($this->guild_id);
        $stream->putString($this->name);
        $stream->putNullableLocalizationDictionary($this->name_localizations);
        $stream->putString($this->description);
        $stream->putNullableLocalizationDictionary($this->description_localizations);
        $stream->putNullableSerializableArray($this->options);
        $stream->putNullableSerializable($this->default_member_permissions);
        $stream->putNullableBool($this->dm_permission);
        $stream->putNullableBool($this->nsfw);
        $stream->putString($this->version);
        return $stream;
    }

    public static function fromBinary(BinaryStream $stream): self{
        return new self(
            $stream->getString(),
            CommandType::from($stream->getByte()),
            $stream->getString(),
            $stream->getNullableString(),
            $stream->getString(),
            $stream->getNullableLocalizationDictionary(),
            $stream->getString(),
            $stream->getNullableLocalizationDictionary(),
            $stream->getNullableSerializableArray(CommandOption::class),
            $stream->getNullableSerializable(RolePermissions::class),
            $stream->getNullableBool(),
            $stream->getNullableBool(),
            $stream->getString()
        );
    }
}