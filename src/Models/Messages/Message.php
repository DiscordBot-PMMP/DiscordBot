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

namespace JaxkDev\DiscordBot\Models\Messages;

use JaxkDev\DiscordBot\Models\Messages\Embed\Embed;
use JaxkDev\DiscordBot\Plugin\Utils;

class Message{

    /** Null when sending message. */
    protected ?string $id;

    /** (<=2000 characters for bots/users. <=4000 for nitro users) Possibly empty with attachments/embeds. */
    protected string $content = "";

    /** Note gateway v9 / dphp7 supports several embeds and attachments in normal messages. (merge with webhook handling) */
    protected ?Embed $embed;

    /**  MemberID (guildID.userID), Null when sending or receiving webhook messages, just (UserID) if DM Channel. */
    protected ?string $author_id;

    protected string $channel_id;

    /** Null if DM Channel. */
    protected ?string $guild_id;

    /** Null when sending message. */
    protected ?float $timestamp;

    /** @var Attachment[] Used for INBOUND messages only. */
    protected array $attachments = [];

    protected bool $everyone_mentioned = false;

    /** @var string[] */
    protected array $users_mentioned = [];

    /** @var string[] */
    protected array $roles_mentioned = [];

    /** @var string[] */
    protected array $channels_mentioned = [];

    /**
     * @param Attachment[] $attachments
     * @param string[]     $users_mentioned
     * @param string[]     $roles_mentioned
     * @param string[]     $channels_mentioned
     */
    public function __construct(string $channel_id, ?string $id = null, string $content = "", ?Embed $embed = null,
                                ?string $author_id = null, ?string $guild_id = null, ?float $timestamp = null,
                                array $attachments = [], bool $everyone_mentioned = false, array $users_mentioned = [],
                                array $roles_mentioned = [], array $channels_mentioned = []){
        $this->setChannelId($channel_id);
        $this->setId($id);
        $this->setContent($content);
        $this->setEmbed($embed);
        $this->setAuthorId($author_id);
        $this->setGuildId($guild_id);
        $this->setTimestamp($timestamp);
        $this->setAttachments($attachments);
        $this->setEveryoneMentioned($everyone_mentioned);
        $this->setUsersMentioned($users_mentioned);
        $this->setRolesMentioned($roles_mentioned);
        $this->setChannelsMentioned($channels_mentioned);
    }

    public function getId(): ?string{
        return $this->id;
    }

    public function setId(?string $id): void{
        $this->id = $id;
    }

    public function getContent(): string{
        return $this->content;
    }

    public function setContent(string $content): void{
        if(strlen($content) > 4000){
            throw new \AssertionError("Message content cannot exceed 4000 characters.");
        }
        $this->content = $content;
    }

    public function getEmbed(): ?Embed{
        return $this->embed;
    }

    public function setEmbed(?Embed $embed): void{
        $this->embed = $embed;
    }

    public function getAuthorId(): ?string{
        return $this->author_id;
    }

    public function setAuthorId(?string $author_id): void{
        if($author_id !== null and stripos($author_id, ".") !== false){
            [$sid, $uid] = explode(".", $author_id);
            if(!Utils::validDiscordSnowflake($sid) or !Utils::validDiscordSnowflake($uid)){
                throw new \AssertionError("Author ID '$author_id' is invalid.");
            }
        }elseif($author_id !== null){
            //Webhooks and DM's
            if(!Utils::validDiscordSnowflake($author_id)){
                throw new \AssertionError("Author ID '$author_id' is invalid.");
            }
        }
        $this->author_id = $author_id;
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

    public function getGuildId(): ?string{
        return $this->guild_id;
    }

    public function setGuildId(?string $guild_id): void{
        if($guild_id !== null and !Utils::validDiscordSnowflake($guild_id)){
            throw new \AssertionError("Guild ID '$guild_id' is invalid.");
        }
        $this->guild_id = $guild_id;
    }

    public function getTimestamp(): ?float{
        return $this->timestamp;
    }

    public function setTimestamp(?float $timestamp): void{
        $this->timestamp = $timestamp;
    }

    /**
     * @return Attachment[]
     */
    public function getAttachments(): array{
        return $this->attachments;
    }

    /**
     * Notice, these will not work when sending/updating messages, its for INBOUND ONLY.
     *
     * @param Attachment[] $attachments
     */
    public function setAttachments(array $attachments): void{
        foreach($attachments as $attachment){
            if(!$attachment instanceof Attachment){
                throw new \AssertionError("Attachments must be an Attachment instance.");
            }
        }
        $this->attachments = $attachments;
    }

    public function isEveryoneMentioned(): bool{
        return $this->everyone_mentioned;
    }

    public function setEveryoneMentioned(bool $everyone_mentioned): void{
        $this->everyone_mentioned = $everyone_mentioned;
    }

    /**
     * @return string[]
     */
    public function getUsersMentioned(): array{
        return $this->users_mentioned;
    }

    /**
     * @param string[] $users_mentioned
     */
    public function setUsersMentioned(array $users_mentioned): void{
        foreach($users_mentioned as $id){
            if(!Utils::validDiscordSnowflake($id)){
                throw new \AssertionError("User ID '$id' is invalid.");
            }
        }
        $this->users_mentioned = $users_mentioned;
    }

    /**
     * @return string[]
     */
    public function getRolesMentioned(): array{
        return $this->roles_mentioned;
    }

    /**
     * @param string[] $roles_mentioned
     */
    public function setRolesMentioned(array $roles_mentioned): void{
        foreach($roles_mentioned as $id){
            if(!Utils::validDiscordSnowflake($id)){
                throw new \AssertionError("Role ID '$id' is invalid.");
            }
        }
        $this->roles_mentioned = $roles_mentioned;
    }

    /**
     * @return string[]
     */
    public function getChannelsMentioned(): array{
        return $this->channels_mentioned;
    }

    /**
     * @param string[] $channels_mentioned
     */
    public function setChannelsMentioned(array $channels_mentioned): void{
        foreach($channels_mentioned as $id){
            if(!Utils::validDiscordSnowflake($id)){
                throw new \AssertionError("Channel ID '$id' is invalid.");
            }
        }
        $this->channels_mentioned = $channels_mentioned;
    }

    //----- Serialization -----//

    public function __serialize(): array{
        return [
            $this->id,
            $this->content,
            $this->embed,
            $this->author_id,
            $this->channel_id,
            $this->guild_id,
            $this->timestamp,
            $this->attachments,
            $this->everyone_mentioned,
            $this->users_mentioned,
            $this->roles_mentioned,
            $this->channels_mentioned
        ];
    }

    public function __unserialize(array $data): void{
        [
            $this->id,
            $this->content,
            $this->embed,
            $this->author_id,
            $this->channel_id,
            $this->guild_id,
            $this->timestamp,
            $this->attachments,
            $this->everyone_mentioned,
            $this->users_mentioned,
            $this->roles_mentioned,
            $this->channels_mentioned
        ] = $data;
    }
}