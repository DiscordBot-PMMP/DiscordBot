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

namespace JaxkDev\DiscordBot\Communication\Packets\Discord;

use JaxkDev\DiscordBot\Communication\BinaryStream;
use JaxkDev\DiscordBot\Communication\Packets\Packet;
use JaxkDev\DiscordBot\Models\Channels\GuildChannel;

/** @extends Packet<ChannelCreate> */
class ChannelCreate extends Packet{

    public const ID = 37;

    private GuildChannel $channel;

    public function __construct(GuildChannel $channel, ?int $uid = null){
        parent::__construct($uid);
        $this->channel = $channel;
    }

    public function getChannel(): GuildChannel{
        return $this->channel;
    }

    public function binarySerialize(): BinaryStream{
        $stream = new BinaryStream();
        //TODO $stream->putSerializable($this->channel);
        return $stream;
    }

    public static function fromBinary(BinaryStream $stream): \JaxkDev\DiscordBot\Communication\BinarySerializable{
        return new self(
            //$stream->getSerializable(g)
        );
    }
}