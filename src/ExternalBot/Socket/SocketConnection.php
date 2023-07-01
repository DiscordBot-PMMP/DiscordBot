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

namespace JaxkDev\DiscordBot\ExternalBot\Socket;

use Monolog\Logger;
use pocketmine\utils\BinaryStream;

/**
 * Represents a connection to the socket server (a client).
 */
class SocketConnection{

    public static int $idCounter = 1;
    private int $id;

    private Logger $logger;

    private \Socket $socket;

    private bool $open = true;

    public function __construct(Logger $logger, \Socket $socket, int $id){
        $this->id = $id;
        $this->logger = $logger->withName("ExternalThread.Socket.$this->id");
        $this->socket = $socket;
    }

    public function close(string $reason = "Unknown"): void{
        if(!$this->open){
            throw new SocketException("Socket is not open.");
        }
        @socket_close($this->socket);
        $this->open = false;
        $this->logger->debug("Socket closed, reason: $reason");
    }

    public function write(BinaryStream $stream): void{
        if(!$this->open){
            throw new SocketException("Socket is not open.");
        }
        //Network format: length (int32BE) + packet
        $packet = $stream->getBuffer();
        $stream = new BinaryStream();
        $stream->putInt(strlen($packet));
        $stream->put($packet);

        $sent = @socket_write($this->socket, $stream->getBuffer());
        if($sent === false){
            $this->close("Failed to send data to socket.");
            throw new SocketException("Failed to send data to socket: " . socket_strerror(socket_last_error()));
        }
    }

    public function read(): ?BinaryStream{
        if(!$this->open){
            throw new SocketException("Socket is not open.");
        }

        $length = @socket_read($this->socket, 4);

        if($length === false and ($e = socket_last_error()) !== SOCKET_EWOULDBLOCK){
            $this->close("Failed to read data from socket.");
            throw new SocketException("Failed to read data from socket: " . socket_strerror($e));
        }

        if($length === "" or $length === false){
            //No data to read.
            return null;
        }
        $length = unpack("N", $length);
        if($length === false){
            $this->close("Failed to unpack data from socket.");
            throw new SocketException("Failed to unpack data from socket.");
        }
        $data = @socket_read($this->socket, $length[1]);
        if($data === false){
            $this->close("Failed to read data from socket.");
            throw new SocketException("Failed to read data from socket: " . socket_strerror(socket_last_error()));
        }
        return new BinaryStream($data);
    }

    public function isOpen(): bool{
        return $this->open;
    }
}