<?php
/*
 * DiscordBot, PocketMine-MP Plugin.
 *
 * Licensed under the Open Software License version 3.0 (OSL-3.0)
 * Copyright (C) 2020-2021 JaxkDev
 *
 * Twitter :: @JaxkDev
 * Discord :: JaxkDev#2698
 * Email   :: JaxkDev@gmail.com
 */

namespace JaxkDev\DiscordBot\Bot\Handlers;

use Discord\Discord;
use Discord\Parts\Channel\Channel as DiscordChannel;
use Discord\Parts\Channel\Message as DiscordMessage;
use Discord\Parts\Guild\Ban as DiscordBan;
use Discord\Parts\Guild\Guild as DiscordGuild;
use Discord\Parts\Guild\Invite as DiscordInvite;
use Discord\Parts\Guild\Role as DiscordRole;
use Discord\Parts\Permissions\RolePermission as DiscordRolePermission;
use Discord\Parts\User\Member as DiscordMember;
use Discord\Parts\User\User as DiscordUser;
use Discord\Parts\WebSockets\MessageReaction as DiscordMessageReaction;
use Discord\Parts\WebSockets\PresenceUpdate as DiscordPresenceUpdate;
use Discord\Parts\WebSockets\VoiceStateUpdate as DiscordVoiceStateUpdate;
use JaxkDev\DiscordBot\Bot\Client;
use JaxkDev\DiscordBot\Bot\ModelConverter;
use JaxkDev\DiscordBot\Communication\BotThread;
use JaxkDev\DiscordBot\Communication\Packets\Discord\ChannelPinsUpdate as ChannelPinsUpdatePacket;
use JaxkDev\DiscordBot\Communication\Packets\Discord\DiscordDataDump as DiscordDataDumpPacket;
use JaxkDev\DiscordBot\Communication\Packets\Discord\BanAdd as BanAddPacket;
use JaxkDev\DiscordBot\Communication\Packets\Discord\BanRemove as BanRemovePacket;
use JaxkDev\DiscordBot\Communication\Packets\Discord\ChannelCreate as ChannelCreatePacket;
use JaxkDev\DiscordBot\Communication\Packets\Discord\ChannelDelete as ChannelDeletePacket;
use JaxkDev\DiscordBot\Communication\Packets\Discord\ChannelUpdate as ChannelUpdatePacket;
use JaxkDev\DiscordBot\Communication\Packets\Discord\InviteCreate as InviteCreatePacket;
use JaxkDev\DiscordBot\Communication\Packets\Discord\InviteDelete as InviteDeletePacket;
use JaxkDev\DiscordBot\Communication\Packets\Discord\MemberJoin as MemberJoinPacket;
use JaxkDev\DiscordBot\Communication\Packets\Discord\MemberLeave as MemberLeavePacket;
use JaxkDev\DiscordBot\Communication\Packets\Discord\MemberUpdate as MemberUpdatePacket;
use JaxkDev\DiscordBot\Communication\Packets\Discord\MessageDelete as MessageDeletePacket;
use JaxkDev\DiscordBot\Communication\Packets\Discord\MessageReactionAdd as MessageReactionAddPacket;
use JaxkDev\DiscordBot\Communication\Packets\Discord\MessageReactionRemove as MessageReactionRemovePacket;
use JaxkDev\DiscordBot\Communication\Packets\Discord\MessageReactionRemoveAll as MessageReactionRemoveAllPacket;
use JaxkDev\DiscordBot\Communication\Packets\Discord\MessageReactionRemoveEmoji as MessageReactionRemoveEmojiPacket;
use JaxkDev\DiscordBot\Communication\Packets\Discord\MessageSent as MessageSentPacket;
use JaxkDev\DiscordBot\Communication\Packets\Discord\MessageUpdate as MessageUpdatePacket;
use JaxkDev\DiscordBot\Communication\Packets\Discord\PresenceUpdate as PresenceUpdatePacket;
use JaxkDev\DiscordBot\Communication\Packets\Discord\RoleCreate as RoleCreatePacket;
use JaxkDev\DiscordBot\Communication\Packets\Discord\RoleDelete as RoleDeletePacket;
use JaxkDev\DiscordBot\Communication\Packets\Discord\RoleUpdate as RoleUpdatePacket;
use JaxkDev\DiscordBot\Communication\Packets\Discord\ServerJoin as ServerJoinPacket;
use JaxkDev\DiscordBot\Communication\Packets\Discord\ServerLeave as ServerLeavePacket;
use JaxkDev\DiscordBot\Communication\Packets\Discord\ServerUpdate as ServerUpdatePacket;
use JaxkDev\DiscordBot\Communication\Packets\Discord\DiscordReady as DiscordReadyPacket;
use JaxkDev\DiscordBot\Communication\Packets\Discord\VoiceStateUpdate as VoiceStateUpdatePacket;
use Monolog\Logger;

