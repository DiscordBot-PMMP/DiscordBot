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

class Socket{

    private Logger $logger;

    private string $address;
    private int $port;

    private bool $open = false;
    private ?\Socket $socket;

    public function __construct(Logger $logger, string $address, int $port){
        $this->logger = $logger;
        $this->address = $address;
        $this->port = $port;
        $this->socket = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if($this->socket === false){
            throw new SocketException("Failed to create socket: " . socket_strerror(socket_last_error()));
        }
        if(@socket_set_nonblock($this->socket) === false){
            throw new SocketException("Failed to set socket to non-blocking: " . socket_strerror(socket_last_error()));
        }
    }

    public function open(): void{
        if(@socket_bind($this->socket, $this->address, $this->port) === false){
            throw new SocketException("Failed to bind socket: " . socket_strerror(socket_last_error()));
        }
        if(@socket_listen($this->socket, 1) === false){
            throw new SocketException("Failed to listen on socket: " . socket_strerror(socket_last_error()));
        }
        var_dump("Socket listening on " . $this->address . ":" . $this->port . ".");
        $this->open = true;
    }

    public function accept(): ?SocketConnection{
        if(!$this->open){
            throw new SocketException("Socket is not open.");
        }

        $client = @socket_accept($this->socket);
        if($client === false and ($e = socket_last_error()) !== SOCKET_EWOULDBLOCK){
            throw new SocketException("Failed to accept client: " . $e);
        }

        if($client === false){
            return null;
        }
        if(@socket_getpeername($client, $ip, $port)){
            var_dump("New client accepted from " . $ip . " on port " . $port . ".");
        }else{
            var_dump("New client accepted.");
        }
        return new SocketConnection($this->logger, $client);
    }

    public function close(): void{
        if($this->socket !== null){
            @socket_close($this->socket);
            $this->socket = null;
        }
        $this->open = false;
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