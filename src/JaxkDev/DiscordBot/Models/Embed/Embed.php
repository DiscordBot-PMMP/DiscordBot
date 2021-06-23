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

namespace JaxkDev\DiscordBot\Models\Embed;

//Yes quite a lot of nullables... (https://discord.com/developers/docs/resources/channel#embed-object)
class Embed implements \Serializable{

	// https://discord.com/developers/docs/resources/channel#embed-object-embed-types
	const
		TYPE_RICH = 0,
		TYPE_IMAGE = 1,
		TYPE_VIDEO = 2,
		TYPE_GIF = 3,
		TYPE_ARTICLE = 4,
		TYPE_LINK = 5;

	/** @var null|string 2048 characters */
	private $title;

	/** @var null|int */
	private $type;

	/** @var null|string 2048 characters */
	private $description;

	/** @var null|string */
	private $url;

	/** @var null|int */
	private $timestamp;

	/** @var null|int */
	private $colour;

	//Provider?? https://discord.com/developers/docs/resources/channel#embed-object-embed-image-structure

	/** @var Footer */
	private $footer;

	/** @var Image */
	private $image;

	/** @var Image */
	private $thumbnail;

	/** @var Video */
	private $video;

	/** @var Author */
	private $author;

	/** @var Field[] 25 max */
	private $fields = [];

	public function getTitle(): ?string{
		return $this->title;
	}

	public function setTitle(?string $title): void{
		if($title !== null and strlen($title) > 2048){
			throw new \AssertionError("Embed title can only have up to 2048 characters.");
		}
		$this->title = $title;
	}

	public function getType(): ?int{
		return $this->type;
	}

	public function setType(?int $type): void{
		if($type !== null and ($type < self::TYPE_RICH or $type > self::TYPE_LINK)){
			throw new \AssertionError("Invalid embed type '{$type}' provided.");
		}
		$this->type = $type;
	}

	public function getDescription(): ?string{
		return $this->description;
	}

	public function setDescription(?string $description): void{
		if($description !== null and strlen($description) > 2048){
			throw new \AssertionError("Embed description can only have up to 2048 characters.");
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
			throw new \AssertionError("Embeds can only have up to 25 fields.");
		}
		$this->fields = $fields;
	}

	//----- Serialization -----//

	public function serialize(): ?string{
		return serialize([
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
		]);
	}

	public function unserialize($data): void{
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
		] = unserialize($data);
	}
}