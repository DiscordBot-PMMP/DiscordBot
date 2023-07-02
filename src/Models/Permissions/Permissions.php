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

namespace JaxkDev\DiscordBot\Models\Permissions;

use JaxkDev\DiscordBot\Communication\BinarySerializable;
use JaxkDev\DiscordBot\Communication\BinaryStream;

/**
 * Note, this goes above 32bit integer limit.
 * However, PMMP requires 64bit PHP so this is okay as ints for now.
 *
 * @link https://discord.com/developers/docs/topics/permissions#permissions-bitwise-permission-flags
 */
abstract class Permissions implements \JsonSerializable, BinarySerializable{

    /** All Voice only permissions (v) */
    const VOICE_PERMISSIONS = [
        "priority_speaker" => (1 << 8),
        "stream" => (1 << 9),
        "connect" => (1 << 20),
        "speak" => (1 << 21),
        "mute_members" => (1 << 22),
        "deafen_members" => (1 << 23),
        "move_members" => (1 << 24),
        "use_vad" => (1 << 25),
        "manage_events" => (1 << 33),
        "use_embedded_activities" => (1 << 39),
        "use_soundboard" => (1 << 42),
        "use_external_sounds" => (1 << 45)
    ];

    /** All Text only permissions (t) */
    const TEXT_PERMISSIONS = [
        "manage_threads" => (1 << 34),
        "create_public_threads" => (1 << 35),
        "create_private_threads" => (1 << 36),
        "send_messages_in_threads" => (1 << 38)
    ];

    /** All Stage only permissions (s) */
    const STAGE_PERMISSIONS = [
        "stream" => (1 << 9),
        "connect" => (1 << 20),
        "mute_members" => (1 << 22),
        "move_members" => (1 << 24),
        "request_to_speak" => (1 << 32),
        "manage_events" => (1 << 33)
    ];

    /** All Role only permissions (None) */
    const ROLE_PERMISSIONS = [
        "kick_members" => (1 << 1),
        "ban_members" => (1 << 2),
        "administrator" => (1 << 3),
        "manage_guild" => (1 << 5),
        "view_audit_log" => (1 << 7),
        "view_guild_insights" => (1 << 19),
        "change_nickname" => (1 << 26),
        "manage_nicknames" => (1 << 27),
        "manage_guild_expressions" => (1 << 30),
        "moderate_members" => (1 << 40),
        "view_creator_monetization_analytics" => (1 << 41)
    ];

    /** All permissions (tvs) */
    const ALL_PERMISSIONS = [
        "create_instant_invite" => (1 << 0),
        "manage_channels" => (1 << 4),
        "add_reactions" => (1 << 6),
        "view_channel" => (1 << 10),
        "send_messages" => (1 << 11),
        "send_tts_messages" => (1 << 12),
        "manage_messages" => (1 << 13),
        "embed_links" => (1 << 14),
        "attach_files" => (1 << 15),
        "read_message_history" => (1 << 16),
        "mention_everyone" => (1 << 17),
        "use_external_emojis" => (1 << 18),
        "manage_roles" => (1 << 28),
        "manage_webhooks" => (1 << 29),
        "use_application_commands" => (1 << 31),
        "use_external_stickers" => (1 << 37),
        "send_voice_messages" => (1 << 46)
    ];

    private int $bitwise;

    /** @var Array<string, bool> */
    private array $permissions = [];

    public function __construct(int $bitwise = 0){
        $this->setBitwise($bitwise, false);
    }

    public function getBitwise(): int{
        return $this->bitwise;
    }

    public function setBitwise(int $bitwise, bool $recalculate = true): void{
        $this->bitwise = $bitwise;
        if($recalculate){
            $this->recalculatePermissions();
        }
    }

    /**
     * Returns all the permissions possible and their current state.
     * @return array<string, bool>
     */
    public function getPermissions(): array{
        if(sizeof($this->permissions) === 0){
            $this->recalculatePermissions();
        }
        return $this->permissions;
    }

    public function getPermission(string $permission): ?bool{
        if(sizeof($this->permissions) === 0){
            $this->recalculatePermissions();
        }
        return $this->permissions[$permission] ?? null;
    }

    public function setPermission(string $permission, bool $state = true): void{
        if(sizeof($this->permissions) === 0){
            $this->recalculatePermissions();
        }
        $permission = strtolower($permission);
        $posPermissions = $this::getPossiblePermissions();

        if(!in_array($permission, array_keys($posPermissions), true)){
            throw new \AssertionError("Invalid permission '{$permission}' for a '".get_parent_class($this)."'");
        }

        if($this->permissions[$permission] === $state) return;
        $this->permissions[$permission] = $state;
        $this->bitwise ^= $posPermissions[$permission];
        return;
    }

    /**
     * Using current bitwise recalculate permissions.
     * @internal
     */
    private function recalculatePermissions(): void{
        $this->permissions = [];
        $possiblePerms = $this::getPossiblePermissions();
        foreach($possiblePerms as $name => $v){
            $this->permissions[$name] = (($this->bitwise & $v) !== 0);
        }
    }

    /**
     * @return array<string, int>
     */
    abstract static function getPossiblePermissions(): array;

    //----- Serialization -----//

    public function binarySerialize(): BinaryStream{
        $stream = new BinaryStream();
        $stream->putString($this->bitwise); // String to allow 32bit programs to have a chance...
        return $stream;
    }

    abstract public static function fromBinary(BinaryStream $stream): self;

    public function jsonSerialize(): int{
        return $this->bitwise;
    }

    abstract public static function fromJson(int $bitwise): self;
}