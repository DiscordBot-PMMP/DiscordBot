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

use JaxkDev\DiscordBot\Models\Ban;
use JaxkDev\DiscordBot\Models\Channels\CategoryChannel;
use JaxkDev\DiscordBot\Models\Channels\ServerChannel;
use JaxkDev\DiscordBot\Models\Channels\VoiceChannel;
use JaxkDev\DiscordBot\Models\Invite;
use JaxkDev\DiscordBot\Models\Member;
use JaxkDev\DiscordBot\Models\Role;
use JaxkDev\DiscordBot\Models\Server;
use JaxkDev\DiscordBot\Models\User;

/*
 * Notes:
 * unset() on the removes doesnt destroy the objects until all references are unset....
 */

class Storage{

    /** @var Array<string, Server> */
    private static $server_map = [];

    /** @var Array<string, ServerChannel> */
    private static $channel_map = [];

    /** @var Array<string, string[]> */
    private static $channel_server_map = [];

    /** @var Array<string, string[]> */
    private static $channel_category_map = [];

    /** @var Array<string, string[]> */
    private static $category_server_map = [];

    /** @var Array<string, string> Link member to voice channel they're currently in. */
    private static $voiceChannelmember_map = [];

    /** @var Array<string, Member> */
    private static $member_map = [];

    /** @var Array<string, string[]> */
    private static $member_server_map = [];

    /** @var Array<string, User> */
    private static $user_map = [];

    /** @var Array<string, Role> */
    private static $role_map = [];

    /** @var Array<string, string[]> */
    private static $role_server_map = [];

    /** @var Array<string, Ban> */
    private static $ban_map = [];

    /** @var Array<string, string[]> */
    private static $ban_server_map = [];

    /** @var Array<string, Invite> */
    private static $invite_map = [];

    /** @var Array<string, string[]> */
    private static $invite_server_map = [];

    /** @var null|User */
    private static $bot_user = null;

    /** @var int */
    private static $timestamp = 0;

    public static function getServer(string $id): ?Server{
        return self::$server_map[$id] ?? null;
    }

    public static function addServer(Server $server): void{
        if(isset(self::$server_map[($id = $server->getId())])) return; //Already added.
        self::$server_map[$id] = $server;
        self::$channel_server_map[$id] = [];
        self::$category_server_map[$id] = [];
        self::$member_server_map[$id] = [];
        self::$role_server_map[$id] = [];
        self::$invite_server_map[$id] = [];
        self::$ban_server_map[$id] = [];
    }

    public static function updateServer(Server $server): void{
        if(!isset(self::$server_map[$server->getId()])){
            self::addServer($server);
        }else{
            self::$server_map[$server->getId()] = $server;
        }
    }

    /**
     * NOTICE, Removes all linked members,channels and roles.
     * @param string $server_id
     */
    public static function removeServer(string $server_id): void{
        if(!isset(self::$server_map[$server_id])) return; //Was never added or already deleted.
        unset(self::$server_map[$server_id]);
        //Remove servers channels.
        foreach(self::$channel_server_map[$server_id] as $cid){
            unset(self::$channel_map[$cid]);
        }
        unset(self::$channel_server_map[$server_id]);
        //Remove servers category's.
        foreach(self::$category_server_map[$server_id] as $cid){
            unset(self::$channel_map[$cid]); //Category's are channels.
        }
        unset(self::$channel_server_map[$server_id]);
        //Remove servers members.
        foreach(self::$member_server_map[$server_id] as $mid){
            unset(self::$member_map[$mid]);
        }
        unset(self::$member_server_map[$server_id]);
        //Remove servers roles.
        foreach(self::$role_server_map[$server_id] as $rid){
            unset(self::$role_map[$rid]);
        }
        unset(self::$role_server_map[$server_id]);
        //Remove servers invites.
        foreach(self::$invite_server_map[$server_id] as $iid){
            unset(self::$invite_map[$iid]);
        }
        unset(self::$invite_server_map[$server_id]);
        //Remove servers bans.
        foreach(self::$ban_server_map[$server_id] as $bid){
            unset(self::$ban_map[$bid]);
        }
        unset(self::$ban_server_map[$server_id]);
    }

