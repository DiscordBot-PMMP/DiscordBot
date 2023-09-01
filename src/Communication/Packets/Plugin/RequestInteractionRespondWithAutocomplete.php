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
use JaxkDev\DiscordBot\Models\Interactions\Commands\CommandOptionChoice;
use JaxkDev\DiscordBot\Models\Interactions\Interaction;

final class RequestInteractionRespondWithAutocomplete extends Packet{

    public const SERIALIZE_ID = 432;

    private Interaction $interaction;

    /** @var CommandOptionChoice[] Max 25 choices. */
    private array $choices;

    /** @param CommandOptionChoice[] $choices */
    public function __construct(Interaction $interaction, array $choices, ?int $uid = null){
        parent::__construct($uid);
        $this->interaction = $interaction;
        $this->choices = $choices;
    }

    public function getInteraction(): Interaction{
        return $this->interaction;
    }

    /** @return CommandOptionChoice[] */
    public function getChoices(): array{
        return $this->choices;
    }

    public function binarySerialize(): BinaryStream{
        $stream = new BinaryStream();
        $stream->putInt($this->getUID());
        $stream->putSerializable($this->interaction);
        $stream->putSerializableArray($this->choices);
        return $stream;
    }

    public static function fromBinary(BinaryStream $stream): self{
        $uid = $stream->getInt();
        return new self(
            $stream->getSerializable(Interaction::class),              // interaction
            $stream->getSerializableArray(CommandOptionChoice::class), // choices
            $uid
        );
    }
}