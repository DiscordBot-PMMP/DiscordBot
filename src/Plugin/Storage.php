<?php
/*
 * DiscordBot, PocketMine-MP Plugin.
 *
 * Licensed under the Open Software License version 3.0 (OSL-3.0)
 * Copyright (C) 2020-present JaxkDev
 *
 * Twitter :: @JaxkDev
 * Discord :: JaxkDev#2698
 * Email   :: JaxkDev@gmail.com
 */

namespace JaxkDev\DiscordBot\Plugin;

use JaxkDev\DiscordBot\Models\Ban;
use JaxkDev\DiscordBot\Models\Channels\CategoryChannel;
use JaxkDev\DiscordBot\Models\Channels\GuildChannel;
use JaxkDev\DiscordBot\Models\Channels\VoiceChannel;
use JaxkDev\DiscordBot\Models\Invite;
use JaxkDev\DiscordBot\Models\Member;
use JaxkDev\DiscordBot\Models\Role;
use JaxkDev\DiscordBot\Models\Guild;
use JaxkDev\DiscordBot\Models\User;

/*
 * Notes:
 * unset() on the removes doesnt destroy the objects until all references are unset....
 */

class Storage{

    /** @var Array<string, Guild> */
    private static $guild_map = [];

    /** @var Array<string, GuildChannel> */
    private static $channel_map = [];

    /** @var Array<string, string[]> */
    private static $channel_guild_map = [];

    /** @var Array<string, string[]> */
    private static $channel_category_map = [];

    /** @var Array<string, string[]> */
    private static $category_guild_map = [];

    /** @var Array<string, string> Link member to voice channel they're currently in. */
    private static $voiceChannel_member_map = [];

    /** @var Array<string, Member> */
    private static $member_map = [];

    /** @var Array<string, string[]> */
    private static $member_guild_map = [];

    /** @var Array<string, User> */
    private static $user_map = [];

    /** @var Array<string, Role> */
    private static $role_map = [];

    /** @var Array<string, string[]> */
    private static $role_guild_map = [];

    /** @var Array<string, Ban> */
    private static $ban_map = [];

    /** @var Array<string, string[]> */
    private static $ban_guild_map = [];

    /** @var Array<string, Invite> */
    private static $invite_map = [];

    /** @var Array<string, string[]> */
    private static $invite_guild_map = [];

    /** @var null|User */
    private static $bot_user = null;

    /** @var int */
    private static $timestamp = 0;

    /**
     * @return Guild[]
     */
    public static function getGuilds(): array{
        return array_values(self::$guild_map);
    }

    public static function getGuild(string $id): ?Guild{
        return self::$guild_map[$id] ?? null;
    }

    public static function addGuild(Guild $guild): void{
        if(isset(self::$guild_map[($id = $guild->getId())])) return; //Already added.
        self::$guild_map[$id] = $guild;
        self::$channel_guild_map[$id] = [];
        self::$category_guild_map[$id] = [];
        self::$member_guild_map[$id] = [];
        self::$role_guild_map[$id] = [];
        self::$invite_guild_map[$id] = [];
        self::$ban_guild_map[$id] = [];
    }

    public static function updateGuild(Guild $guild): void{
        if(!isset(self::$guild_map[$guild->getId()])){
            self::addGuild($guild);
        }else{
            self::$guild_map[$guild->getId()] = $guild;
        }
    }

    /**
     * NOTICE, Removes all linked members,channels and roles.
     * @param string $guild_id
     */
    public static function removeGuild(string $guild_id): void{
        if(!isset(self::$guild_map[$guild_id])) return; //Was never added or already deleted.
        unset(self::$guild_map[$guild_id]);
        //Remove guilds channels.
        foreach(self::$channel_guild_map[$guild_id] as $cid){
            unset(self::$channel_map[$cid]);
        }
        unset(self::$channel_guild_map[$guild_id]);
        //Remove guilds category's.
        foreach(self::$category_guild_map[$guild_id] as $cid){
            unset(self::$channel_map[$cid]); //Categories are channels.
        }
        unset(self::$channel_guild_map[$guild_id]);
        //Remove guilds members.
        foreach(self::$member_guild_map[$guild_id] as $mid){
            unset(self::$member_map[$mid]);
        }
        unset(self::$member_guild_map[$guild_id]);
        //Remove guilds roles.
        foreach(self::$role_guild_map[$guild_id] as $rid){
            unset(self::$role_map[$rid]);
        }
        unset(self::$role_guild_map[$guild_id]);
        //Remove guilds invites.
        foreach(self::$invite_guild_map[$guild_id] as $iid){
            unset(self::$invite_map[$iid]);
        }
        unset(self::$invite_guild_map[$guild_id]);
        //Remove guilds bans.
        foreach(self::$ban_guild_map[$guild_id] as $bid){
            unset(self::$ban_map[$bid]);
        }
        unset(self::$ban_guild_map[$guild_id]);
    }