class DiscordEventHandler{

    /** @var Client */
    private $client;

    /** @var Logger */
    private $logger;

    public function __construct(Client $client){
        $this->client = $client;
        $this->logger = $client->getLogger();
    }

    public function registerEvents(): void{
        $discord = $this->client->getDiscordClient();
        $discord->on("MESSAGE_CREATE", [$this, "onMessageCreate"]);
        $discord->on("MESSAGE_DELETE", [$this, "onMessageDelete"]);
        $discord->on("MESSAGE_UPDATE", [$this, "onMessageUpdate"]);  //AKA Edit

        $discord->on("GUILD_MEMBER_ADD", [$this, "onMemberJoin"]);
        $discord->on("GUILD_MEMBER_REMOVE", [$this, "onMemberLeave"]);
        $discord->on("GUILD_MEMBER_UPDATE", [$this, "onMemberUpdate"]);   //Includes Roles,nickname etc

        $discord->on("GUILD_CREATE", [$this, "onGuildJoin"]);
        $discord->on("GUILD_UPDATE", [$this, "onGuildUpdate"]);
        $discord->on("GUILD_DELETE", [$this, "onGuildLeave"]);

        $discord->on("CHANNEL_CREATE", [$this, "onChannelCreate"]);
        $discord->on("CHANNEL_UPDATE", [$this, "onChannelUpdate"]);
        $discord->on("CHANNEL_DELETE", [$this, "onChannelDelete"]);
        $discord->on("CHANNEL_PINS_UPDATE", [$this, "onChannelPinsUpdate"]);

        $discord->on("GUILD_ROLE_CREATE", [$this, "onRoleCreate"]);
        $discord->on("GUILD_ROLE_UPDATE", [$this, "onRoleUpdate"]);
        $discord->on("GUILD_ROLE_DELETE", [$this, "onRoleDelete"]);

        $discord->on("INVITE_CREATE", [$this, "onInviteCreate"]);
        $discord->on("INVITE_DELETE", [$this, "onInviteDelete"]);

        $discord->on("GUILD_BAN_ADD", [$this, "onBanAdd"]);
        $discord->on("GUILD_BAN_REMOVE", [$this, "onBanRemove"]);

        $discord->on("MESSAGE_REACTION_ADD", [$this, "onMessageReactionAdd"]);
        $discord->on("MESSAGE_REACTION_REMOVE", [$this, "onMessageReactionRemove"]);
        $discord->on("MESSAGE_REACTION_REMOVE_ALL", [$this, "onMessageReactionRemoveAll"]);
        $discord->on("MESSAGE_REACTION_REMOVE_EMOJI", [$this, "onMessageReactionRemoveEmoji"]);
    
        $discord->on("PRESENCE_UPDATE", [$this, "onPresenceUpdate"]);
        $discord->on("VOICE_STATE_UPDATE", [$this, "onVoiceStateUpdate"]);
    }

