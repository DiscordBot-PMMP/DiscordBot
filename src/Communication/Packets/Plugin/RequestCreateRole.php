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

namespace JaxkDev\DiscordBot\Communication\Packets\Plugin;

use JaxkDev\DiscordBot\Communication\BinaryStream;
use JaxkDev\DiscordBot\Communication\Packets\Packet;
use JaxkDev\DiscordBot\Models\Permissions\RolePermissions;

final class RequestCreateRole extends Packet{

    public const SERIALIZE_ID = 44;

    private string $guild_id;

    private string $name;

    private RolePermissions $permissions;

    private int $colour;

    private bool $hoist;

    private ?string $icon_hash;

    private ?string $unicode_emoji;

    private bool $mentionable;

    private ?string $reason;

    public function __construct(string $guild_id, string $name, RolePermissions $permissions, int $colour, bool $hoist,
                                ?string $icon_hash, ?string $unicode_emoji, bool $mentionable, ?string $reason = null,
                                ?int $uid = null){
        parent::__construct($uid);
        $this->guild_id = $guild_id;
        $this->name = $name;
        $this->permissions = $permissions;
        $this->colour = $colour;
        $this->hoist = $hoist;
        $this->icon_hash = $icon_hash;
        $this->unicode_emoji = $unicode_emoji;
        $this->mentionable = $mentionable;
        $this->reason = $reason;
    }

    public function getGuildId(): string{
        return $this->guild_id;
    }

    public function getName(): string{
        return $this->name;
    }

    public function getPermissions(): RolePermissions{
        return $this->permissions;
    }

    public function getColour(): int{
        return $this->colour;
    }

    public function getHoist(): bool{
        return $this->hoist;
    }

    public function getIconHash(): ?string{
        return $this->icon_hash;
    }

    public function getUnicodeEmoji(): ?string{
        return $this->unicode_emoji;
    }

    public function getMentionable(): bool{
        return $this->mentionable;
    }

    public function getReason(): ?string{
        return $this->reason;
    }

    public function binarySerialize(): BinaryStream{
        $stream = new BinaryStream();
        $stream->putInt($this->getUID());
        $stream->putString($this->guild_id);
        $stream->putString($this->name);
        $stream->putSerializable($this->permissions);
        $stream->putInt($this->colour);
        $stream->putBool($this->hoist);
        $stream->putNullableString($this->icon_hash);
        $stream->putNullableString($this->unicode_emoji);
        $stream->putBool($this->mentionable);
        $stream->putNullableString($this->reason);
        return $stream;
    }

    public static function fromBinary(BinaryStream $stream): self{
        $uid = $stream->getInt();
        return new self(
            $stream->getString(),                             // guild_id
            $stream->getString(),                             // name
            $stream->getSerializable(RolePermissions::class), // permissions
            $stream->getInt(),                                // colour
            $stream->getBool(),                               // hoist
            $stream->getNullableString(),                     // icon_hash
            $stream->getNullableString(),                     // unicode_emoji
            $stream->getBool(),                               // mentionable
            $stream->getNullableString(),                     // reason
            $uid
        );
    }
}