    public static function getChannel(string $id): ?GuildChannel{
        return self::$channel_map[$id] ?? null;
    }

    /**
     * @param string $guild_id
     * @return GuildChannel[]
     */
    public static function getChannelsByGuild(string $guild_id): array{
        $channels = [];
        foreach((self::$channel_guild_map[$guild_id] ?? []) as $id){
            $c = self::getChannel($id);
            if($c !== null) $channels[] = $c;
        }
        return $channels;
    }

    /**
     * @param string $category_id
     * @return GuildChannel[]
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
     * @param string $guild_id
     * @return CategoryChannel[]
     */
    public static function getCategoriesByGuild(string $guild_id): array{
        $channels = [];
        foreach((self::$category_guild_map[$guild_id] ?? []) as $id){
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

    public static function addChannel(GuildChannel $channel): void{
        if($channel->getId() === null){
            throw new \AssertionError("Failed to add channel to storage, ID not found.");
        }
        if(isset(self::$channel_map[$channel->getId()])) return;
        if($channel instanceof CategoryChannel){
            self::$category_guild_map[$channel->getGuildId()][] = $channel->getId();
        }else{
            self::$channel_guild_map[$channel->getGuildId()][] = $channel->getId();
            self::$channel_category_map[$channel->getCategoryId()][] = $channel->getId();
        }
        self::$channel_map[$channel->getId()] = $channel;
    }

    public static function updateChannel(GuildChannel $channel): void{
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
        $guild_id = $channel->getGuildId();
        if($channel instanceof CategoryChannel){
            if(isset(self::$channel_category_map[$channel_id])) unset(self::$channel_category_map[$channel_id]);
            $i = array_search($channel_id, self::$category_guild_map[$guild_id], true);
            if($i === false || is_string($i)) return; //Not in this guilds category map.
            array_splice(self::$category_guild_map[$guild_id], $i, 1);
        }else{
            $i = array_search($channel_id, self::$channel_guild_map[$guild_id], true);
            if($i === false || is_string($i)) return; //Not in this guilds channel map.
            array_splice(self::$channel_guild_map[$guild_id], $i, 1);
        }
    }

    public static function getMember(string $id): ?Member{
        return self::$member_map[$id] ?? null;
    }

    /**
     * @param string $guild_id
     * @return Member[]
     */
    public static function getMembersByGuild(string $guild_id): array{
        $members = [];
        foreach((self::$member_guild_map[$guild_id] ?? []) as $id){
            $m = self::getMember($id);
            if($m !== null) $members[] = $m;
        }
        return $members;
    }

    public static function addMember(Member $member): void{
        if(isset(self::$member_map[$member->getId()])) return;
        self::$member_guild_map[$member->getGuildId()][] = $member->getId();
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
        $guild_id = $member->getGuildId();
        unset(self::$member_map[$member_id]);
        $i = array_search($member_id, self::$member_guild_map[$guild_id], true);
        if($i === false || is_string($i)) return; //Not in this guilds member map.
        array_splice(self::$member_guild_map[$guild_id], $i, 1);
    }

    /**
     * @return User[]
     */
    public static function getUsers(): array{
        return array_values(self::$user_map);
    }

    public static function getUser(string $id): ?User{
        return self::$user_map[$id] ?? null;
    }

    public static function addUser(User $user): void{
        self::$user_map[$user->getId()] = $user;
    }

    /**
     * Same function as addUser because no links are kept for users.
     * @param User $user
     */
    public static function updateUser(User $user): void{
        self::addUser($user);
    }

    public static function removeUser(string $user_id): void{
        unset(self::$user_map[$user_id]);
    }

    /**
     * @internal
     */
    public static function setMembersVoiceChannel(string $member_id, string $voice_channel_id): void{
        if(!((self::$channel_map[$voice_channel_id]??null) instanceof VoiceChannel)){
            throw new \AssertionError("Voice channel '$voice_channel_id' does not exist in storage.");
        }
        self::$voiceChannel_member_map[$member_id] = $voice_channel_id;
    }

    /**
     * Returns the voice channel the specified member is currently in.
     *
     * @param string $member_id
     * @return VoiceChannel|null
     */
    public static function getMembersVoiceChannel(string $member_id): ?VoiceChannel{
        if(($id = self::$voiceChannel_member_map[$member_id]??null) === null) return null;
        $c = self::$channel_map[$id];
        return ($c instanceof VoiceChannel) ? $c : null;
    }

    /**
     * @internal
     */
    public static function unsetMembersVoiceChannel(string $member_id): void{
        unset(self::$voiceChannel_member_map[$member_id]);
    }

    public static function getRole(string $id): ?Role{
        return self::$role_map[$id] ?? null;
    }

    /**
     * @param string $guild_id
     * @return Role[]
     */
    public static function getRolesByGuild(string $guild_id): array{
        $roles = [];
        foreach((self::$role_guild_map[$guild_id] ?? []) as $id){
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
        self::$role_guild_map[$role->getGuildId()][] = $role->getId();
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
        $guild_id = $role->getGuildId();
        unset(self::$role_map[$role_id]);
        $i = array_search($role_id, self::$role_guild_map[$guild_id], true);
        if($i === false || is_string($i)) return; //Not in this guilds role map.
        array_splice(self::$role_guild_map[$guild_id], $i, 1);
    }

    public static function getBan(string $id): ?Ban{
        return self::$ban_map[$id] ?? null;
    }

    /**
     * @param string $guild_id
     * @return Ban[]
     */
    public static function getBansByGuild(string $guild_id): array{
        $bans = [];
        foreach((self::$ban_guild_map[$guild_id]??[]) as $member){
            $b = self::getBan($member);
            if($b !== null) $bans[] = $b;
        }
        return $bans;
    }

    public static function addBan(Ban $ban): void{
        if(isset(self::$ban_map[$ban->getId()])) return;
        self::$ban_map[$ban->getId()] = $ban;
        self::$ban_guild_map[$ban->getGuildId()][] = $ban->getId();
    }

    public static function removeBan(string $id): void{
        $ban = self::getBan($id);
        if($ban === null) return; //Already deleted or not added.
        $guildId = $ban->getGuildId();
        unset(self::$ban_map[$id]);
        $i = array_search($id, self::$ban_guild_map[$guildId], true);
        if($i === false || is_string($i)) return; //Not in this guilds ban map.
        array_splice(self::$ban_guild_map[$guildId], $i, 1);
    }

    public static function getInvite(string $code): ?Invite{
        return self::$invite_map[$code] ?? null;
    }

    /**
     * @param string $guild_id
     * @return Invite[]
     */
    public static function getInvitesByGuild(string $guild_id): array{
        $invites = [];
        foreach((self::$invite_guild_map[$guild_id] ?? []) as $id){
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
        self::$invite_guild_map[$invite->getGuildId()][] = $invite->getCode();
        self::$invite_map[$invite->getCode()] = $invite;
    }

    public static function updateInvite(Invite $invite): void{
        if($invite->getCode() === null){
            throw new \AssertionError("Failed to update invite in storage, Code not found.");
        }
        if(!isset(self::$invite_map[$invite->getCode()])){
            self::addInvite($invite);
        }else{
            self::$invite_map[$invite->getCode()] = $invite;
        }
    }

    public static function removeInvite(string $code): void{
        $invite = self::getInvite($code);
        if($invite === null) return; //Already deleted or not added.
        $guild_id = $invite->getGuildId();
        unset(self::$invite_map[$code]);
        $i = array_search($code, self::$invite_guild_map[$guild_id], true);
        if($i === false || is_string($i)) return; //Not in this guilds invite map.
        array_splice(self::$invite_guild_map[$guild_id], $i, 1);
    }

    public static function getBotUser(): ?User{
        return self::$bot_user;
    }

    public static function setBotUser(User $user): void{
        self::$bot_user = $user;
    }

    public static function getBotMemberByGuild(string $guild_id): ?Member{
        $u = self::getBotUser();
        if($u === null) return null;
        return self::getMember("{$guild_id}.{$u->getId()}");
    }

    public static function getTimestamp(): int{
        return self::$timestamp;
    }

    public static function setTimestamp(int $timestamp): void{
        if($timestamp < 0){
            throw new \InvalidArgumentException("Timestamp must be greater than or equal to 0.");
        }
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
        self::$invite_guild_map = $storage["invite_guild_map"];
        self::$invite_map = $storage["invite_map"];
        self::$ban_guild_map = $storage["ban_guild_map"];
        self::$ban_map = $storage["ban_map"];
        self::$role_guild_map = $storage["role_guild_map"];
        self::$role_map = $storage["role_map"];
        self::$user_map = $storage["user_map"];
        self::$member_guild_map = $storage["member_guild_map"];
        self::$member_map = $storage["member_map"];
        self::$category_guild_map = $storage["category_guild_map"];
        self::$channel_category_map = $storage["channel_category_map"];
        self::$channel_guild_map = $storage["channel_guild_map"];
        self::$channel_map = $storage["channel_map"];
        self::$guild_map = $storage["guild_map"];

        MainLogger::getLogger()->debug("[DiscordBot] Successfully loaded storage from '$file'.");
        return true;
    }*/
}