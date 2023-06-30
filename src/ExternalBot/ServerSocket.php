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

use JaxkDev\DiscordBot\Communication\NetworkApi;
use JaxkDev\DiscordBot\Communication\Packets\External\Connect;
use JaxkDev\DiscordBot\Communication\Packets\External\Disconnect;
use JaxkDev\DiscordBot\Communication\Packets\Packet;
use JaxkDev\DiscordBot\Communication\Thread;
use JaxkDev\DiscordBot\Communication\ThreadStatus;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Level as LoggerLevel;
use Monolog\Logger;
use Socket;

//Small testing socket.
class ServerSocket{

    private Thread $thread;

    private Logger $logger;

    private Socket $sock;

    private string $host;
    private int $port;

    private int $lastTick = 0;

    public function __construct(Thread $thread){
        $this->thread = $thread;

        //Socket options.
        $this->host = (string)$this->thread->getConfig()["protocol"]["external"]["host"];
        $this->port = (int)$this->thread->getConfig()["protocol"]["external"]["port"];

        //Setup logger.
        $this->logger = new Logger('DiscordThread-External');
        $handler = new RotatingFileHandler(
            \JaxkDev\DiscordBot\DATA_PATH . $this->thread->getConfig()['logging']['directory'] . DIRECTORY_SEPARATOR . "DiscordBot.log",
            $this->thread->getConfig()['logging']['max_files'],
            LoggerLevel::Debug
        );
        $handler->setFilenameFormat('{filename}-{date}', 'Y-m-d');
        $this->logger->setHandlers(array($handler));

        $this->getLogger()->debug("Creating socket.");
        $sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if($sock === false){
            throw new \RuntimeException("Failed to create socket.");
        }
        $this->getLogger()->debug("Setting socket to non-blocking.");
        $this->sock = $sock;
        if(socket_set_nonblock($this->sock) === false){
            socket_close($this->sock);
            throw new \RuntimeException("Failed to set socket to non-blocking.");
        }

        $this->getLogger()->debug("Socket created.");
        $this->start();
    }

    private function start(): void{
        $this->getLogger()->debug("Starting socket.");
        $this->thread->setStatus(ThreadStatus::STARTING);

        if(socket_bind($this->sock, $this->host, $this->port) === false){
            socket_close($this->sock);
            throw new \RuntimeException("Failed to bind on socket ({$this->host}:{$this->port})");
        }
        if(socket_listen($this->sock) === false){
            socket_close($this->sock);
            throw new \RuntimeException("Failed to listen on socket ({$this->host}:{$this->port})");
        }

        $this->thread->setStatus(ThreadStatus::STARTED);

        $this->verifyLoop();
    }

    private static function close(Socket $sock, ?string $message = null): void{
        var_dump("Closing socket: ".$message);
        $close = new Disconnect($message);
        try{
            self::writeDataPacket($sock, $close);
        }catch(\AssertionError){}
        @socket_close($sock);
    }

