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

namespace JaxkDev\DiscordBot\ExternalBot;

use JaxkDev\DiscordBot\Communication\BinaryStream;
use JaxkDev\DiscordBot\Communication\NetworkApi;
use JaxkDev\DiscordBot\Communication\Packets\External\Connect;
use JaxkDev\DiscordBot\Communication\Packets\External\Disconnect;
use JaxkDev\DiscordBot\Communication\Packets\Packet;
use JaxkDev\DiscordBot\Communication\Thread;
use JaxkDev\DiscordBot\Communication\ThreadStatus;
use JaxkDev\DiscordBot\ExternalBot\Socket\Socket;
use JaxkDev\DiscordBot\ExternalBot\Socket\SocketConnection;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Level as LoggerLevel;
use Monolog\Logger;

//Small testing socket.
class Client{

    private Thread $thread;

    private Logger $logger;

    private Socket $socket;
    private ?SocketConnection $connection = null;

    private int $lastTick = 0;

    public function __construct(Thread $thread){
        Packet::$UID_COUNT = 1000;

        $this->thread = $thread;

        //Setup logger.
        $this->logger = new Logger('ExternalThread');
        $handler = new RotatingFileHandler(
            \JaxkDev\DiscordBot\DATA_PATH . $this->thread->getConfig()['logging']['directory'] . DIRECTORY_SEPARATOR . "DiscordBot.log",
            $this->thread->getConfig()['logging']['max_files'],
            LoggerLevel::Debug
        );
        $handler->setFilenameFormat('{filename}-{date}', 'Y-m-d');
        $this->logger->setHandlers(array($handler));


        //Socket options.
        $host = (string)$this->thread->getConfig()["protocol"]["external"]["host"];
        $port = (int)$this->thread->getConfig()["protocol"]["external"]["port"];

        $this->logger->debug("Creating socket.");
        try{
            $this->socket = new Socket($this->logger, $host, $port);
        }catch(\RuntimeException $e){
            $this->logger->error("Failed to create socket: ".$e->getMessage());
            $this->thread->setStatus(ThreadStatus::STOPPED);
            exit(1);
        }

        $this->start();
    }

    private function start(): void{
        $this->getLogger()->debug("Opening socket.");
        $this->thread->setStatus(ThreadStatus::STARTING);
        try{
            $this->socket->open();
        }catch(\RuntimeException $e){
            $this->getLogger()->error("Failed to open socket: ".$e->getMessage());
            $this->thread->setStatus(ThreadStatus::STOPPED);
            exit(1);
        }

        $this->thread->setStatus(ThreadStatus::STARTED);

        $this->connectionLoop();
    }

    private function stop(): void{
        $this->getLogger()->notice("Stopping external client.");
        $this->thread->setStatus(ThreadStatus::STOPPING);
        try{
            if($this->connection !== null){
                $this->getLogger()->debug("Closing connection.");
                try{
                    $this->writeDataPacket(new Disconnect("Server closing."));
                    $this->closeConnection();
                }catch(\AssertionError){}
            }
            if($this->socket->isOpen()){
                $this->getLogger()->debug("Closing socket.");
                $this->socket->close();
            }
        }catch(\RuntimeException $e){
            $this->getLogger()->error("Failed to close socket: ".$e->getMessage());
        }
        $this->thread->setStatus(ThreadStatus::STOPPED);
        $this->logger->notice("External client stopped gracefully.");
        exit(0);
    }

    private function closeConnection(): void{
        if($this->connection === null){
            return;
        }
        $this->logger->debug("Closing main connection.");
        if($this->connection->isOpen()){
            $this->connection->close();
        }
        $this->connection = null;
    }

    /**
     * @param Packet<mixed> $packet
     * @throws \AssertionError
     */
    public function writeDataPacket(Packet $packet): void{
        if($this->connection === null){
            throw new \AssertionError("No connection to write data to.");
        }

        // packet id (Unsigned 16bit BE) + data (string)
        $stream = new BinaryStream();
        $stream->putShort($packet::SERIALIZE_ID);
        $stream->put($packet->binarySerialize()->getBuffer());
        try{
            $this->connection->write($stream);
        }catch(\RuntimeException $e){
            $this->closeConnection();
            throw new \AssertionError("Failed to write data.", 0, $e);
        }
    }

