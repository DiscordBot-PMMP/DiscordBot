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

namespace JaxkDev\DiscordBot\Models\Channels;

class CategoryChannel extends GuildChannel{

    public function __construct(string $name, int $position, string $guild_id, ?string $id = null){
        parent::__construct($name, $position, $guild_id, null, $id);
    }

    public function getCategoryId(): ?string{
        return null;
    }

    public function setCategoryId(?string $category_id): void{
        if($category_id !== null) throw new \AssertionError("Category channels cannot have categories");
    }

    //----- Serialization -----//

    public function __serialize(): array{
        return [
            $this->id,
            $this->name,
            $this->position,
            $this->member_permissions,
            $this->role_permissions,
            $this->guild_id
        ];
    }

    public function __unserialize(array $data): void{
        [
            $this->id,
            $this->name,
            $this->position,
            $this->member_permissions,
            $this->role_permissions,
            $this->guild_id
        ] = $data;
    }
}