    public static function getChannel(string $id): ?ServerChannel{
        return self::$channel_map[$id] ?? null;
    }

    /**
     * @param string $server_id
     * @return ServerChannel[]
     */
    public static function getChannelsByServer(string $server_id): array{
        $channels = [];
        foreach((self::$channel_server_map[$server_id] ?? []) as $id){
            $c = self::getChannel($id);
            if($c !== null) $channels[] = $c;
        }
        return $channels;
    }

    /**
     * @param string $category_id
     * @return ServerChannel[]
     */
    public static function getChannelsByCategory(string $category_id): array{
        $channels = [];
        foreach((self::$channel_category_map[$category_id] ?? []) as $id){
            $c = self::getChannel($id);
            if($c !== null){
                if($c instanceof CategoryChannel){
                    throw new \AssertionError("Channel '".$c->getId()."' error 0x0002 (Report this on github if you see this)");
                }else{
                    $channels[] = $c;
                }
            }
        }
        return $channels;
    }

    /**
     * @param string $server_id
     * @return CategoryChannel[]
     */
    public static function getCategoriesByServer(string $server_id): array{
        $channels = [];
        foreach((self::$category_server_map[$server_id] ?? []) as $id){
            $c = self::getChannel($id);
            if($c !== null){
                if(!$c instanceof CategoryChannel){
                    throw new \AssertionError("Channel '".$c->getId()."' error 0x0001 (Report this on github if you see this)");
                }else{
                    $channels[] = $c;
                }
            }
        }
        return $channels;
    }

    public static function addChannel(ServerChannel $channel): void{
        if($channel->getId() === null){
            throw new \AssertionError("Failed to add channel to storage, ID not found.");
        }
        if(isset(self::$channel_map[$channel->getId()])) return;
        if($channel instanceof CategoryChannel){
            self::$category_server_map[$channel->getServerId()][] = $channel->getId();
        }else{
            self::$channel_server_map[$channel->getServerId()][] = $channel->getId();
            self::$channel_category_map[$channel->getCategoryId()][] = $channel->getId();
        }
        self::$channel_map[$channel->getId()] = $channel;
    }

    public static function updateChannel(ServerChannel $channel): void{
        if($channel->getId() === null){
            throw new \AssertionError("Failed to update channel in storage, ID not found.");
        }
        if(!isset(self::$channel_map[$channel->getId()])){
            self::addChannel($channel);
        }else{
            self::$channel_map[$channel->getId()] = $channel;
        }
    }

    public static function removeChannel(string $channel_id): void{
        $channel = self::getChannel($channel_id);
        if($channel === null) return; //Already deleted or not added.
        unset(self::$channel_map[$channel_id]);
        $server_id = $channel->getServerId();
        if($channel instanceof CategoryChannel){
            if(isset(self::$channel_category_map[$channel_id])) unset(self::$channel_category_map[$channel_id]);
            $i = array_search($channel_id, self::$category_server_map[$server_id], true);
            if($i === false || is_string($i)) return; //Not in this servers category map.
            array_splice(self::$category_server_map[$server_id], $i, 1);
        }elseif($channel instanceof ServerChannel){
            $i = array_search($channel_id, self::$channel_server_map[$server_id], true);
            if($i === false || is_string($i)) return; //Not in this servers channel map.
            array_splice(self::$channel_server_map[$server_id], $i, 1);
        }
    }

    public static function getMember(string $id): ?Member{
        return self::$member_map[$id] ?? null;
    }

    /**
     * @param string $server_id
     * @return Member[]
     */
    public static function getMembersByServer(string $server_id): array{
        $members = [];
        foreach((self::$member_server_map[$server_id] ?? []) as $id){
            $m = self::getMember($id);
            if($m !== null) $members[] = $m;
        }
        return $members;
    }

    public static function addMember(Member $member): void{
        if(isset(self::$member_map[$member->getId()])) return;
        self::$member_server_map[$member->getServerId()][] = $member->getId();
        self::$member_map[$member->getId()] = $member;
    }

    public static function updateMember(Member $member): void{
        if(!isset(self::$member_map[$member->getId()])){
            self::addMember($member);
        }else{
            self::$member_map[$member->getId()] = $member;
        }
    }

