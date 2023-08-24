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

use JaxkDev\DiscordBot\Communication\BinarySerializable;
use JaxkDev\DiscordBot\Communication\BinaryStream;
use JaxkDev\DiscordBot\Models\Permissions\RolePermissions;
use JaxkDev\DiscordBot\Plugin\Utils;

/**
 * @implements BinarySerializable<Role>
 * @link https://discord.com/developers/docs/topics/permissions#role-object
 */
final class Role implements BinarySerializable{

    public const SERIALIZE_ID = 8;

    /** Role ID, never null unless you are sending a new createRole via API. */
    private ?string $id;

    /** The guild this role is part of, used for internal mapping. */
    private string $guild_id;

    /** Role name */
    private string $name;

    /** Integer representation of hexadecimal color code  */
    private int $colour;

    /** If this role is pinned in the user listing */
    private bool $hoist;

    /** Role icon */
    private ?string $icon;

    /** Role unicode emoji */
    private ?string $unicode_emoji;

    /** Position of this role */
    private int $position;

    /** Permissions */
    private RolePermissions $permissions;

    /** Whether this role is managed by an integration */
    private bool $managed;

    /** Whether this role is mentionable */
    private bool $mentionable;

    /** The tags this role has */
    private ?RoleTags $tags;

    /**
     * @internal See API::createRole()
     * @see API::createRole()
     */
    public function __construct(?string $id, string $guild_id, string $name, int $colour, bool $hoist, ?string $icon,
                                ?string $unicode_emoji, int $position, RolePermissions $permissions, bool $managed,
                                bool $mentionable, ?RoleTags $tags){
        $this->setId($id);
        $this->setGuildId($guild_id);
        $this->setName($name);
        $this->setColour($colour);
        $this->setHoist($hoist);
        $this->setIcon($icon);
        $this->setUnicodeEmoji($unicode_emoji);
        $this->setPosition($position);
        $this->setPermissions($permissions);
        $this->setManaged($managed);
        $this->setMentionable($mentionable);
        $this->setTags($tags);
    }

    public function getId(): ?string{
        return $this->id;
    }

    public function setId(?string $id): void{
        if($id !== null && !Utils::validDiscordSnowflake($id)){
            throw new \AssertionError("Role ID '$id' is invalid.");
        }
        $this->id = $id;
    }

    public function getGuildId(): string{
        return $this->guild_id;
    }

    public function setGuildId(string $guild_id): void{
        if(!Utils::validDiscordSnowflake($guild_id)){
            throw new \AssertionError("Guild ID '$guild_id' is invalid.");
        }
        $this->guild_id = $guild_id;
    }

    public function getName(): string{
        return $this->name;
    }

    public function setName(string $name): void{
        $this->name = $name;
    }

    public function getColour(): int{
        return $this->colour;
    }

    /** @param int $colour Hex [0x000000 - 0xFFFFFF] */
    public function setColour(int $colour): void{
        if($colour < 0 || $colour > 0xFFFFFF){
            throw new \AssertionError("Colour '$colour' is outside the bounds 0x000000-0xFFFFFF.");
        }
        $this->colour = $colour;
    }

    public function getHoist(): bool{
        return $this->hoist;
    }

    public function setHoist(bool $hoist): void{
        $this->hoist = $hoist;
    }

    public function getIconUrl(): ?string{
        return ($this->id === null || $this->icon === null) ? null : "https://cdn.discordapp.com/role-icons/{$this->id}/{$this->icon}.png";
    }

    public function getIcon(): ?string{
        return $this->icon;
    }

    public function setIcon(?string $icon): void{
        $this->icon = $icon;
    }

    public function getUnicodeEmoji(): ?string{
        return $this->unicode_emoji;
    }

    public function setUnicodeEmoji(?string $unicode_emoji): void{
        $this->unicode_emoji = $unicode_emoji;
    }

    public function getPosition(): int{
        return $this->position;
    }

    public function setPosition(int $position): void{
        $this->position = $position;
    }

    public function getPermissions(): RolePermissions{
        return $this->permissions;
    }

    public function setPermissions(RolePermissions $permissions): void{
        $this->permissions = $permissions;
    }

    public function getManaged(): bool{
        return $this->managed;
    }

    public function setManaged(bool $managed): void{
        $this->managed = $managed;
    }

    public function getMentionable(): bool{
        return $this->mentionable;
    }

    public function setMentionable(bool $mentionable): void{
        $this->mentionable = $mentionable;
    }

    public function getTags(): ?RoleTags{
        return $this->tags;
    }

    public function setTags(?RoleTags $tags): void{
        $this->tags = $tags;
    }

    //----- Serialization -----//

    public function binarySerialize(): BinaryStream{
        $stream = new BinaryStream();
        $stream->putNullableString($this->id);
        $stream->putString($this->guild_id);
        $stream->putString($this->name);
        $stream->putInt($this->colour);
        $stream->putBool($this->hoist);
        $stream->putNullableString($this->icon);
        $stream->putNullableString($this->unicode_emoji);
        $stream->putInt($this->position);
        $stream->putSerializable($this->permissions);
        $stream->putBool($this->managed);
        $stream->putBool($this->mentionable);
        $stream->putNullableSerializable($this->tags);
        return $stream;
    }

    public static function fromBinary(BinaryStream $stream): self{
        return new self(
            $stream->getNullableString(),                       // id
            $stream->getString(),                               // guild_id
            $stream->getString(),                               // name
            $stream->getInt(),                                  // colour
            $stream->getBool(),                                 // hoist
            $stream->getNullableString(),                       // icon
            $stream->getNullableString(),                       // unicode_emoji
            $stream->getInt(),                                  // position
            $stream->getSerializable(RolePermissions::class),   // permissions
            $stream->getBool(),                                 // managed
            $stream->getBool(),                                 // mentionable
            $stream->getNullableSerializable(RoleTags::class)   // tags
        );
    }
}