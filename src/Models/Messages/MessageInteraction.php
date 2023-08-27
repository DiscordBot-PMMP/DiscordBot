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

namespace JaxkDev\DiscordBot\Models\Messages;

use JaxkDev\DiscordBot\Communication\BinarySerializable;
use JaxkDev\DiscordBot\Communication\BinaryStream;
use JaxkDev\DiscordBot\Models\Interactions\InteractionType;
use JaxkDev\DiscordBot\Models\Member;
use JaxkDev\DiscordBot\Models\User;
use JaxkDev\DiscordBot\Plugin\Utils;

/**
 * Represents a message interaction.\
 * "This is sent on the message object when the message is a response to an Interaction without an existing message."\
 * "This means responses to Message Components do not include this property, instead including a message reference
 *  object as components always exist on preexisting messages."
 *
 * @link https://discord.com/developers/docs/interactions/receiving-and-responding#message-interaction-object
 * @implements BinarySerializable<MessageInteraction>
 */
final class MessageInteraction implements BinarySerializable{

    /** ID of the interaction */
    private string $id;

    /** Type of interaction */
    private InteractionType $type;

    /** Name of the application command, including subcommands and subcommand groups */
    private string $name;

    /** User who invoked the interaction */
    private User $user;

    private ?Member $member;

    public function __construct(string $id, InteractionType $type, string $name, User $user, ?Member $member = null){
        $this->setId($id);
        $this->setType($type);
        $this->setName($name);
        $this->setUser($user);
        $this->setMember($member);
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

    public function getType(): InteractionType{
        return $this->type;
    }

    public function setType(InteractionType $type): void{
        $this->type = $type;
    }

    public function getName(): string{
        return $this->name;
    }

    public function setName(string $name): void{
        $this->name = $name;
    }

    public function getUser(): User{
        return $this->user;
    }

    public function setUser(User $user): void{
        $this->user = $user;
    }

    public function getMember(): ?Member{
        return $this->member;
    }

    public function setMember(?Member $member): void{
        $this->member = $member;
    }

    public function binarySerialize(): BinaryStream{
        $stream = new BinaryStream();
        $stream->putString($this->id);
        $stream->putByte($this->type->value);
        $stream->putString($this->name);
        $stream->putSerializable($this->user);
        $stream->putNullableSerializable($this->member);
        return $stream;
    }

    public static function fromBinary(BinaryStream $stream): self{
        return new self(
            $stream->getString(),                             // id
            InteractionType::from($stream->getByte()),        // type
            $stream->getString(),                             // name
            $stream->getSerializable(User::class),            // user
            $stream->getNullableSerializable(Member::class)   // member
        );
    }
}