    /*
Some timing notes.
array(5) {
  ["server"]=>
  array(2) {
    [0]=>
    float(0.00259)
    [1]=>
    int(46)
  }
  ["channel"]=>
  array(2) {
    [0]=>
    float(0.33944)
    [1]=>
    int(2721)
  }
  ["role"]=>
  array(2) {
    [0]=>
    float(0.03372)
    [1]=>
    int(1497)
  }
  ["member"]=>
  array(2) {
    [0]=>
    float(0.11359)
    [1]=>
    int(436)
  }
  ["user"]=>
  array(2) {
    [0]=>
    float(0.00466)
    [1]=>
    int(259)
  }
}

array(5) {
  ["server"]=>
  array(2) {
    [0]=>
    float(0.012749999999999975)
    [1]=>
    int(46)
  }
  ["channel"]=>
  array(2) {
    [0]=>
    float(0.30067999999999995)
    [1]=>
    int(2721)
  }
  ["role"]=>
  array(2) {
    [0]=>
    float(0.03301)
    [1]=>
    int(1497)
  }
  ["member"]=>
  array(2) {
    [0]=>
    float(0.17436000000000001)
    [1]=>
    int(435)
  }
  ["user"]=>
  array(2) {
    [0]=>
    float(0.01252)
    [1]=>
    int(258)
  }
}
     */

    public function onReady(): void{
        //Checked frequently during data dump as this is the only time when it can cause the thread to hang during disable.
        $statusCheck = function(){
            if($this->client->getThread()->getStatus() !== BotThread::STATUS_STARTED){
                $this->logger->warning("Closing thread, unexpected state change.");
                $this->client->close();
            }
        };

        // Register all other events.
        $this->registerEvents();

        // Dump all discord data.
        $pk = new DiscordDataDumpPacket();
        $pk->setTimestamp(time());

        $this->logger->debug("Starting the data pack, please be patient.");
        $t = microtime(true);
        $mem = memory_get_usage(true);

        $client = $this->client->getDiscordClient();

        /** @var DiscordGuild $guild */
        foreach($client->guilds as $guild){
            $statusCheck();

            $pk->addServer(ModelConverter::genModelServer($guild));

            /** @var DiscordRolePermission $permissions */
            $permissions = $guild->members->offsetGet($client->id)->getPermissions();

            if($permissions->ban_members){
                /** @noinspection PhpUnhandledExceptionInspection */
                $guild->bans->freshen()->done(function() use ($guild){
                    $this->logger->debug("Successfully fetched ".sizeof($guild->bans)." bans from server '".
                        $guild->name."' (".$guild->id.")");
                    if(sizeof($guild->bans) === 0) return;
                    $pk = new DiscordDataDumpPacket();
                    $pk->setTimestamp(time());
                    /** @var DiscordBan $ban */
                    foreach($guild->bans as $ban){
                        $pk->addBan(ModelConverter::genModelBan($ban));
                    }
                    $this->client->getThread()->writeOutboundData($pk);
                }, function() use ($guild){
                    $this->logger->warning("Failed to fetch bans from server '".$guild->name."' (".$guild->id.")");
                });
            }else{
                $this->logger->notice("Cannot fetch bans from server '".$guild->name."' (".$guild->id.
                    "), Bot does not have 'ban_members' permission.");
            }

            /** @var DiscordChannel $channel */
            foreach($guild->channels as $channel){
                $c = ModelConverter::genModelChannel($channel);
                if($c !== null) $pk->addChannel($c);
            }

            /** @var DiscordRole $role */
            foreach($guild->roles as $role){
                $pk->addRole(ModelConverter::genModelRole($role));
            }

            $statusCheck();

            if($permissions->manage_guild){
                /** @noinspection PhpUnhandledExceptionInspection */
                $guild->invites->freshen()->done(function() use ($guild){
                    $this->logger->debug("Successfully fetched ".sizeof($guild->invites).
                        " invites from server '".$guild->name."' (".$guild->id.")");
                    if(sizeof($guild->invites) === 0) return;
                    $pk = new DiscordDataDumpPacket();
                    $pk->setTimestamp(time());
                    /** @var DiscordInvite $invite */
                    foreach($guild->invites as $invite){
                        $pk->addInvite(ModelConverter::genModelInvite($invite));
                    }
                    $this->client->getThread()->writeOutboundData($pk);
                }, function() use ($guild){
                    $this->logger->warning("Failed to fetch invites from server '".$guild->name."' (".$guild->id.")");
                });
            }else{
                $this->logger->notice("Cannot fetch invites from server '".$guild->name."' (".$guild->id.
                    "), Bot does not have 'manage_guild' permission.");
            }

            /** @var DiscordMember $member */
            foreach($guild->members as $member){
                $pk->addMember(ModelConverter::genModelMember($member));
            }
        }

        $statusCheck();

        /** @var DiscordUser $user */
        foreach($client->users as $user){
            $pk->addUser(ModelConverter::genModelUser($user));
        }

        //Very important to check status before overwriting, can cause dangerous behaviour.
        $statusCheck();

        $pk->setBotUser(ModelConverter::genModelUser($client->user));

        $this->logger->debug("Data pack took: ".round(microtime(true)-$t, 5)."s & ".
            round(((memory_get_usage(true)-$mem)/1024)/1024, 4)."mb of memory, Final size: ".$pk->getSize());

        $this->client->getThread()->writeOutboundData($pk);

        $this->client->getThread()->setStatus(BotThread::STATUS_READY);
        $this->logger->info("Client '".$client->username."#".$client->discriminator."' ready.");

        $this->client->getThread()->writeOutboundData(new DiscordReadyPacket());
        $this->client->getCommunicationHandler()->sendHeartbeat();
    }

