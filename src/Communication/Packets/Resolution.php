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
use pocketmine\utils\BinaryStream;

class Resolution extends Packet{

    public const ID = 2;

    private int $pid;

    private bool $successful;

    private string $response;

    /** @var BinarySerializable[] */
    private array $data;

    /** @param BinarySerializable[] $data */
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
        $stream->putInt(strlen($this->response));
        $stream->put($this->response);
        $stream->putInt(0); //temp 0 model count.
        /*$stream->putInt(count($this->data));
        foreach($this->data as $model){
            //TODO Wait for models binary implementation.
            $serialized = $model->binarySerialize()->getBuffer();
            $stream->putInt(strlen($serialized));
            //TODO Write model ID (n).
            $stream->put($serialized);
        }*/
        return $stream;
    }

    public static function fromBinary(BinaryStream $stream): self{
        $uid = $stream->getInt();
        $pid = $stream->getInt();
        $successful = $stream->getBool();
        $responseSize = $stream->getInt();
        $response = $stream->get($responseSize);
        $modelCount = $stream->getInt();
        $models = [];
        for($i = 0; $i < $modelCount; $i++){
            $length = $stream->getInt() - 4;
            $modelID = $stream->getInt();
            $model = $stream->get($length);
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