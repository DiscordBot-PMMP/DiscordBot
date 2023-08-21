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

namespace JaxkDev\DiscordBot\Models\Channels;

use JaxkDev\DiscordBot\Communication\BinarySerializable;
use JaxkDev\DiscordBot\Communication\BinaryStream;
use JaxkDev\DiscordBot\Models\Permissions\ChannelPermissions;
use JaxkDev\DiscordBot\Models\Permissions\RolePermissions;
use function JaxkDev\DiscordBot\Plugin\Utils\validDiscordSnowflake;

/**
 * @implements BinarySerializable<Overwrite>
 * @link https://discord.com/developers/docs/resources/channel#overwrite-object-overwrite-structure
 */
class Overwrite implements BinarySerializable{

    /** Role or user id */
    private string $id;

    private OverwriteType $type;

    private ChannelPermissions|RolePermissions $allow;
    private ChannelPermissions|RolePermissions $deny;

    public function __construct(string $id, OverwriteType $type, ChannelPermissions|RolePermissions $allow,
                                ChannelPermissions|RolePermissions $deny){
        $this->id = $id;
        $this->type = $type;
        $this->allow = $allow;
        $this->deny = $deny;
    }

    public function getId(): string{
        return $this->id;
    }

    public function setId(string $id): void{
        if(!validDiscordSnowflake($id)){
            throw new \AssertionError("Invalid ID provided.");
        }
        $this->id = $id;
    }

    public function getType(): OverwriteType{
        return $this->type;
    }

    public function setType(OverwriteType $type): void{
        $this->type = $type;
    }

    public function getAllow(): ChannelPermissions|RolePermissions{
        return $this->allow;
    }

    public function setAllow(ChannelPermissions|RolePermissions $allow): void{
        $this->allow = $allow;
    }

    public function getDeny(): ChannelPermissions|RolePermissions{
        return $this->deny;
    }

    public function setDeny(ChannelPermissions|RolePermissions $deny): void{
        $this->deny = $deny;
    }

    public function binarySerialize(): BinaryStream{
        $stream = new BinaryStream();
        $stream->putString($this->id);
        $stream->putByte($this->type->value);
        $stream->putSerializable($this->allow);
        $stream->putSerializable($this->deny);
        return $stream;
    }

    public static function fromBinary(BinaryStream $stream): BinarySerializable{
        return new self(
            $stream->getString(),
            ($t = OverwriteType::from($stream->getByte())),
            $stream->getSerializable($t === OverwriteType::ROLE ? RolePermissions::class : ChannelPermissions::class),
            $stream->getSerializable($t === OverwriteType::ROLE ? RolePermissions::class : ChannelPermissions::class)
        );
    }
}