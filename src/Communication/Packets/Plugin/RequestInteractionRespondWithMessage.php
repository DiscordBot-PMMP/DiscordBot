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
use JaxkDev\DiscordBot\Models\Interactions\Interaction;
use JaxkDev\DiscordBot\Models\Messages\Component\ActionRow;
use JaxkDev\DiscordBot\Models\Messages\Embed\Embed;

final class RequestInteractionRespondWithMessage extends Packet{

    public const SERIALIZE_ID = 433;

    private Interaction $interaction;

    /**
     * Cannot be longer than 2000 characters.
     *
     * IMPORTANT NOTE: Must provide a value for at least one of content, embeds, components, or files
     * Can only be null if one of the other options mentioned above is provided.
     */
    private ?string $content;

    /** @var Embed[]|null Up to 10 rich embeds (up to 6000 characters) */
    private ?array $embeds;

    private ?bool $tts;

    /** @var ActionRow[]|null Array of attached components (ActionRows) (max 5 Action Rows, no TextInput components are allowed via message)*/
    private ?array $components;

    /**
     * @var array<string, string>|null Array of files to send with the message, file name (key) must include extension.
     * ["file_name.txt" => "RAW_FILE_DATA", "file_name.png" => "RAW FILE ETC..."]
     * No limit on amount of files, but total size of all files cannot exceed 8MB.
     */
    private ?array $files;

    private bool $ephemeral;

    /**
     * @param Embed[]|null               $embeds
     * @param ActionRow[]|null           $components
     * @param array<string, string>|null $files
     */
    public function __construct(Interaction $interaction, ?string $content, ?array $embeds, ?bool $tts,
                                ?array $components, ?array $files, bool $ephemeral, ?int $uid = null){
        parent::__construct($uid);
        $this->interaction = $interaction;
        $this->content = $content;
        $this->embeds = $embeds;
        $this->tts = $tts;
        $this->components = $components;
        $this->files = $files;
        $this->ephemeral = $ephemeral;
    }

    public function getInteraction(): Interaction{
        return $this->interaction;
    }

    public function getContent(): ?string{
        return $this->content;
    }

    /** @return Embed[]|null */
    public function getEmbeds(): ?array{
        return $this->embeds;
    }

    public function getTts(): ?bool{
        return $this->tts;
    }

    /** @return ActionRow[]|null */
    public function getComponents(): ?array{
        return $this->components;
    }

    /** @return array<string, string>|null */
    public function getFiles(): ?array{
        return $this->files;
    }

    public function getEphemeral(): bool{
        return $this->ephemeral;
    }

    public function binarySerialize(): BinaryStream{
        $stream = new BinaryStream();
        $stream->putInt($this->getUID());
        $stream->putSerializable($this->interaction);
        $stream->putNullableString($this->content);
        $stream->putNullableSerializableArray($this->embeds);
        $stream->putNullableBool($this->tts);
        $stream->putNullableSerializableArray($this->components);
        $stream->putNullableStringStringArray($this->files);
        $stream->putBool($this->ephemeral);
        return $stream;
    }

    public static function fromBinary(BinaryStream $stream): self{
        $uid = $stream->getInt();
        return new self(
            $stream->getSerializable(Interaction::class),                   // interaction
            $stream->getNullableString(),                                   // content
            $stream->getNullableSerializableArray(Embed::class),            // embeds
            $stream->getNullableBool(),                                     // tts
            $stream->getNullableSerializableArray(ActionRow::class),        // components
            $stream->getNullableStringStringArray(),                        // files
            $stream->getBool(),                                             // ephemeral
            $uid
        );
    }
}