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

namespace JaxkDev\DiscordBot\Models\Interactions;

use JaxkDev\DiscordBot\Communication\BinarySerializable;
use JaxkDev\DiscordBot\Communication\BinaryStream;
use JaxkDev\DiscordBot\Models\Messages\Component\Component;

/**
 * @implements BinarySerializable<ModalSubmitData>
 * @link https://discord.com/developers/docs/interactions/receiving-and-responding#interaction-object-modal-submit-data-structure
 */
class ModalSubmitData implements BinarySerializable{

    /** the custom_id of the modal. */
    private string $custom_id;

    /**
     * the values the user submitted.
     * @var Component[] $components
     */
    private array $components;

    /** @param Component[] $components */
    public function __construct(string $custom_id, array $components){
        $this->setCustomId($custom_id);
        $this->setComponents($components);
    }

    public function getCustomId(): string{
        return $this->custom_id;
    }

    public function setCustomId(string $custom_id): void{
        $this->custom_id = $custom_id;
    }

    /** @return Component[] */
    public function getComponents(): array{
        return $this->components;
    }

    /** @param Component[] $components */
    public function setComponents(array $components): void{
        $this->components = $components;
    }

    public function binarySerialize(): BinaryStream{
        // TODO: Implement binarySerialize() method.
        // We don't know the exact components coming/going, need to SERIALIZE_ID & map them :(
        return new BinaryStream();
    }

    public static function fromBinary(BinaryStream $stream): self{
        // TODO: Implement fromBinary() method.
        return new self("", []);
    }
}