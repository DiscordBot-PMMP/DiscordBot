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

class Resolution extends Packet{

    public const ID = 2;

    private int $pid;

    private bool $successful;

    private string $response;

    private array $data;

    public function __construct(int $pid, bool $successful, string $response, array $data = [], int $UID = null){
        parent::__construct($UID);
        $this->pid = $pid;
        $this->successful = $successful;
        $this->response = $response;
        $this->data = $data;
    }

    public function getPid(): int{
        return $this->pid;
    }

    public function wasSuccessful(): bool{
        return $this->successful;
    }

    public function getResponse(): string{
        return $this->response;
    }

    public function getData(): array{
        return $this->data;
    }

    public function jsonSerialize(): array{
        return [
            "uid" => $this->UID,
            "pid" => $this->pid,
            "successful" => $this->successful,
            "response" => $this->response,
            "data" => json_encode($this->data)
        ];
    }

    public static function fromJson(array $data): self{
        return new self(
            $data["pid"],
            $data["successful"],
            $data["response"],
            (array)json_decode($data["data"], true),
            $data["uid"]
        );
    }
}