    public function onVoiceStateUpdate(DiscordVoiceStateUpdate $ds): void{
        if($ds->guild_id === null) return; //DM's
        $this->client->getThread()->writeOutboundData(new VoiceStateUpdatePacket($ds->guild_id.".".$ds->user_id,
            ModelConverter::genModelVoiceState($ds)));
    }

    public function onPresenceUpdate(DiscordPresenceUpdate $presenceUpdate): void{
        $clientStatus = [
            "desktop" => $presenceUpdate->client_status->desktop??null,
            "mobile" => $presenceUpdate->client_status->mobile??null,
            "web" => $presenceUpdate->client_status->web??null
        ];
        $activities = [];
        foreach($presenceUpdate->activities as $activity){
            $activities[] = ModelConverter::genModelActivity($activity);
        }
        $this->client->getThread()->writeOutboundData(new PresenceUpdatePacket($presenceUpdate->guild_id.".".$presenceUpdate->user->id,
            $presenceUpdate->status, $clientStatus, $activities));
    }

    public function onMessageCreate(DiscordMessage $message, Discord $discord): void{
        if(!$this->checkMessage($message)) return;
        //if($message->author->id === "305060807887159296") $message->react("❤️");
        //Dont ask questions...
        $packet = new MessageSentPacket(ModelConverter::genModelMessage($message));
        $this->client->getThread()->writeOutboundData($packet);
    }

    public function onMessageUpdate(DiscordMessage $message, Discord $discord): void{
        if(!$this->checkMessage($message)) return;
        $packet = new MessageUpdatePacket(ModelConverter::genModelMessage($message));
        $this->client->getThread()->writeOutboundData($packet);
    }

    /**
     * @param DiscordMessage|\stdClass $data
     * @param Discord                  $discord
     */
    public function onMessageDelete($data, Discord $discord): void{
        if($data instanceof DiscordMessage){
            $message = ModelConverter::genModelMessage($data);
        }else{
            $message = [
                "message_id" => $data->id,
                "channel_id" => $data->channel_id,
                "server_id" => $data->guild_id
            ];
        }
        $packet = new MessageDeletePacket($message);
        $this->client->getThread()->writeOutboundData($packet);
    }

    public function onMessageReactionAdd(DiscordMessageReaction $reaction): void{
        $packet = new MessageReactionAddPacket($reaction->message_id, $reaction->emoji->name,
            $reaction->guild_id.".".$reaction->user_id, $reaction->channel_id);
        $this->client->getThread()->writeOutboundData($packet);
    }

