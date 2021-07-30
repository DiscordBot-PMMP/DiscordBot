<?php
/*
 * DiscordBot, PocketMine-MP Plugin.
 *
 * Licensed under the Open Software License version 3.0 (OSL-3.0)
 * Copyright (C) 2020-2021 JaxkDev
 *
 * Twitter :: @JaxkDev
 * Discord :: JaxkDev#2698
 * Email   :: JaxkDev@gmail.com
 */

namespace JaxkDev\DiscordBot\Models\Messages;

use JaxkDev\DiscordBot\Models\Messages\Embed\Embed;
use JaxkDev\DiscordBot\Plugin\Utils;

class Message implements \Serializable{

    /** @var ?string Null when sending message. */
    protected $id;

    /** @var string (<=2000 characters for bots/users. <=4000 for nitro users) Possibly empty with attachments/embeds. */
    protected $content = "";

    /** @var ?Embed Note gateway v9 / dphp7 supports several embeds and attachments in normal messages. (merge with webhook handling) */
    protected $embed;

    /** @var ?string MemberID (guildID.userID), Null when sending or receiving webhook messages, just (UserID) if DM Channel. */
    protected $author_id;

    /** @var string */
    protected $channel_id;

    /** @var ?string Null if DM Channel. */
    protected $server_id;

    /** @var ?float Null when sending message. */
    protected $timestamp;

    /** @var Attachment[] Used for INBOUND messages only. */
    protected $attachments = [];

    /** @var bool */
    protected $everyone_mentioned = false;

    /** @var string[] */
    protected $users_mentioned = [];

    /** @var string[] */
    protected $roles_mentioned = [];

    /** @var string[] */
    protected $channels_mentioned = [];

    /**
     * Message constructor.
     *
     * @param string       $channel_id
     * @param string|null  $id
     * @param string       $content
     * @param Embed|null   $embed
     * @param string|null  $author_id
     * @param string|null  $server_id
     * @param float|null   $timestamp
     * @param Attachment[] $attachments
     * @param bool         $everyone_mentioned
     * @param string[]     $users_mentioned
     * @param string[]     $roles_mentioned
     * @param string[]     $channels_mentioned
     */
    public function __construct(string $channel_id, ?string $id = null, string $content = "", ?Embed $embed = null,
                                ?string $author_id = null, ?string $server_id = null, ?float $timestamp = null,
                                array $attachments = [], bool $everyone_mentioned = false, array $users_mentioned = [],
                                array $roles_mentioned = [], array $channels_mentioned = []){
        $this->setChannelId($channel_id);
        $this->setId($id);
        $this->setContent($content);
        $this->setEmbed($embed);
        $this->setAuthorId($author_id);
        $this->setServerId($server_id);
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

    public function getServerId(): ?string{
        return $this->server_id;
    }

    public function setServerId(?string $server_id): void{
        if($server_id !== null and !Utils::validDiscordSnowflake($server_id)){
            throw new \AssertionError("Server ID '$server_id' is invalid.");
        }
        $this->server_id = $server_id;
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
     * @var Attachment[] $attachments
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

    public function serialize(): ?string{
        return serialize([
            $this->id,
            $this->content,
            $this->embed,
            $this->author_id,
            $this->channel_id,
            $this->server_id,
            $this->timestamp,
            $this->attachments,
            $this->everyone_mentioned,
            $this->users_mentioned,
            $this->roles_mentioned,
            $this->channels_mentioned
        ]);
    }

    public function unserialize($data): void{
        [
            $this->id,
            $this->content,
            $this->embed,
            $this->author_id,
            $this->channel_id,
            $this->server_id,
            $this->timestamp,
            $this->attachments,
            $this->everyone_mentioned,
            $this->users_mentioned,
            $this->roles_mentioned,
            $this->channels_mentioned
        ] = unserialize($data);
    }
}