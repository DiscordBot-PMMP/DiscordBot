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

namespace JaxkDev\DiscordBot\Models\Messages\Embed;

//Yes quite a lot of nullables... (https://discord.com/developers/docs/resources/channel#embed-object)
class Embed{

    // https://discord.com/developers/docs/resources/channel#embed-object-embed-types
    const
        TYPE_RICH = 'rich',
        TYPE_IMAGE = 'image',
        TYPE_VIDEO = 'video',
        TYPE_GIFV = 'gifv',
        TYPE_ARTICLE = 'article',
        TYPE_LINK = 'link';

    /** 2048 characters */
    private ?string $title;

    private ?string $type;

    /** 4096 characters */
    private ?string $description;

    private ?string $url;

    private ?int $timestamp;

    private ?int $colour;

    //Provider?? https://discord.com/developers/docs/resources/channel#embed-object-embed-image-structure

    private Footer $footer;

    private Image $image;

    private Image $thumbnail;

    private Video $video;

    private Author $author;

    /** @var Field[] 25 max */
    private array $fields = [];

    /**
     * @param Field[] $fields
     */
    public function __construct(?string $title = null, ?string $type = null, ?string $description = null, ?string $url = null,
                                   ?int $timestamp = null, ?int $colour = null, Footer $footer = null, Image $image = null,
                                   Image $thumbnail = null, Video $video = null, Author $author = null, array $fields = []){
        $this->setTitle($title);
        $this->setType($type);
        $this->setDescription($description);
        $this->setUrl($url);
        $this->setTimestamp($timestamp);
        $this->setColour($colour);
        $this->setFooter($footer ?? new Footer());
        $this->setImage($image ?? new Image());
        $this->setThumbnail($thumbnail ?? new Image());
        $this->setVideo($video ?? new Video());
        $this->setAuthor($author ?? new Author());
        $this->setFields($fields);
    }

    public function getTitle(): ?string{
        return $this->title;
    }

    public function setTitle(?string $title): void{
        if($title !== null and strlen($title) > 2048){
            throw new \AssertionError("Embed title can only have up to 2048 characters.");
        }
        $this->title = $title;
    }

    public function getType(): ?string{
        return $this->type;
    }

    public function setType(?string $type): void{
        if($type !== null and (!in_array($type, [self::TYPE_LINK, self::TYPE_ARTICLE, self::TYPE_GIFV, self::TYPE_VIDEO, self::TYPE_IMAGE, self::TYPE_RICH], true))){
            throw new \AssertionError("Invalid embed type '{$type}' provided.");
        }
        $this->type = $type;
    }

    public function getDescription(): ?string{
        return $this->description;
    }

    public function setDescription(?string $description): void{
        if($description !== null and strlen($description) > 4096){
            throw new \AssertionError("Embed description can only have up to 4096 characters.");
        }
        $this->description = $description;
    }

    public function getUrl(): ?string{
        return $this->url;
    }

    public function setUrl(?string $url): void{
        $this->url = $url;
    }

    public function getTimestamp(): ?int{
        return $this->timestamp;
    }

    public function setTimestamp(?int $timestamp): void{
        $this->timestamp = $timestamp;
    }

    public function getColour(): ?int{
        return $this->colour;
    }

    public function setColour(?int $colour): void{
        $this->colour = $colour;
    }

    public function getFooter(): Footer{
        return $this->footer;
    }

    public function setFooter(Footer $footer): void{
        $this->footer = $footer;
    }

    public function getImage(): Image{
        return $this->image;
    }

    public function setImage(Image $image): void{
        $this->image = $image;
    }

    public function getThumbnail(): Image{
        return $this->thumbnail;
    }

    public function setThumbnail(Image $thumbnail): void{
        $this->thumbnail = $thumbnail;
    }

    public function getVideo(): Video{
        return $this->video;
    }

    public function setVideo(Video $video): void{
        $this->video = $video;
    }

    public function getAuthor(): Author{
        return $this->author;
    }

    public function setAuthor(Author $author): void{
        $this->author = $author;
    }

    /** @return Field[] */
    public function getFields(): array{
        return $this->fields;
    }

    /** @param Field[] $fields */
    public function setFields(array $fields): void{
        if(sizeof($fields) > 25){
            throw new \AssertionError("Embed can only have up to 25 fields.");
        }
        $this->fields = $fields;
    }

    //----- Serialization -----//

    public function __serialize(): array{
        return [
            $this->title,
            $this->type,
            $this->description,
            $this->url,
            $this->timestamp,
            $this->colour,
            $this->footer,
            $this->image,
            $this->thumbnail,
            $this->video,
            $this->author,
            $this->fields
        ];
    }

    public function __unserialize(array $data): void{
        [
            $this->title,
            $this->type,
            $this->description,
            $this->url,
            $this->timestamp,
            $this->colour,
            $this->footer,
            $this->image,
            $this->thumbnail,
            $this->video,
            $this->author,
            $this->fields
        ] = $data;
    }
}