    public static function removeMember(string $member_id): void{
        $member = self::getMember($member_id);
        if($member === null) return; //Already deleted or not added.
        $server_id = $member->getServerId();
        unset(self::$member_map[$member_id]);
        $i = array_search($member_id, self::$member_server_map[$server_id], true);
        if($i === false || is_string($i)) return; //Not in this servers member map.
        array_splice(self::$member_server_map[$server_id], $i, 1);
    }

    public static function getUser(string $id): ?User{
        return self::$user_map[$id] ?? null;
    }

    public static function addUser(User $user): void{
        self::$user_map[$user->getId()] = $user;
    }

    /**
     * @internal
     */
    public static function setMembersVoiceChannel(string $member_id, string $voice_channel_id): void{
        if(!((self::$channel_map[$voice_channel_id]??null) instanceof VoiceChannel)){
            throw new \AssertionError("Voice channel '$voice_channel_id' does not exist in storage.");
        }
        self::$voiceChannelmember_map[$member_id] = $voice_channel_id;
    }

    /**
     * Returns the voice channel the specified member is currently in.
     *
     * @param string $member_id
     * @return VoiceChannel|null
     */
    public static function getMembersVoiceChannel(string $member_id): ?VoiceChannel{
        if(($id = self::$voiceChannelmember_map[$member_id]??null) === null) return null;
        $c = self::$channel_map[$id];
        return ($c instanceof VoiceChannel) ? $c : null;
    }

    /**
     * @internal
     */
    public static function unsetMembersVoiceChannel(string $member_id): void{
        unset(self::$voiceChannelmember_map[$member_id]);
    }

    /**
     * Same function as addUser because no links are kept for users.
     * @param User $user
     */
    public static function updateUser(User $user): void{
        //No links can overwrite.
        self::addUser($user);
    }

    public static function removeUser(string $user_id): void{
        unset(self::$user_map[$user_id]);
    }

    public static function getRole(string $id): ?Role{
        return self::$role_map[$id] ?? null;
    }

    /**
     * @param string $server_id
     * @return Role[]
     */
    public static function getRolesByServer(string $server_id): array{
        $roles = [];
        foreach((self::$role_server_map[$server_id] ?? []) as $id){
            $r = self::getRole($id);
            if($r !== null){
                $roles[] = $r;
            }
        }
        return $roles;
    }

    public static function addRole(Role $role): void{
        if($role->getId() === null){
            throw new \AssertionError("Failed to add role to storage, ID not found.");
        }
        if(isset(self::$role_map[$role->getId()])) return;
        self::$role_server_map[$role->getServerId()][] = $role->getId();
        self::$role_map[$role->getId()] = $role;
    }

    public static function updateRole(Role $role): void{
        if($role->getId() === null){
            throw new \AssertionError("Failed to update role in storage, ID not found.");
        }
        if(!isset(self::$role_map[$role->getId()])){
            self::addRole($role);
        }else{
            self::$role_map[$role->getId()] = $role;
        }
    }

    public static function removeRole(string $role_id): void{
        $role = self::getRole($role_id);
        if($role === null) return; //Already deleted or not added.
        $server_id = $role->getServerId();
        unset(self::$role_map[$role_id]);
        $i = array_search($role_id, self::$role_server_map[$server_id], true);
        if($i === false || is_string($i)) return; //Not in this servers role map.
        array_splice(self::$role_server_map[$server_id], $i, 1);
    }

    public static function getBan(string $id): ?Ban{
        return self::$ban_map[$id] ?? null;
    }

    /**
     * @param string $server_id
     * @return Ban[]
     */
    public static function getServerBans(string $server_id): array{
        $bans = [];
        foreach((self::$ban_server_map[$server_id]??[]) as $member){
            $b = self::getBan($member);
            if($b !== null) $bans[] = $b;
        }
        return $bans;
    }

    public static function addBan(Ban $ban): void{
        if(isset(self::$ban_map[$ban->getId()])) return;
        self::$ban_map[$ban->getId()] = $ban;
        self::$ban_server_map[$ban->getServerId()][] = $ban->getId();
    }

