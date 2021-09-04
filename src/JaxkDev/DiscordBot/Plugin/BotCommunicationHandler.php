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

namespace JaxkDev\DiscordBot\Plugin;

use JaxkDev\DiscordBot\Communication\Packets\Resolution as ResolutionPacket;
use JaxkDev\DiscordBot\Communication\Packets\Discord\DiscordDataDump as DiscordDataDumpPacket;
use JaxkDev\DiscordBot\Communication\Packets\Discord\BanAdd as BanAddPacket;
use JaxkDev\DiscordBot\Communication\Packets\Discord\BanRemove as BanRemovePacket;
use JaxkDev\DiscordBot\Communication\Packets\Discord\ChannelCreate as ChannelCreatePacket;
use JaxkDev\DiscordBot\Communication\Packets\Discord\ChannelDelete as ChannelDeletePacket;
use JaxkDev\DiscordBot\Communication\Packets\Discord\ChannelUpdate as ChannelUpdatePacket;
use JaxkDev\DiscordBot\Communication\Packets\Discord\ChannelPinsUpdate as ChannelPinsUpdatePacket;
use JaxkDev\DiscordBot\Communication\Packets\Discord\InviteCreate as InviteCreatePacket;
use JaxkDev\DiscordBot\Communication\Packets\Discord\InviteDelete as InviteDeletePacket;
use JaxkDev\DiscordBot\Communication\Packets\Discord\MemberJoin as MemberJoinPacket;
use JaxkDev\DiscordBot\Communication\Packets\Discord\MemberLeave as MemberLeavePacket;
use JaxkDev\DiscordBot\Communication\Packets\Discord\MemberUpdate as MemberUpdatePacket;
use JaxkDev\DiscordBot\Communication\Packets\Discord\MessageDelete as MessageDeletePacket;
use JaxkDev\DiscordBot\Communication\Packets\Discord\MessageSent as MessageSentPacket;
use JaxkDev\DiscordBot\Communication\Packets\Discord\MessageUpdate as MessageUpdatePacket;
use JaxkDev\DiscordBot\Communication\Packets\Discord\MessageReactionAdd as MessageReactionAddPacket;
use JaxkDev\DiscordBot\Communication\Packets\Discord\MessageReactionRemove as MessageReactionRemovePacket;
use JaxkDev\DiscordBot\Communication\Packets\Discord\MessageReactionRemoveAll as MessageReactionRemoveAllPacket;
use JaxkDev\DiscordBot\Communication\Packets\Discord\MessageReactionRemoveEmoji as MessageReactionRemoveEmojiPacket;
use JaxkDev\DiscordBot\Communication\Packets\Discord\PresenceUpdate as PresenceUpdatePacket;
use JaxkDev\DiscordBot\Communication\Packets\Discord\RoleCreate as RoleCreatePacket;
use JaxkDev\DiscordBot\Communication\Packets\Discord\RoleDelete as RoleDeletePacket;
use JaxkDev\DiscordBot\Communication\Packets\Discord\RoleUpdate as RoleUpdatePacket;
use JaxkDev\DiscordBot\Communication\Packets\Discord\ServerJoin as ServerJoinPacket;
use JaxkDev\DiscordBot\Communication\Packets\Discord\ServerLeave as ServerLeavePacket;
use JaxkDev\DiscordBot\Communication\Packets\Discord\ServerUpdate as ServerUpdatePacket;
use JaxkDev\DiscordBot\Communication\Packets\Discord\DiscordReady as DiscordReadyPacket;
use JaxkDev\DiscordBot\Communication\Packets\Discord\VoiceStateUpdate as VoiceStateUpdatePacket;
use JaxkDev\DiscordBot\Communication\Packets\Heartbeat as HeartbeatPacket;
use JaxkDev\DiscordBot\Communication\Packets\Packet;
use JaxkDev\DiscordBot\Models\Activity;
use JaxkDev\DiscordBot\Models\Channels\TextChannel;
use JaxkDev\DiscordBot\Models\Channels\VoiceChannel;
use JaxkDev\DiscordBot\Models\Member;
use JaxkDev\DiscordBot\Plugin\Events\BanCreated as BanCreatedEvent;
use JaxkDev\DiscordBot\Plugin\Events\BanDeleted as BanDeletedEvent;
use JaxkDev\DiscordBot\Plugin\Events\ChannelDeleted as ChannelDeletedEvent;
use JaxkDev\DiscordBot\Plugin\Events\ChannelPinsUpdated as ChannelPinsUpdatedEvent;
use JaxkDev\DiscordBot\Plugin\Events\ChannelUpdated as ChannelUpdatedEvent;
use JaxkDev\DiscordBot\Plugin\Events\InviteCreated as InviteCreatedEvent;
use JaxkDev\DiscordBot\Plugin\Events\InviteDeleted as InviteDeletedEvent;
use JaxkDev\DiscordBot\Plugin\Events\MemberJoined as MemberJoinedEvent;
use JaxkDev\DiscordBot\Plugin\Events\MemberLeft as MemberLeftEvent;
use JaxkDev\DiscordBot\Plugin\Events\MemberUpdated as MemberUpdatedEvent;
use JaxkDev\DiscordBot\Plugin\Events\MessageDeleted as MessageDeletedEvent;
use JaxkDev\DiscordBot\Plugin\Events\MessageReactionAdd as MessageReactionAddEvent;
use JaxkDev\DiscordBot\Plugin\Events\MessageReactionRemove as MessageReactionRemoveEvent;
use JaxkDev\DiscordBot\Plugin\Events\MessageReactionRemoveAll as MessageReactionRemoveAllEvent;
use JaxkDev\DiscordBot\Plugin\Events\MessageReactionRemoveEmoji as MessageReactionRemoveEmojiEvent;
use JaxkDev\DiscordBot\Plugin\Events\MessageSent as MessageSentEvent;
use JaxkDev\DiscordBot\Plugin\Events\MessageUpdated as MessageUpdatedEvent;
use JaxkDev\DiscordBot\Plugin\Events\DiscordReady as DiscordReadyEvent;
use JaxkDev\DiscordBot\Plugin\Events\PresenceUpdated as PresenceUpdatedEvent;
use JaxkDev\DiscordBot\Plugin\Events\RoleCreated as RoleCreatedEvent;
use JaxkDev\DiscordBot\Plugin\Events\RoleDeleted as RoleDeletedEvent;
use JaxkDev\DiscordBot\Plugin\Events\RoleUpdated as RoleUpdatedEvent;
use JaxkDev\DiscordBot\Plugin\Events\ServerDeleted as ServerDeletedEvent;
use JaxkDev\DiscordBot\Plugin\Events\ServerJoined as ServerJoinedEvent;
use JaxkDev\DiscordBot\Plugin\Events\ServerUpdated as ServerUpdatedEvent;
use JaxkDev\DiscordBot\Plugin\Events\VoiceChannelMemberJoined as VoiceChannelMemberJoinedEvent;
use JaxkDev\DiscordBot\Plugin\Events\VoiceChannelMemberLeft as VoiceChannelMemberLeftEvent;
use JaxkDev\DiscordBot\Plugin\Events\VoiceChannelMemberMoved as VoiceChannelMemberMovedEvent;
use JaxkDev\DiscordBot\Plugin\Events\VoiceStateUpdated as VoiceStateUpdatedEvent;

