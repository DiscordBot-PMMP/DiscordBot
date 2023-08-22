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
use JaxkDev\DiscordBot\Communication\NetworkApi;
use function count;
use function is_string;

final class Resolution extends Packet{

    public const SERIALIZE_ID = 2;

    private int $pid;

    private bool $successful;

    private string $response;

    /** @var BinarySerializable<mixed>[]|string[] */
    private array $data;

    /** @param BinarySerializable<mixed>[]|string[] $data */
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

    /** @return BinarySerializable<mixed>[]|string[] */
    public function getData(): array{
        return $this->data;
    }

    public function binarySerialize(): BinaryStream{
        $stream = new BinaryStream();
        $stream->putInt($this->getUID());
        $stream->putInt($this->pid);
        $stream->putBool($this->successful);
        $stream->putString($this->response);
        $stream->putInt(count($this->data));
        foreach($this->data as $data){
            if($data instanceof BinarySerializable && $this->successful === true){
                $stream->putShort($data::SERIALIZE_ID);
                $stream->putSerializable($data);
            }elseif(is_string($data) && $this->successful === false){
                $stream->putString($data);
            }else{
                throw new \AssertionError("Unknown data/success combo.");
            }
        }
        return $stream;
    }

    public static function fromBinary(BinaryStream $stream): self{
        $uid = $stream->getInt();
        $pid = $stream->getInt();
        $successful = $stream->getBool();
        $response = $stream->getString();
        if($successful){
            $modelCount = $stream->getInt();
            $models = [];
            for($i = 0; $i < $modelCount; $i++){
                $modelID = $stream->getShort();
                $modelClass = NetworkApi::getModelClass($modelID);
                if($modelClass === null){
                    throw new \AssertionError("Invalid model ID '{$modelID}'");
                }
                $models[] = $stream->getSerializable($modelClass);
            }
        }else{
            $models = $stream->getStringArray();
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