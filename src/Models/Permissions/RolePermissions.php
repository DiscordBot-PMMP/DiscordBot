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

namespace JaxkDev\DiscordBot\Models\Permissions;

use JaxkDev\DiscordBot\Communication\BinaryStream;

/** @extends Permissions<RolePermissions> */
class RolePermissions extends Permissions{

    /** @return Array<string, int> */
    static function getPossiblePermissions(): array{
        return array_merge(Permissions::ALL_PERMISSIONS, Permissions::ROLE_PERMISSIONS, Permissions::TEXT_PERMISSIONS,
            Permissions::VOICE_PERMISSIONS, Permissions::STAGE_PERMISSIONS);
    }

    public static function fromBinary(BinaryStream $stream): self{
        return new self((int)$stream->getString());
    }
}