class BotCommunicationHandler{

    /** @var Main */
    private $plugin;

    /** @var float|null */
    private $lastHeartbeat = null;

    public function __construct(Main $plugin){
        $this->plugin = $plugin;
    }

    public function handle(Packet $packet): void{
        // If's instances instead of ID switching due to phpstan/types.
        if($packet instanceof ResolutionPacket){
            ApiResolver::handleResolution($packet);
            return;
        }
        if($packet instanceof HeartbeatPacket){
            $this->lastHeartbeat = $packet->getHeartbeat();
            return;
        }

        if($packet instanceof PresenceUpdatePacket) $this->handlePresenceUpdate($packet);
        elseif($packet instanceof VoiceStateUpdatePacket) $this->handleVoiceStateUpdate($packet);
        elseif($packet instanceof MemberJoinPacket) $this->handleMemberJoin($packet);
        elseif($packet instanceof MemberLeavePacket) $this->handleMemberLeave($packet);
        elseif($packet instanceof MemberUpdatePacket) $this->handleMemberUpdate($packet);
        elseif($packet instanceof MessageSentPacket) $this->handleMessageSent($packet);
        elseif($packet instanceof MessageUpdatePacket) $this->handleMessageUpdate($packet);
        elseif($packet instanceof MessageDeletePacket) $this->handleMessageDelete($packet);
        elseif($packet instanceof MessageReactionAddPacket) $this->handleMessageReactionAdd($packet);
        elseif($packet instanceof MessageReactionRemovePacket) $this->handleMessageReactionRemove($packet);
        elseif($packet instanceof MessageReactionRemoveAllPacket) $this->handleMessageReactionRemoveAll($packet);
        elseif($packet instanceof MessageReactionRemoveEmojiPacket) $this->handleMessageReactionRemoveEmoji($packet);
        elseif($packet instanceof ChannelCreatePacket) $this->handleChannelCreate($packet);
        elseif($packet instanceof ChannelUpdatePacket) $this->handleChannelUpdate($packet);
        elseif($packet instanceof ChannelDeletePacket) $this->handleChannelDelete($packet);
        elseif($packet instanceof ChannelPinsUpdatePacket) $this->handleChannelPinsUpdate($packet);
        elseif($packet instanceof RoleCreatePacket) $this->handleRoleCreate($packet);
        elseif($packet instanceof RoleUpdatePacket) $this->handleRoleUpdate($packet);
        elseif($packet instanceof RoleDeletePacket) $this->handleRoleDelete($packet);
        elseif($packet instanceof InviteCreatePacket) $this->handleInviteCreate($packet);
        elseif($packet instanceof InviteDeletePacket) $this->handleInviteDelete($packet);
        elseif($packet instanceof BanAddPacket) $this->handleBanAdd($packet);
        elseif($packet instanceof BanRemovePacket) $this->handleBanRemove($packet);
        elseif($packet instanceof ServerJoinPacket) $this->handleServerJoin($packet);
        elseif($packet instanceof ServerLeavePacket) $this->handleServerLeave($packet);
        elseif($packet instanceof ServerUpdatePacket) $this->handleServerUpdate($packet);
        elseif($packet instanceof DiscordDataDumpPacket) $this->handleDataDump($packet);
        elseif($packet instanceof DiscordReadyPacket) $this->handleReady();
    }