    public function onMessageReactionRemove(DiscordMessageReaction $reaction): void{
        $packet = new MessageReactionRemovePacket($reaction->message_id, $reaction->emoji->name,
            $reaction->guild_id.".".$reaction->user_id, $reaction->channel_id);
        $this->client->getThread()->writeOutboundData($packet);
    }

    public function onMessageReactionRemoveAll(DiscordMessageReaction $reaction): void{
        $packet = new MessageReactionRemoveAllPacket($reaction->message_id, $reaction->channel_id);
        $this->client->getThread()->writeOutboundData($packet);
    }

    /** @var \stdClass{"message_id": string, "emoji": \stdClass{"name": string}, "channel_id": string, "guild_id": string} $data */
    public function onMessageReactionRemoveEmoji(\stdClass $data): void{
        $this->client->getThread()->writeOutboundData(new MessageReactionRemoveEmojiPacket($data->message_id, $data->channel_id, $data->emoji->name));
    }

    public function onMemberJoin(DiscordMember $member, Discord $discord): void{
        $packet = new MemberJoinPacket(ModelConverter::genModelMember($member), ModelConverter::genModelUser($member->user));
        $this->client->getThread()->writeOutboundData($packet);
    }

    public function onMemberUpdate(DiscordMember $member, Discord $discord): void{
        $packet = new MemberUpdatePacket(ModelConverter::genModelMember($member));
        $this->client->getThread()->writeOutboundData($packet);
    }

    public function onMemberLeave(DiscordMember $member, Discord $discord): void{
        $packet = new MemberLeavePacket($member->guild_id.".".$member->id);
        $this->client->getThread()->writeOutboundData($packet);
    }

    public function onGuildJoin(DiscordGuild $guild, Discord $discord): void{
        $channels = [];
        /** @var DiscordChannel $channel */
        foreach($guild->channels as $channel){
            $c = ModelConverter::genModelChannel($channel);
            if($c !== null) $channels[] = $c;
        }
        $roles = [];
        /** @var DiscordRole $role */
        foreach($guild->roles as $role){
            $roles[] = ModelConverter::genModelRole($role);
        }
        $members = [];
        /** @var DiscordMember $member */
        foreach($guild->members as $member){
            $members[] = ModelConverter::genModelMember($member);
        }

        $packet = new ServerJoinPacket(ModelConverter::genModelServer($guild), $channels, $members, $roles);
        $this->client->getThread()->writeOutboundData($packet);
    }

    public function onGuildUpdate(DiscordGuild $guild, Discord $discord): void{
        $packet = new ServerUpdatePacket(ModelConverter::genModelServer($guild));
        $this->client->getThread()->writeOutboundData($packet);
    }

    public function onGuildLeave(DiscordGuild $guild, Discord $discord): void{
        $packet = new ServerLeavePacket($guild->id);
        $this->client->getThread()->writeOutboundData($packet);
    }

    public function onChannelCreate(DiscordChannel $channel, Discord $discord): void{
        $c = ModelConverter::genModelChannel($channel);
        if($c === null) return;
        $packet = new ChannelCreatePacket($c);
        $this->client->getThread()->writeOutboundData($packet);
    }

    public function onChannelUpdate(DiscordChannel $channel, Discord $discord): void{
        $c = ModelConverter::genModelChannel($channel);
        if($c === null) return;
        $packet = new ChannelUpdatePacket($c);
        $this->client->getThread()->writeOutboundData($packet);
    }

    public function onChannelDelete(DiscordChannel $channel, Discord $discord): void{
        $packet = new ChannelDeletePacket($channel->id);
        $this->client->getThread()->writeOutboundData($packet);
    }

    /** $data ["last_pin_timestamp" => string, "channel_id" => string, "guild_id" => string] */
    public function onChannelPinsUpdate(\stdClass $data): void{
        $packet = new ChannelPinsUpdatePacket($data->channel_id);
        $this->client->getThread()->writeOutboundData($packet);
    }

