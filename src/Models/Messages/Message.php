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
use JaxkDev\DiscordBot\Models\Messages\Component\ActionRow;
use JaxkDev\DiscordBot\Models\Messages\Embed\Embed;
use JaxkDev\DiscordBot\Models\StickerPartial;
use JaxkDev\DiscordBot\Plugin\Utils;
use function count;

/**
 * @implements BinarySerializable<Message>
 * @link https://discord.com/developers/docs/resources/channel#message-object-message-structure
 */
final class Message implements BinarySerializable{

    public const SERIALIZE_ID = 12;

    /**
     * @link https://discord.com/developers/docs/resources/channel#message-object-message-flags
     * @var array<string, int>
     */
    public const FLAGS = [
        "CROSSPOSTED" => 1 << 0,
        "IS_CROSSPOST" => 1 << 1,
        "SUPPRESS_EMBEDS" => 1 << 2,
        "SOURCE_MESSAGE_DELETED" => 1 << 3,
        "URGENT" => 1 << 4,
        "HAS_THREAD" => 1 << 5,
        "EPHEMERAL" => 1 << 6,
        "LOADING" => 1 << 7,
        "FAILED_TO_MENTION_SOME_ROLES_IN_THREAD" => 1 << 8,
        "SUPPRESS_NOTIFICATIONS" => 1 << 12,
        "IS_VOICE_MESSAGE" => 1 << 13
    ];

    private MessageType $type;

    private string $id;

    private string $channel_id;

    private ?string $author_id;

    /** Possibly null with attachments/embeds/stickers/components. */
    private ?string $content;

    private int $timestamp;

    /** Null if never edited. */
    private ?int $edited_timestamp;

    private bool $tts;

    private bool $mention_everyone;

    /** @var string[] User IDs */
    private array $mentions;

    /** @var string[] Role IDs */
    private array $mention_roles;

    /** @var Attachment[] */
    private array $attachments;

    /** @var Embed[] */
    private array $embeds;

    /** @var Reaction[] */
    private array $reactions;

    private bool $pinned;

    private ?string $webhook_id;

    private ?Activity $activity;

    private ?string $application_id;

    private ?Reference $message_reference;

    /**
     * @var int|null Flags bitmask.
     * @see Message::FLAGS
     */
    private ?int $flags;

    /**
     * This field is only returned for messages with a type of 19 (REPLY) or 21 (THREAD_STARTER_MESSAGE).
     * If the message is a reply but the referenced_message field is not present,
     * the backend did not attempt to fetch the message that was being replied to, so its state is unknown.
     * If the field exists but is null, the referenced message was deleted.
     */
    private ?Message $referenced_message;

    /**
     * This is sent on the message object when the message is a response to an Interaction without an existing message.
     * This means responses to Message Components do not include this property,
     * instead including a message reference object as components always exist on preexisting messages.
     */
    private ?MessageInteraction $message_interaction;

    private ?string $thread_id;

    /** @var ActionRow[] Max 5 */
    private array $components;

    /** @var StickerPartial[] */
    private array $sticker_items;

    /**
     * @param string[]         $mentions
     * @param string[]         $mention_roles
     * @param Attachment[]     $attachments
     * @param Embed[]          $embeds
     * @param Reaction[]       $reactions
     * @param ActionRow[]      $components    Max 5
     * @param StickerPartial[] $sticker_items
     */
    public function __construct(MessageType $type, string $id, string $channel_id, ?string $author_id, ?string $content,
                                int $timestamp, ?int $edited_timestamp, bool $tts, bool $mention_everyone,
                                array $mentions, array $mention_roles, array $attachments, array $embeds,
                                array $reactions, bool $pinned, ?string $webhook_id, ?Activity $activity,
                                ?string $application_id, ?Reference $message_reference, ?int $flags,
                                ?Message $referenced_message, ?MessageInteraction $message_interaction,
                                ?string $thread_id, array $components, array $sticker_items){
        $this->type = $type;
        $this->setId($id);
        $this->setChannelId($channel_id);
        $this->setAuthorId($author_id);
        $this->setContent($content);
        $this->setTimestamp($timestamp);
        $this->setEditedTimestamp($edited_timestamp);
        $this->setTts($tts);
        $this->setMentionEveryone($mention_everyone);
        $this->setMentions($mentions);
        $this->setMentionRoles($mention_roles);
        $this->setAttachments($attachments);
        $this->setEmbeds($embeds);
        $this->setReactions($reactions);
        $this->setPinned($pinned);
        $this->setWebhookId($webhook_id);
        $this->setActivity($activity);
        $this->setApplicationId($application_id);
        $this->setMessageReference($message_reference);
        $this->setFlags($flags);
        $this->setReferencedMessage($referenced_message);
        $this->setMessageInteraction($message_interaction);
        $this->setThreadId($thread_id);
        $this->setComponents($components);
        $this->setStickerItems($sticker_items);
    }