    private function handleReady(): void{
        //Default activity, Feel free to change activity after ReadyEvent.
        $ac = new Activity("PocketMine-MP v".\pocketmine\VERSION." | DiscordBot ".\JaxkDev\DiscordBot\VERSION, Activity::TYPE_PLAYING);
        $this->plugin->getApi()->updateBotPresence($ac, Member::STATUS_IDLE)->otherwise(function(ApiRejection $a){
            $this->plugin->getLogger()->logException($a);
        });

        (new DiscordReadyEvent($this->plugin))->call();
    }

    //Uses the storage (aka cache)
    private function handleVoiceStateUpdate(VoiceStateUpdatePacket $packet): void{
        $member = Storage::getMember($packet->getMemberId());
        if($member === null){
            throw new \AssertionError("Member '{$packet->getMemberId()}' not found in storage.");
        }
        $state = $packet->getVoiceState();
        if($state->getChannelId() === null){
            $channel = Storage::getMembersVoiceChannel($packet->getMemberId());
            if($channel === null){
                throw new \AssertionError("Voice Channel '{$state->getChannelId()}' not found in storage.");
            }
            (new VoiceChannelMemberLeftEvent($this->plugin, $member, $channel))->call();
            $member->setVoiceState(null);
            $members = $channel->getMembers();
            if(($key = array_search($packet->getMemberId(), $members)) !== false) {
                unset($members[$key]);
            }
            $channel->setMembers($members);
            Storage::updateMember($member);
            Storage::updateChannel($channel);
            Storage::unsetMembersVoiceChannel($packet->getMemberId());
        }else{
            $channel = Storage::getChannel($state->getChannelId());
            if($channel === null){
                throw new \AssertionError("Channel '{$state->getChannelId()}' not found in storage.");
            }
            if(!$channel instanceof VoiceChannel){
                throw new \AssertionError("Channel '{$state->getChannelId()}' not a voice channel.");
            }
            if(in_array($packet->getMemberId(), $channel->getMembers())){
                //Member did not leave/join/transfer voice channel but muted/deaf/self_muted/self_deafen etc.
                (new VoiceStateUpdatedEvent($this->plugin, $member, $state))->call();
                $member->setVoiceState($packet->getVoiceState());
                Storage::updateMember($member);
            }else{
                if($channel->getMemberLimit() !== 0 and sizeof($channel->getMembers()) >= $channel->getMemberLimit()){
                    //Shouldn't ever happen.
                    throw new \AssertionError("Channel '{$state->getChannelId()}' shouldn't have room for this member.");
                }
                $previous = Storage::getMembersVoiceChannel($packet->getMemberId());
                if($previous !== null and $previous->getId() !== $state->getChannelId()){
                    (new VoiceChannelMemberMovedEvent($this->plugin, $member, $previous, $channel, $state))->call();
                    $members = $previous->getMembers();
                    if(($key = array_search($packet->getMemberId(), $members)) !== false) {
                        unset($members[$key]);
                    }
                    $previous->setMembers($members);
                    Storage::updateChannel($previous);
                }else{
                    (new VoiceChannelMemberJoinedEvent($this->plugin, $member, $channel, $state))->call();
                }
                $member->setVoiceState($packet->getVoiceState());
                $members = $channel->getMembers();
                $members[] = $packet->getMemberId();
                $channel->setMembers($members);
                Storage::updateMember($member);
                Storage::updateChannel($channel);
                Storage::setMembersVoiceChannel($packet->getMemberId(), $state->getChannelId());
            }
        }
    }

