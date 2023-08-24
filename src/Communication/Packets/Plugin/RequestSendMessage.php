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

namespace JaxkDev\DiscordBot\Communication\Packets\Plugin;

use JaxkDev\DiscordBot\Communication\BinaryStream;
use JaxkDev\DiscordBot\Communication\Packets\Packet;
use JaxkDev\DiscordBot\Models\Messages\Component\ActionRow;
use JaxkDev\DiscordBot\Models\Messages\Embed\Embed;

final class RequestSendMessage extends Packet{

    public const SERIALIZE_ID = 77;

    private ?string $guild_id;

    private string $channel_id;

    /**
     * Cannot be longer than 2000 characters.
     *
     * IMPORTANT NOTE: "When creating a message, must provide a value for at least one of content, embeds, sticker_ids, components, or files"
     * Can only be null if one of the other options mentioned above is provided.
     */
    private ?string $content;

    /** If message is a reply, set the message ID to reply to. */
    private ?string $reply_message_id;

    /** @var Embed[]|null Up to 10 rich embeds (up to 6000 characters) */
    private ?array $embeds;

    /** true if this is a TTS message */
    private ?bool $tts;

    /** @var ActionRow[]|null Array of attached components (ActionRows) (max 5 Action Rows, no TextInput components are allowed via message)*/
    private ?array $components;

    /** @var string[]|null IDs of up to 3 stickers in the server to send in the message */
    private ?array $sticker_ids;

    /**
     * @var array<string, string>|null Array of files to send with the message, file name (key) must include extension.
     * ["file_name.txt" => "RAW_FILE_DATA", "file_name.png" => "RAW FILE ETC..."]
     * No limit on amount of files, but total size of all files cannot exceed 8MB.
     */
    private ?array $files;

    /**
     * @param Embed[]|null               $embeds
     * @param ActionRow[]|null           $components
     * @param string[]|null              $sticker_ids
     * @param array<string, string>|null $files
     */
    public function __construct(?string $guild_id, string $channel_id, ?string $content = null, ?string $reply_message_id = null,
                                ?array $embeds = null, ?bool $tts = null, ?array $components = null, ?array $sticker_ids = null,
                                ?array $files = null, ?int $uid = null){
        parent::__construct($uid);
        $this->guild_id = $guild_id;
        $this->channel_id = $channel_id;
        $this->content = $content;
        $this->reply_message_id = $reply_message_id;
        $this->embeds = $embeds;
        $this->tts = $tts;
        $this->components = $components;
        $this->sticker_ids = $sticker_ids;
        $this->files = $files;
    }

    public function getGuildId(): ?string{
        return $this->guild_id;
    }

    public function getChannelId(): string{
        return $this->channel_id;
    }

    public function getContent(): ?string{
        return $this->content;
    }

    public function getReplyMessageId(): ?string{
        return $this->reply_message_id;
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

    /** @return string[]|null */
    public function getStickerIds(): ?array{
        return $this->sticker_ids;
    }

    /** @return array<string, string>|null */
    public function getFiles(): ?array{
        return $this->files;
    }

    public function binarySerialize(): BinaryStream{
        $stream = new BinaryStream();
        $stream->putInt($this->getUID());
        $stream->putNullableString($this->guild_id);
        $stream->putString($this->channel_id);
        $stream->putNullableString($this->content);
        $stream->putNullableString($this->reply_message_id);
        $stream->putNullableSerializableArray($this->embeds);
        $stream->putNullableBool($this->tts);
        $stream->putNullableSerializableArray($this->components);
        $stream->putNullableStringArray($this->sticker_ids);
        $stream->putNullableStringStringArray($this->files);
        return $stream;
    }

    public static function fromBinary(BinaryStream $stream): self{
        $uid = $stream->getInt();
        return new self(
            $stream->getNullableString(),                                   // guild_id
            $stream->getString(),                                           // channel_id
            $stream->getNullableString(),                                   // content
            $stream->getNullableString(),                                   // reply_message_id
            $stream->getNullableSerializableArray(Embed::class),            // embeds
            $stream->getNullableBool(),                                     // tts
            $stream->getNullableSerializableArray(ActionRow::class),        // components
            $stream->getNullableStringArray(),                              // sticker_ids
            $stream->getNullableStringStringArray(),                        // files
            $uid
        );
    }
}