    /**
     * @throws \AssertionError
     */
    private static function writeDataPacket(Socket $sock, Packet|string $packet): void{
        if($packet instanceof Packet){
            $data = json_encode($packet, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            if($data === false){
                throw new \AssertionError("Failed to encode packet.");
            }
        }else{
            $data = $packet;
        }
        // data length (Unsigned 32bit BE) + packet id (Unsigned 16bit BE) + data (string)
        $buf = pack("Nn", strlen($data) + 2, $packet::ID) . $data;
        if(socket_write($sock, $buf) === false){
            throw new \AssertionError("Failed to write packet.");
        }
    }

    /**
     * @throws \AssertionError
     */
    private static function readDataPacket(Socket $sock, bool $block = false): ?Packet{
        do{
            $buf = socket_read($sock, 4);
        }while(($buf === false or $buf === "") and $block === true);
        if($buf === false or $buf === ""){
            //Nothing to read.
            return null;
        }
        $len = unpack("N", $buf);
        if($len === false){
            self::close($sock, "Failed to read data length.");
            throw new \AssertionError("Failed to unpack data length.");
        }
        $buf = socket_read($sock, $len[1]);
        if($buf === false or $buf === "" or strlen($buf) !== $len[1]){
            self::close($sock, "Failed to read data.");
            throw new \AssertionError("Failed to read data.");
        }

        $id = unpack("n", substr($buf, 0, 2));
        if($id === false){
            self::close($sock, "Failed to read packet id.");
            throw new \AssertionError("Failed to unpack packet id.");
        }
        $data = (array)json_decode(substr($buf, 2));

        /** @var ?Packet $packet */
        $packet = NetworkApi::getPacketClass($id[1]);
        if($packet === null){
            self::close($sock, "Unknown packet id $id[1] received.");
            throw new \AssertionError("Unknown packet id received.");
        }

        try{
            $packet = $packet::fromJson($data);
        }catch(\Throwable $e){
            self::close($sock, "Failed to parse packet - " . $e->getMessage());
            throw new \AssertionError("Failed to parse packet.", 0, $e);
        }

        return $packet;
    }

    /**
     * Loop new connections until a valid verify packet is received.
     */
    private function verifyLoop(): void{
        //Todo lower tick rate here, maybe 20 tps max.
        while(true){
            $client = socket_accept($this->sock);
            if($client === false) continue;
            socket_getpeername($client, $ip, $port);
            var_dump("New client from " . $ip . " on port " . $port . ", pending Connect packet.");
            while(true){
                //Wait for initial Packet.
                try{
                    $packet = self::readDataPacket($client, true);
                }catch(\AssertionError){
                    break;
                }
                if(!$packet instanceof Connect){
                    self::close($client, "Invalid packet type, Expecting Connect packet.");
                    break;
                }
                var_dump("Received Connect packet.");
                if($packet->getMagic() !== NetworkApi::MAGIC){
                    self::close($client, "Invalid network magic.");
                    break;
                }
                if($packet->getVersion() !== NetworkApi::VERSION){
                    self::close($client, "Invalid network version, expecting " . NetworkApi::VERSION . " got " . $packet->getVersion() . ".");
                    break;
                }
                var_dump("Client connected successfully.");

                $packet = new Connect(NetworkApi::VERSION, NetworkApi::MAGIC);
                try{
                    self::writeDataPacket($client, $packet);
                }catch(\AssertionError $e){
                    throw new \RuntimeException("Failed to send outbound Connect packet.", 0, $e);
                }
                $this->tickLoop($client);
                break;
            }
            var_dump("Waiting for new client.");
        }
    }

    /**
     * Loop the socket for packets.
     */
    private function tickLoop(Socket $client): void{
        $this->thread->setStatus(ThreadStatus::RUNNING);
        $this->lastTick = (int)(microtime(true)*1000000);
        while(true){

            //Read packets:

            $count = 0;
            do{
                try{
                    $packet = self::readDataPacket($client);
                }catch(\AssertionError){
                    return;
                }

                if($packet !== null){
                    $count += 1;
                    if($packet instanceof Connect){
                        self::close($client, "Invalid packet type, Not expecting connect packet.");
                        return;
                    }
                    if($packet instanceof Disconnect){
                        var_dump("External client disconnected, reason: ".$packet->getMessage());
                        socket_close($client);
                        return;
                    }
                    var_dump("Received packet: " . $packet::ID . "(" . $packet->getUID() . ") from external client.");
                    $this->thread->writeOutboundData($packet);
                }
            }while($packet !== null and $count < $this->thread->getConfig()["protocol"]["general"]["packets_per_tick"]);

            //Write packets:
            /** @var Packet[] $packets */
            $packets = $this->thread->readInboundData($this->thread->getConfig()["protocol"]["general"]["packets_per_tick"]);
            foreach($packets as $data){
                try{
                    var_dump("Sending packet: " . $data::ID . "(" . $data->getUID() . ") to external client.");
                    self::writeDataPacket($client, $data);
                }catch(\AssertionError $e){
                    var_dump("Failed to send outbound packet ". $data::ID . " " . $e->getMessage());
                }
            }

            $time = (int)(microtime(true)*1000000);
            //sleep dynamically to keep up with the tick rate (1/20).
            if($time - $this->lastTick < 49000){
                usleep(50000 - ($time - $this->lastTick));
            }
            $this->lastTick = $time;
        }
    }

    public function getLogger(): Logger{
        return $this->logger;
    }
}