    private function handlePresenceUpdate(PresenceUpdatePacket $packet): void{
        $member = Storage::getMember($packet->getMemberId());
        if($member === null){
            throw new \AssertionError("Member '{$packet->getMemberID()}' not found in storage.");
        }
        (new PresenceUpdatedEvent($this->plugin, $member, $packet->getStatus(), $packet->getClientStatus(), $packet->getActivities()))->call();
        $member->setStatus($packet->getStatus());
        $member->setClientStatus($packet->getClientStatus());
        $member->setActivities($packet->getActivities());
        Storage::updateMember($member);
    }

    private function handleMessageSent(MessageSentPacket $packet): void{
        (new MessageSentEvent($this->plugin, $packet->getMessage()))->call();
    }

    private function handleMessageUpdate(MessageUpdatePacket $packet): void{
        (new MessageUpdatedEvent($this->plugin, $packet->getMessage()))->call();
    }

    private function handleMessageDelete(MessageDeletePacket $packet): void{
        (new MessageDeletedEvent($this->plugin, $packet->getMessage()))->call();
    }

    private function handleMessageReactionAdd(MessageReactionAddPacket $packet): void{
        $channel = Storage::getChannel($packet->getChannelId());
        if($channel === null){
            throw new \AssertionError("Channel '{$packet->getChannelId()}' does not exist in storage.");
        }
        $member = Storage::getMember($packet->getMemberId());
        if($member === null){
            throw new \AssertionError("Member '{$packet->getMemberId()}' does not exist in storage.");
        }
        (new MessageReactionAddEvent($this->plugin, $packet->getEmoji(), $packet->getMessageId(), $channel, $member))->call();
    }

    private function handleMessageReactionRemove(MessageReactionRemovePacket $packet): void{
        $channel = Storage::getChannel($packet->getChannelId());
        if($channel === null){
            throw new \AssertionError("Channel '{$packet->getChannelId()}' does not exist in storage.");
        }
        $member = Storage::getMember($packet->getMemberId());
        if($member === null){
            throw new \AssertionError("Member '{$packet->getMemberId()}' does not exist in storage.");
        }
        (new MessageReactionRemoveEvent($this->plugin, $packet->getEmoji(), $packet->getMessageId(), $channel, $member))->call();
    }

