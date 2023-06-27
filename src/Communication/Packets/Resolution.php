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

namespace JaxkDev\DiscordBot\Communication\Packets;

class Resolution extends Packet{

    private int $pid;

    private bool $successful;

    private string $response;

    private array $data;

    public function __construct(int $pid, bool $successful, string $response, array $data = []){
        parent::__construct();
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

    public function __serialize(): array{
        return [
            $this->pid,
            $this->successful,
            $this->response,
            $this->data
        ];
    }

    public function __unserialize(array $data): void{
        [
            $this->pid,
            $this->successful,
            $this->response,
            $this->data
        ] = $data;
    }
}