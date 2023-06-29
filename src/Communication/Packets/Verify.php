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

namespace JaxkDev\DiscordBot\Communication\Packets;

/**
 * Packet for external bot to verify its connection with the correct Version and MAGIC.
 * Only used on ExternalThread.
 */
class Verify extends Packet{

    private int $version;
    private int $magic;

    public function __construct(int $version, int $magic){
        parent::__construct();
        $this->version = $version;
        $this->magic = $magic;
    }

    public function getVersion(): int{
        return $this->version;
    }

    public function getMagic(): int{
        return $this->magic;
    }

    public function jsonSerialize(): array{
        return [
            "version" => $this->version,
            "magic" => $this->magic
        ];
    }

    public static function fromJson(array $data): Packet{
        return new self(
            $data["version"],
            $data["magic"]
        );
    }
}