    private function handleMessageReactionRemoveAll(MessageReactionRemoveAllPacket $packet): void{
        $channel = Storage::getChannel($packet->getChannelId());
        if($channel === null){
            throw new \AssertionError("Channel '{$packet->getChannelId()}' does not exist in storage.");
        }
        (new MessageReactionRemoveAllEvent($this->plugin, $packet->getMessageId(), $channel))->call();
    }

    private function handleMessageReactionRemoveEmoji(MessageReactionRemoveEmojiPacket $packet): void{
        $channel = Storage::getChannel($packet->getChannelId());
        if($channel === null){
            throw new \AssertionError("Channel '{$packet->getChannelId()}' does not exist in storage.");
        }
        (new MessageReactionRemoveEmojiEvent($this->plugin, $packet->getEmoji(), $packet->getMessageId(), $channel))->call();
    }

    private function handleChannelCreate(ChannelCreatePacket $packet): void{
        (new ChannelUpdatedEvent($this->plugin, $packet->getChannel()))->call();
        Storage::addChannel($packet->getChannel());
    }

    private function handleChannelUpdate(ChannelUpdatePacket $packet): void{
        (new ChannelUpdatedEvent($this->plugin, $packet->getChannel()))->call();
        Storage::updateChannel($packet->getChannel());
    }

    private function handleChannelDelete(ChannelDeletePacket $packet): void{
        $c = Storage::getChannel($packet->getChannelId());
        if($c === null){
            throw new \AssertionError("Server Channel '{$packet->getChannelId()}' not found in storage.");
        }
        (new ChannelDeletedEvent($this->plugin, $c))->call();
        Storage::removeChannel($packet->getChannelId());
    }

    private function handleChannelPinsUpdate(ChannelPinsUpdatePacket $packet): void{
        $c = Storage::getChannel($packet->getChannelId());
        if($c === null or !$c instanceof TextChannel){
            throw new \AssertionError("Text Channel '{$packet->getChannelId()}' not found in storage.");
        }
        (new ChannelPinsUpdatedEvent($this->plugin, $c))->call();
    }

    private function handleRoleCreate(RoleCreatePacket $packet): void{
        (new RoleCreatedEvent($this->plugin, $packet->getRole()))->call();
        Storage::addRole($packet->getRole());
    }

    private function handleRoleUpdate(RoleUpdatePacket $packet): void{
        (new RoleUpdatedEvent($this->plugin, $packet->getRole()))->call();
        Storage::updateRole($packet->getRole());
    }

    private function handleRoleDelete(RoleDeletePacket $packet): void{
        $r = Storage::getRole($packet->getRoleId());
        if($r === null){
            throw new \AssertionError("Role '{$packet->getRoleId()}' not found in storage.");
        }
        (new RoleDeletedEvent($this->plugin, $r))->call();
        Storage::removeRole($packet->getRoleId());
    }

    private function handleInviteCreate(InviteCreatePacket $packet): void{
        (new InviteCreatedEvent($this->plugin, $packet->getInvite()))->call();
        Storage::addInvite($packet->getInvite());
    }

    private function handleInviteDelete(InviteDeletePacket $packet): void{
        $i = Storage::getInvite($packet->getInviteCode());
        if($i === null){
            throw new \AssertionError("Invite '{$packet->getInviteCode()}' not found in storage.");
        }
        (new InviteDeletedEvent($this->plugin, $i))->call();
        Storage::removeInvite($packet->getInviteCode());
    }

    private function handleBanAdd(BanAddPacket $packet): void{
        (new BanCreatedEvent($this->plugin, $packet->getBan()))->call();
        Storage::addBan($packet->getBan());
    }

    private function handleBanRemove(BanRemovePacket $packet): void{
        $ban = Storage::getBan($packet->getBanId());
        if($ban === null){
            throw new \AssertionError("Ban '{$packet->getBanId()}' not found in storage.");
        }
        (new BanDeletedEvent($this->plugin, $ban))->call();
        Storage::removeBan($packet->getBanId());
    }