    public function onRoleCreate(DiscordRole $role, Discord $discord): void{
        $packet = new RoleCreatePacket(ModelConverter::genModelRole($role));
        $this->client->getThread()->writeOutboundData($packet);
    }

    public function onRoleUpdate(DiscordRole $role, Discord $discord): void{
        $packet = new RoleUpdatePacket(ModelConverter::genModelRole($role));
        $this->client->getThread()->writeOutboundData($packet);
    }

    public function onRoleDelete(DiscordRole $role, Discord $discord): void{
        $packet = new RoleDeletePacket($role->id);
        $this->client->getThread()->writeOutboundData($packet);
    }

    public function onInviteCreate(DiscordInvite $invite, Discord $discord): void{
        $packet = new InviteCreatePacket(ModelConverter::genModelInvite($invite));
        $this->client->getThread()->writeOutboundData($packet);
    }

    /**
     * @param \stdClass $invite {channel_id: str, guild_id: str, code: str}
     * @param Discord   $discord
     */
    public function onInviteDelete(\stdClass $invite, Discord $discord): void{
        $packet = new InviteDeletePacket($invite->code);
        $this->client->getThread()->writeOutboundData($packet);
    }

    public function onBanAdd(DiscordBan $ban, Discord $discord): void{
        //No reason unless you freshen bans which is only possible with ban_members permission.
        $g = $ban->guild;
        /** @var DiscordMember|null $m */
        $m = $g->members->offsetGet($discord->user->id);
        if($m !== null and $m->getPermissions()->ban_members){
            //Get ban reason.
            /** @noinspection PhpUnhandledExceptionInspection */ //Impossible.
            $g->bans->freshen()->done(function() use ($ban, $g){
                //Got latest bans so we can fetch reason unless it was unbanned in like 0.01s
                /** @var DiscordBan|null $b */
                $b = $g->bans->offsetGet($ban->user_id);
                if($b !== null){
                    $this->logger->debug("Successfully fetched bans, attached reason to new ban event.");
                    $packet = new BanAddPacket(ModelConverter::genModelBan($b));
                    $this->client->getThread()->writeOutboundData($packet);
                }else{
                    $this->logger->debug("No ban after freshen ??? (IMPORTANT LOGIC ERROR)");
                    $packet = new BanAddPacket(ModelConverter::genModelBan($ban));
                    $this->client->getThread()->writeOutboundData($packet);
                }
            }, function() use ($ban){
                //Failed so just send ban with no reason.
                $this->logger->debug("Failed to fetch bans even with ban_members permission, using old ban object.");
                $packet = new BanAddPacket(ModelConverter::genModelBan($ban));
                $this->client->getThread()->writeOutboundData($packet);
            });
        }else{
            $this->logger->debug("Bot does not have ban_members permission so no reason could be attached to this ban.");
            $packet = new BanAddPacket(ModelConverter::genModelBan($ban));
            $this->client->getThread()->writeOutboundData($packet);
        }
    }

    public function onBanRemove(DiscordBan $ban, Discord $discord): void{
        $packet = new BanRemovePacket($ban->guild_id.".".$ban->user_id);
        $this->client->getThread()->writeOutboundData($packet);
    }

    /**
     * Checks if we handle this type of message in this type of channel.
     * @param DiscordMessage $message
     * @return bool
     */
    private function checkMessage(DiscordMessage $message): bool{
        // Can be user if bot doesnt have correct intents enabled on discord developer dashboard.
        if($message->author === null) return false; //"Shouldn't" happen now...
        if($message->author->id === $this->client->getDiscordClient()->id) return false;

        // Other types of messages not used right now.
        if($message->type !== DiscordMessage::TYPE_NORMAL and $message->type !== DiscordMessage::TYPE_REPLY) return false;
        if(($message->content??"") === "" and $message->embeds->count() === 0 and sizeof($message->attachments) === 0) return false;
        // ^ Spotify/Games etc

        return true;
    }
}