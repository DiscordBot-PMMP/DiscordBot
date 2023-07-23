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

use JaxkDev\DiscordBot\Communication\BinaryStream;
use Monolog\Logger;

class Socket{

    private Logger $logger;

    private string $address;
    private int $port;

    private bool $open = false;
    private \Socket $socket;

    public function __construct(Logger $logger, string $address, int $port){
        $this->logger = $logger->withName("ExternalThread.Socket");
        $this->address = $address;
        $this->port = $port;
        $socket = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if($socket === false){
            throw new SocketException("Failed to create socket: " . socket_strerror(socket_last_error()));
        }else{
            $this->socket = $socket;
        }
        if(@socket_set_nonblock($this->socket) === false){
            throw new SocketException("Failed to set socket to non-blocking: " . socket_strerror(socket_last_error()));
        }
    }

    public function open(): void{
        if(@socket_connect($this->socket, $this->address, $this->port) === false and ($e = socket_last_error()) !== SOCKET_EINPROGRESS){
            throw new SocketException("Failed to connect to socket: " . socket_strerror($e));
        }
        $s = [$this->socket];
        if(@socket_select($s, $s, $s, 0) === false){
            throw new SocketException("Failed to select socket: " . socket_strerror(socket_last_error()));
        }
        $this->logger->info("Socket connected to " . $this->address . ":" . $this->port . ".");
        $this->open = true;
    }

    public function close(string $reason = "Unknown"): void{
        if(!$this->open){
            $this->logger->warning("Cannot close a non-open socket.");
        }
        @socket_close($this->socket);
        $this->open = false;
        $this->logger->debug("Socket closed, reason: $reason");
    }

    public function write(BinaryStream $stream): void{
        if(!$this->open){
            throw new SocketException("Socket is not open.");
        }
        $data = new BinaryStream();
        $data->putString($stream->getBuffer());

        $sent = @socket_write($this->socket, $data->getBuffer());
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
        //Check int bounds to avoid memory overflow/attacks.
        if($length[1] > 1024 * 1024){
            $this->close("Data length exceeds 1MB.");
            throw new SocketException("Data length exceeds 1MB.");
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

    public function getAddress(): string{
        return $this->address;
    }

    public function getPort(): int{
        return $this->port;
    }
}