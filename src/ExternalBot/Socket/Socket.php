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
use JaxkDev\DiscordBot\Communication\Packets\External\Disconnect;
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
        if(@socket_bind($this->socket, $this->address, $this->port) === false){
            throw new SocketException("Failed to bind socket: " . socket_strerror(socket_last_error()));
        }
        if(@socket_listen($this->socket, 1) === false){
            throw new SocketException("Failed to listen on socket: " . socket_strerror(socket_last_error()));
        }
        $this->logger->info("Socket listening on " . $this->address . ":" . $this->port . ".");
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

        $id = SocketConnection::$idCounter++;

        if(@socket_getpeername($client, $ip, $port)){
            $this->logger->info("New client ID: $id, accepted from " . $ip . " on port " . $port . ".");
        }else{
            $this->logger->info("New client ID: $id, accepted.");
        }
        return new SocketConnection($this->logger, $client, $id);
    }

    public function reject(): bool{
        if(!$this->open){
            return false;
        }

        $client = @socket_accept($this->socket);
        if($client === false and socket_last_error() !== SOCKET_EWOULDBLOCK){
            return false;
        }

        if($client === false){
            return false;
        }

        if(@socket_getpeername($client, $ip, $port)){
            $this->logger->debug("Rejecting client from " . $ip . " on port " . $port . ".");
        }else{
            $this->logger->debug("New client rejected.");
        }

        $packet = (new Disconnect("Connection refused."))->binarySerialize()->getBuffer();
        $stream = new BinaryStream();
        $stream->putInt(2 + strlen($packet));
        $stream->putShort(Disconnect::ID);
        $stream->put($packet);
        @socket_write($client, $stream->getBuffer());

        @socket_close($client);

        return true;
    }

    /**
     * Clears up to 10 pending connections.
     */
    public function clearPendingConnections(): void{
        if(!$this->open){
            return;
        }

        $count = 0;
        do{
            $rejected = $this->reject();
            $count += 1;
        }while($rejected and $count < 10);
    }

    public function close(): void{
        if($this->open){
            @socket_close($this->socket);
            $this->open = false;
            $this->logger->info("Socket closed.");
        }else{
            $this->logger->warning("Cannot close socket, socket is not open.");
        }
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