    public function getType(): MessageType{
        return $this->type;
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

    public function getChannelId(): string{
        return $this->channel_id;
    }

    public function setChannelId(string $channel_id): void{
        if(!Utils::validDiscordSnowflake($channel_id)){
            throw new \AssertionError("Channel ID '$channel_id' is invalid.");
        }
        $this->channel_id = $channel_id;
    }

    public function getAuthorId(): ?string{
        return $this->author_id;
    }

    public function setAuthorId(?string $author_id): void{
        if($author_id !== null && !Utils::validDiscordSnowflake($author_id)){
            throw new \AssertionError("Author ID '$author_id' is invalid.");
        }
        $this->author_id = $author_id;
    }

    public function getContent(): ?string{
        return $this->content;
    }

    public function setContent(?string $content): void{
        $this->content = $content;
    }

    public function getTimestamp(): int{
        return $this->timestamp;
    }

    public function setTimestamp(int $timestamp): void{
        $this->timestamp = $timestamp;
    }

    public function getEditedTimestamp(): ?int{
        return $this->edited_timestamp;
    }

    public function setEditedTimestamp(?int $edited_timestamp): void{
        $this->edited_timestamp = $edited_timestamp;
    }

    public function getTts(): bool{
        return $this->tts;
    }

    public function setTts(bool $tts): void{
        $this->tts = $tts;
    }

    public function getMentionEveryone(): bool{
        return $this->mention_everyone;
    }

    public function setMentionEveryone(bool $mention_everyone): void{
        $this->mention_everyone = $mention_everyone;
    }

    /** @return string[] User IDs */
    public function getMentions(): array{
        return $this->mentions;
    }

    /** @param string[] $mentions User IDs */
    public function setMentions(array $mentions): void{
        foreach($mentions as $mention){
            if(!Utils::validDiscordSnowflake($mention)){
                throw new \AssertionError("Mention ID '$mention' is invalid.");
            }
        }
        $this->mentions = $mentions;
    }

    /** @return string[] Role IDs */
    public function getMentionRoles(): array{
        return $this->mention_roles;
    }

    /** @param string[] $mention_roles Role IDs */
    public function setMentionRoles(array $mention_roles): void{
        foreach($mention_roles as $mention_role){
            if(!Utils::validDiscordSnowflake($mention_role)){
                throw new \AssertionError("Mention role ID '$mention_role' is invalid.");
            }
        }
        $this->mention_roles = $mention_roles;
    }

    /** @return Attachment[] */
    public function getAttachments(): array{
        return $this->attachments;
    }

    /** @param Attachment[] $attachments */
    public function setAttachments(array $attachments): void{
        foreach($attachments as $attachment){
            if(!$attachment instanceof Attachment){
                throw new \AssertionError("Attachments must be an array of Attachment.");
            }
        }
        $this->attachments = $attachments;
    }

    /** @return Embed[] */
    public function getEmbeds(): array{
        return $this->embeds;
    }

    /** @param Embed[] $embeds */
    public function setEmbeds(array $embeds): void{
        foreach($embeds as $embed){
            if(!$embed instanceof Embed){
                throw new \AssertionError("Embeds must be an array of Embed.");
            }
        }
        $this->embeds = $embeds;
    }

    /** @return Reaction[] */
    public function getReactions(): array{
        return $this->reactions;
    }

    /** @param Reaction[] $reactions */
    public function setReactions(array $reactions): void{
        foreach($reactions as $reaction){
            if(!$reaction instanceof Reaction){
                throw new \AssertionError("Reactions must be an array of Reaction.");
            }
        }
        $this->reactions = $reactions;
    }

    public function getPinned(): bool{
        return $this->pinned;
    }

    public function setPinned(bool $pinned): void{
        $this->pinned = $pinned;
    }

    public function getWebhookId(): ?string{
        return $this->webhook_id;
    }

    public function setWebhookId(?string $webhook_id): void{
        if($webhook_id !== null && !Utils::validDiscordSnowflake($webhook_id)){
            throw new \AssertionError("Webhook ID '$webhook_id' is invalid.");
        }
        $this->webhook_id = $webhook_id;
    }

    public function getActivity(): ?Activity{
        return $this->activity;
    }

    public function setActivity(?Activity $activity): void{
        $this->activity = $activity;
    }

    public function getApplicationId(): ?string{
        return $this->application_id;
    }

    public function setApplicationId(?string $application_id): void{
        if($application_id !== null && !Utils::validDiscordSnowflake($application_id)){
            throw new \AssertionError("Application ID '$application_id' is invalid.");
        }
        $this->application_id = $application_id;
    }

    public function getMessageReference(): ?Reference{
        return $this->message_reference;
    }

    public function setMessageReference(?Reference $message_reference): void{
        $this->message_reference = $message_reference;
    }

    public function getFlags(): ?int{
        return $this->flags;
    }

    public function setFlags(?int $flags): void{
        $this->flags = $flags;
    }

    public function getReferencedMessage(): ?Message{
        return $this->referenced_message;
    }

    public function setReferencedMessage(?Message $referenced_message): void{
        $this->referenced_message = $referenced_message;
    }

    public function getMessageInteraction(): ?MessageInteraction{
        return $this->message_interaction;
    }

    public function setMessageInteraction(?MessageInteraction $message_interaction): void{
        $this->message_interaction = $message_interaction;
    }

    public function getThreadId(): ?string{
        return $this->thread_id;
    }

    public function setThreadId(?string $thread_id): void{
        if($thread_id !== null && !Utils::validDiscordSnowflake($thread_id)){
            throw new \AssertionError("Thread ID '$thread_id' is invalid.");
        }
        $this->thread_id = $thread_id;
    }

    /** @return ActionRow[] */
    public function getComponents(): array{
        return $this->components;
    }

    /** @param ActionRow[] $components Max 5 */
    public function setComponents(array $components): void{
        if(count($components) > 5){
            throw new \AssertionError("Max 5 components per message.");
        }
        foreach($components as $component){
            if(!$component instanceof ActionRow){
                throw new \AssertionError("Components must be an array of ActionRow.");
            }
        }
        $this->components = $components;
    }

    /** @return StickerPartial[] */
    public function getStickerItems(): array{
        return $this->sticker_items;
    }

    /** @param StickerPartial[] $sticker_items */
    public function setStickerItems(array $sticker_items): void{
        foreach($sticker_items as $sticker_item){
            if(!$sticker_item instanceof StickerPartial){
                throw new \AssertionError("StickerPartial items must be an array of StickerPartial.");
            }
        }
        $this->sticker_items = $sticker_items;
    }

    public function binarySerialize(): BinaryStream{
        $stream = new BinaryStream();
        $stream->putByte($this->type->value);
        $stream->putString($this->id);
        $stream->putString($this->channel_id);
        $stream->putNullableString($this->author_id);
        $stream->putNullableString($this->content);
        $stream->putLong($this->timestamp);
        $stream->putNullableLong($this->edited_timestamp);
        $stream->putBool($this->tts);
        $stream->putBool($this->mention_everyone);
        $stream->putStringArray($this->mentions);
        $stream->putStringArray($this->mention_roles);
        $stream->putSerializableArray($this->attachments);
        $stream->putSerializableArray($this->embeds);
        $stream->putSerializableArray($this->reactions);
        $stream->putBool($this->pinned);
        $stream->putNullableString($this->webhook_id);
        $stream->putNullableSerializable($this->activity);
        $stream->putNullableString($this->application_id);
        $stream->putNullableSerializable($this->message_reference);
        $stream->putNullableInt($this->flags);
        $stream->putNullableSerializable($this->referenced_message);
        $stream->putNullableSerializable($this->message_interaction);
        $stream->putNullableString($this->thread_id);
        $stream->putSerializableArray($this->components);
        $stream->putSerializableArray($this->sticker_items);
        return $stream;
    }

    public static function fromBinary(BinaryStream $stream): self{
        return new self(
            MessageType::from($stream->getByte()),                       // type
            $stream->getString(),                                        // id
            $stream->getString(),                                        // channel_id
            $stream->getNullableString(),                                // author_id
            $stream->getNullableString(),                                // content
            $stream->getLong(),                                          // timestamp
            $stream->getNullableLong(),                                  // edited_timestamp
            $stream->getBool(),                                          // tts
            $stream->getBool(),                                          // mention_everyone
            $stream->getStringArray(),                                   // mentions
            $stream->getStringArray(),                                   // mention_roles
            $stream->getSerializableArray(Attachment::class),            // attachments
            $stream->getSerializableArray(Embed::class),                 // embeds
            $stream->getSerializableArray(Reaction::class),              // reactions
            $stream->getBool(),                                          // pinned
            $stream->getNullableString(),                                // webhook_id
            $stream->getNullableSerializable(Activity::class),           // activity
            $stream->getNullableString(),                                // application_id
            $stream->getNullableSerializable(Reference::class),          // message_reference
            $stream->getNullableInt(),                                   // flags
            $stream->getNullableSerializable(Message::class),            // referenced_message
            $stream->getNullableSerializable(MessageInteraction::class), // message_interaction
            $stream->getNullableString(),                                // thread_id
            $stream->getSerializableArray(ActionRow::class),             // components
            $stream->getSerializableArray(StickerPartial::class)         // sticker_items
        );
    }
}