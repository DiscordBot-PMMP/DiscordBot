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

class Footer{

    /** 2048 characters */
    private ?string $text;

    /** Must be prefixed with `https` */
    private ?string $icon_url;

    public function __construct(?string $text = null, ?string $icon_url = null){
        $this->setText($text);
        $this->setIconUrl($icon_url);
    }

    public function getText(): ?string{
        return $this->text;
    }

    public function setText(?string $text): void{
        if($text !== null and strlen($text) > 2048){
            throw new \AssertionError("Embed footer text can only have up to 2048 characters.");
        }
        $this->text = $text;
    }

    public function getIconUrl(): ?string{
        return $this->icon_url;
    }

    public function setIconUrl(?string $icon_url): void{
        if($icon_url !== null and strpos($icon_url , "https" ) !== 0){
            throw new \AssertionError("Embed footer icon URL '$icon_url' must start with https.");
        }
        $this->icon_url = $icon_url;
    }

    //----- Serialization -----//

    public function __serialize(): array{
        return [
            $this->text,
            $this->icon_url
        ];
    }

    public function __unserialize(array $data): void{
        [
            $this->text,
            $this->icon_url
        ] = $data;
    }
}