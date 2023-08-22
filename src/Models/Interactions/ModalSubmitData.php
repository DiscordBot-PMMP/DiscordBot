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
use JaxkDev\DiscordBot\Communication\NetworkApi;
use JaxkDev\DiscordBot\Models\Messages\Component\Component;
use function sizeof;

/**
 * @implements BinarySerializable<ModalSubmitData>
 * @link https://discord.com/developers/docs/interactions/receiving-and-responding#interaction-object-modal-submit-data-structure
 */
final class ModalSubmitData implements BinarySerializable{

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
        $stream = new BinaryStream();
        $stream->putString($this->custom_id);
        $stream->putInt(sizeof($this->components));
        foreach($this->components as $component){
            $stream->putShort($component::SERIALIZE_ID);
            $stream->put($component->binarySerialize()->getBuffer());
        }
        return $stream;
    }

    public static function fromBinary(BinaryStream $stream): self{
        $custom_id = $stream->getString();
        $components = [];
        for($i = 0, $count = $stream->getInt(); $i < $count; $i++){
            $modelID = $stream->getShort();
            $modelClass = NetworkApi::getModelClass($modelID);
            if($modelClass === null){
                throw new \AssertionError("Invalid model ID '{$modelID}'");
            }
            /** @var Component $t */
            $t = $stream->getSerializable($modelClass);
            $components[] = $t;
        }
        return new self($custom_id, $components);
    }
}