    /**
     * @return Packet<mixed>|null
     * @throws \AssertionError
     */
    public function readDataPacket(): ?Packet{
        if($this->connection === null){
            throw new \AssertionError("No connection to read data from.");
        }

        try{
            $stream = $this->connection->read();
        }catch(\RuntimeException $e){
            try{
                $this->writeDataPacket(new Disconnect("Failed to read data."));
                $this->closeConnection();
            }catch(\AssertionError){}
            throw new \AssertionError("Failed to read data.", 0, $e);
        }

        if($stream === null){
            //No data to read.
            return null;
        }

        try{
            $id = $stream->getShort();
        }catch(\RuntimeException){
            try{
                $this->writeDataPacket(new Disconnect("Failed to unpack packet id."));
                $this->closeConnection();
            }catch(\AssertionError){}
            throw new \AssertionError("Failed to unpack packet id.");
        }
        /** @var Packet<mixed>|null $packet */
        $packet = NetworkApi::getPacketClass($id);
        if($packet === null){
            try{
                $this->writeDataPacket(new Disconnect("Unknown packet id $id received."));
                $this->closeConnection();
            }catch(\AssertionError){}
            throw new \AssertionError("Unknown packet id received.");
        }

        try{
            /** @var Packet<mixed> $packet */
            $packet = $packet::fromBinary($stream);
        }catch(\RuntimeException $e){
            try{
                $this->writeDataPacket(new Disconnect("Failed to parse packet - " . $e->getMessage()));
                $this->closeConnection();
            }catch(\AssertionError){}
            throw new \AssertionError("Failed to parse packet.", 0, $e);
        }

        return $packet;
    }

    /**
     * Loop new connections until a valid verify packet is received.
     *
     * @return bool false if socket closed before a connection was made.
     */
    private function getConnection(): bool{
        while($this->socket->isOpen() and $this->connection === null){
            $this->checkStatus();
            try{
                $this->connection = $this->socket->accept();
            }catch(\RuntimeException $e){
                $this->getLogger()->error("Failed to accept connection: ".$e->getMessage());
                $this->stop();
            }
            usleep(50000);
        }

        return $this->connection !== null and $this->socket->isOpen();
    }

    /**
     * Loop that runs until the socket is closed or a verified connection is made (goes on to main loop).
     */
    private function connectionLoop(): void{
        while($this->socket->isOpen()){
            $this->checkStatus();
            if($this->connection === null){
                if(!$this->getConnection()){
                    break;
                }
            }
            try{
                $packet = $this->readDataPacket();
            }catch(\AssertionError){
                continue;
            }
            if($packet === null){
                //sleep for 1/20th of a second
                usleep(50000);
                continue;
            }
            if(!$packet instanceof Connect){
                $this->logger->debug("Invalid packet type received, expecting Connect packet. Closing connection.");
                try{
                    $this->writeDataPacket(new Disconnect("Invalid packet type, Expecting Connect packet."));
                    $this->closeConnection();
                }catch(\AssertionError){}
                continue;
            }
            $this->getLogger()->debug("Received connect packet.");
            if($packet->getVersion() !== NetworkApi::VERSION){
                $this->getLogger()->debug("Invalid version, expecting " . NetworkApi::VERSION . " got " . $packet->getVersion() . ". Closing connection.");
                try{
                    $this->writeDataPacket(new Disconnect("Invalid version, Expecting " . NetworkApi::VERSION . " got " . $packet->getVersion()));
                    $this->closeConnection();
                }catch(\AssertionError){}
                continue;
            }
            if($packet->getMagic() !== NetworkApi::MAGIC){
                $this->getLogger()->debug("Invalid magic, expecting " . NetworkApi::MAGIC . " got " . $packet->getMagic() . ". Closing connection.");
                try{
                    $this->writeDataPacket(new Disconnect("Invalid magic."));
                    $this->closeConnection();
                }catch(\AssertionError){}
                continue;
            }
            $this->getLogger()->debug("Connection established.");
            //Start the main loop, where we have an active verified connection.
            $this->loop();
        }
    }

    private function loop(): void{
        $this->thread->setStatus(ThreadStatus::RUNNING);
        while($this->connection?->isOpen() === true){
            $this->lastTick = (int)(microtime(true)*1000000);
            $this->checkStatus();

            $count = 0;
            do{
                try{
                    $packet = $this->readDataPacket();
                }catch(\AssertionError){
                    continue 2;
                }
                if($packet !== null){
                    $count += 1;
                    $this->thread->writeOutboundData($packet);
                }
            }while($packet !== null and $count < $this->thread->getConfig()["protocol"]["general"]["packets_per_tick"]);

            $packets = $this->thread->readInboundData($this->thread->getConfig()["protocol"]["general"]["packets_per_tick"]);
            foreach($packets as $data){
                try{
                    $this->writeDataPacket($data);
                }catch(\AssertionError){
                    continue 2;
                }
            }

            //Clear pending connections, we don't want to accept any new connections.
            $this->socket->clearPendingConnections();

            //sleep dynamically to keep up with the tick rate (1/20).
            $time = (int)(microtime(true)*1000000);
            if($time - $this->lastTick < 49000){
                usleep(50000 - ($time - $this->lastTick));
            }
            $this->lastTick = $time;
        }
        $this->logger->info("Lost connection to client, attempting to reconnect.");
        $this->thread->setStatus(ThreadStatus::STARTED);
    }

    private function checkStatus(): void{
        if($this->thread->getStatus() === ThreadStatus::STOPPING || $this->thread->getStatus() === ThreadStatus::STOPPED){
            $this->stop();
        }
    }

    public function getLogger(): Logger{
        return $this->logger;
    }
}