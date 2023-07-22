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

use JaxkDev\DiscordBot\Communication\BinarySerializable;
use JaxkDev\DiscordBot\Communication\BinaryStream;

class Resolution extends Packet{

    public const SERIALIZE_ID = 4;

    private int $pid;

    private bool $successful;

    private string $response;

    /** @var BinarySerializable<mixed>[] */
    private array $data;

    /** @param BinarySerializable<mixed>[] $data */
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

    public function binarySerialize(): BinaryStream{
        $stream = new BinaryStream();
        $stream->putInt($this->UID);
        $stream->putInt($this->pid);
        $stream->putBool($this->successful);
        $stream->putString($this->response);
        $stream->putInt(count($this->data));
        foreach($this->data as $model){
            //TODO Have a think about identifying model type, do we need IDs?
            $stream->put($model->binarySerialize()->getBuffer());
        }
        return $stream;
    }

    public static function fromBinary(BinaryStream $stream): self{
        $uid = $stream->getInt();
        $pid = $stream->getInt();
        $successful = $stream->getBool();
        $response = $stream->getString();
        $modelCount = $stream->getInt();
        $models = [];
        for($i = 0; $i < $modelCount; $i++){
            $modelID = $stream->getShort();
            //TODO Wait for models binary implementation.
            //Deserialize from class $modelID.
            //$models[] = $model;
        }
        return new self(
            $pid,
            $successful,
            $response,
            $models,
            $uid
        );
    }
}