    public static function removeBan(string $id): void{
        $ban = self::getBan($id);
        if($ban === null) return; //Already deleted or not added.
        $serverId = $ban->getServerId();
        unset(self::$ban_map[$id]);
        $i = array_search($id, self::$ban_server_map[$serverId], true);
        if($i === false || is_string($i)) return; //Not in this servers ban map.
        array_splice(self::$ban_server_map[$serverId], $i, 1);
    }

    public static function getInvite(string $code): ?Invite{
        return self::$invite_map[$code] ?? null;
    }

    /**
     * @param string $server_id
     * @return Invite[]
     */
    public static function getInvitesByServer(string $server_id): array{
        $invites = [];
        foreach((self::$invite_server_map[$server_id] ?? []) as $id){
            $i = self::getInvite($id);
            if($i !== null) $invites[] = $i;
        }
        return $invites;
    }

    public static function addInvite(Invite $invite): void{
        if($invite->getCode() === null){
            throw new \AssertionError("Failed to add invite to storage, Code not found.");
        }
        if(isset(self::$invite_map[$invite->getCode()])) return;
        self::$invite_server_map[$invite->getServerId()][] = $invite->getCode();
        self::$invite_map[$invite->getCode()] = $invite;
    }

    public static function updateInvite(Invite $invite): void{
        if($invite->getCode() === null){
            throw new \AssertionError("Failed to update invite in storage, Code not found.");
        }
        if(!isset(self::$invite_map[$invite->getCode()])){
            self::addinvite($invite);
        }else{
            self::$invite_map[$invite->getCode()] = $invite;
        }
    }

    public static function removeInvite(string $code): void{
        $invite = self::getinvite($code);
        if($invite === null) return; //Already deleted or not added.
        $server_id = $invite->getServerId();
        unset(self::$invite_map[$code]);
        $i = array_search($code, self::$invite_server_map[$server_id], true);
        if($i === false || is_string($i)) return; //Not in this servers invite map.
        array_splice(self::$invite_server_map[$server_id], $i, 1);
    }

    public static function getBotUser(): ?User{
        return self::$bot_user;
    }

    public static function setBotUser(User $user): void{
        self::$bot_user = $user;
    }

    public static function getBotMemberByServer(string $server_id): ?Member{
        $u = self::getBotUser();
        if($u === null) return null;
        return self::getMember("{$server_id}.{$u->getId()}");
    }

    public static function getTimestamp(): int{
        return self::$timestamp;
    }

    public static function setTimestamp(int $timestamp): void{
        self::$timestamp = $timestamp;
    }

    /**
     * Serializes entire storage, ONLY USE FOR DEBUGGING PURPOSES.
     */
    public static function serializeStorage(): string{
        return serialize([1, (new \ReflectionClass("\JaxkDev\DiscordBot\Plugin\Storage"))->getStaticProperties()]);
    }

    //Disabled for public, this should ONLY be used by active developers of DiscordBot.
    /*public static function loadStorage(string $file): bool{
        MainLogger::getLogger()->debug("[DiscordBot] Loading storage from '$file'...");

        $data = file_get_contents($file);
        if($data === false) return false;
        $data = unserialize($data);
        if(!is_array($data) or sizeof($data) !== 2 or !is_int($data[0])) return false;
        $storage = $data[1];
        self::$bot_user = $storage["bot_user"];
        self::$timestamp = $storage["timestamp"];
        self::$invite_server_map = $storage["invite_server_map"];
        self::$invite_map = $storage["invite_map"];
        self::$ban_server_map = $storage["ban_server_map"];
        self::$ban_map = $storage["ban_map"];
        self::$role_server_map = $storage["role_server_map"];
        self::$role_map = $storage["role_map"];
        self::$user_map = $storage["user_map"];
        self::$member_server_map = $storage["member_server_map"];
        self::$member_map = $storage["member_map"];
        self::$category_server_map = $storage["category_server_map"];
        self::$channel_category_map = $storage["channel_category_map"];
        self::$channel_server_map = $storage["channel_server_map"];
        self::$channel_map = $storage["channel_map"];
        self::$server_map = $storage["server_map"];

        MainLogger::getLogger()->debug("[DiscordBot] Successfully loaded storage from '$file'.");
        return true;
    }*/
}