    private function handleMemberJoin(MemberJoinPacket $packet): void{
        $server = Storage::getServer($packet->getMember()->getServerId());
        if($server === null){
            throw new \AssertionError("Server '{$packet->getMember()->getServerId()}' not found for member '{$packet->getMember()->getId()}'");
        }
        (new MemberJoinedEvent($this->plugin, $packet->getMember()))->call();
        Storage::addMember($packet->getMember());
        Storage::addUser($packet->getUser());
    }

    private function handleMemberUpdate(MemberUpdatePacket $packet): void{
        (new MemberUpdatedEvent($this->plugin, $packet->getMember()))->call();
        Storage::updateMember($packet->getMember());
    }

    private function handleMemberLeave(MemberLeavePacket $packet): void{
        //When leaving server this is emitted.
        if(($u = Storage::getBotUser()) !== null and $u->getId() === explode(".", $packet->getMemberID())[1]) return;

        $member = Storage::getMember($packet->getMemberID());
        if($member === null){
            throw new \AssertionError("Member '{$packet->getMemberID()}' not found in storage.");
        }

        $server = Storage::getServer($member->getServerId());
        if($server === null){
            throw new \AssertionError("Server '{$member->getServerId()}' not found for member '{$member->getId()}'");
        }

        (new MemberLeftEvent($this->plugin, $member))->call();

        Storage::removeMember($packet->getMemberID());
    }

    private function handleServerJoin(ServerJoinPacket $packet): void{
        (new ServerJoinedEvent($this->plugin, $packet->getServer(), $packet->getRoles(),
            $packet->getChannels(), $packet->getMembers()))->call();

        Storage::addServer($packet->getServer());
        foreach($packet->getMembers() as $member){
            Storage::addMember($member);
        }
        foreach($packet->getRoles() as $role){
            Storage::addRole($role);
        }
        foreach($packet->getChannels() as $channel){
            Storage::addChannel($channel);
        }
    }

    private function handleServerUpdate(ServerUpdatePacket $packet): void{
        (new ServerUpdatedEvent($this->plugin, $packet->getServer()))->call();
        Storage::updateServer($packet->getServer());
    }

    private function handleServerLeave(ServerLeavePacket $packet): void{
        $server = Storage::getServer($packet->getServerId());
        if($server === null){
            throw new \AssertionError("Server '{$packet->getServerId()}' not found in storage.");
        }
        (new ServerDeletedEvent($this->plugin, $server))->call();
        Storage::removeServer($packet->getServerId());
    }

    private function handleDataDump(DiscordDataDumpPacket $packet): void{
        foreach($packet->getServers() as $server){
            Storage::addServer($server);
        }
        foreach($packet->getChannels() as $channel){
            Storage::addChannel($channel);
        }
        foreach($packet->getRoles() as $role){
            Storage::addRole($role);
        }
        foreach($packet->getBans() as $ban){
            Storage::addBan($ban);
        }
        foreach($packet->getInvites() as $invite){
            Storage::addInvite($invite);
        }
        foreach($packet->getMembers() as $member){
            Storage::addMember($member);
        }
        foreach($packet->getUsers() as $user){
            Storage::addUser($user);
        }
        if($packet->getBotUser() !== null){
            Storage::setBotUser($packet->getBotUser());
        }
        Storage::setTimestamp($packet->getTimestamp());
        $this->plugin->getLogger()->debug("Handled data dump (".$packet->getTimestamp().") (".$packet->getSize().")");
    }

    /**
     * Checks last KNOWN Heartbeat timestamp with current time, does not check pre-start condition.
     */
    public function checkHeartbeat(): void{
        if($this->lastHeartbeat === null) return;
        if(($diff = microtime(true) - $this->lastHeartbeat) > $this->plugin->getPluginConfig()["protocol"]["heartbeat_allowance"]){
            $this->plugin->getLogger()->emergency("DiscordBot has not responded for {$diff} seconds, disabling plugin.");
            $this->plugin->getServer()->getPluginManager()->disablePlugin($this->plugin);
        }
    }

    public function sendHeartbeat(): void{
        $this->plugin->writeOutboundData(new HeartbeatPacket(microtime(true)));
    }

    public function getLastHeartbeat(): ?float{
        return $this->lastHeartbeat;
    }
}