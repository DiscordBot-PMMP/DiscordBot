<?php
/*
 * DiscordBot, PocketMine-MP Plugin.
 *
 * Licensed under the Open Software License version 3.0 (OSL-3.0)
 * Copyright (C) 2020-present JaxkDev
 *
 * Twitter :: @JaxkDev
 * Discord :: JaxkDev#2698
 * Email   :: JaxkDev@gmail.com
 */

namespace JaxkDev\DiscordBot\Models\Messages\Embed;

// https://discord.com/developers/docs/resources/channel#embed-object-embed-author-structure
class Author{

    /** 2048 characters */
    private ?string $name;

    private ?string $url;

    /** Must be prefixed with `https` */
    private ?string $icon_url;

    public function __construct(?string $name = null, ?string $url = null, ?string $icon_url = null){
        $this->setName($name);
        $this->setUrl($url);
        $this->setIconUrl($icon_url);
    }

    public function getName(): ?string{
        return $this->name;
    }

    public function setName(?string $name): void{
        if($name !== null and strlen($name) > 2048){
            throw new \AssertionError("Embed author name can only have up to 2048 characters.");
        }
        $this->name = $name;
    }

    public function getUrl(): ?string{
        return $this->url;
    }

    public function setUrl(?string $url): void{
        $this->url = $url;
    }

    public function getIconUrl(): ?string{
        return $this->icon_url;
    }

    public function setIconUrl(?string $icon_url): void{
        if($icon_url !== null and strpos($icon_url , "https" ) !== 0){
            throw new \AssertionError("Embed author icon url '$icon_url' must start with https.");
        }
        $this->icon_url = $icon_url;
    }

    //----- Serialization -----//

    public function __serialize(): array{
        return [
            $this->name,
            $this->url,
            $this->icon_url
        ];
    }

    public function __unserialize(array $data): void{
        [
            $this->name,
            $this->url,
            $this->icon_url
        ] = $data;
    }
}