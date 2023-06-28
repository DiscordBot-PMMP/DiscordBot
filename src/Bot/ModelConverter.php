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

namespace JaxkDev\DiscordBot\Bot;

use AssertionError;
use Carbon\Carbon;
use Discord\Parts\Channel\Channel as DiscordChannel;
use Discord\Parts\Channel\Invite as DiscordInvite;
use Discord\Parts\Channel\Message as DiscordMessage;
use Discord\Parts\Channel\Overwrite as DiscordOverwrite;
use Discord\Parts\Channel\Webhook as DiscordWebhook;
use Discord\Parts\Embed\Author as DiscordAuthor;
use Discord\Parts\Embed\Embed as DiscordEmbed;
use Discord\Parts\Embed\Field as DiscordField;
use Discord\Parts\Embed\Footer as DiscordFooter;
use Discord\Parts\Embed\Image as DiscordImage;
use Discord\Parts\Embed\Video as DiscordVideo;
use Discord\Parts\Guild\Ban as DiscordBan;
use Discord\Parts\Guild\Emoji as DiscordEmoji;
use Discord\Parts\Guild\Guild as DiscordGuild;
use Discord\Parts\Guild\Role as DiscordRole;
use Discord\Parts\Permissions\RolePermission as DiscordRolePermission;
use Discord\Parts\User\Activity as DiscordActivity;
use Discord\Parts\User\Member as DiscordMember;
use Discord\Parts\User\User as DiscordUser;
use Discord\Parts\WebSockets\VoiceStateUpdate as DiscordVoiceStateUpdate;
use JaxkDev\DiscordBot\Models\Ban;
use JaxkDev\DiscordBot\Models\Channels\CategoryChannel;
use JaxkDev\DiscordBot\Models\Channels\GuildChannel;
use JaxkDev\DiscordBot\Models\Channels\TextChannel;
use JaxkDev\DiscordBot\Models\Channels\VoiceChannel;
use JaxkDev\DiscordBot\Models\Emoji;
use JaxkDev\DiscordBot\Models\Guild\DefaultMessageNotificationLevel;
use JaxkDev\DiscordBot\Models\Guild\ExplicitContentFilterLevel;
use JaxkDev\DiscordBot\Models\Guild\Guild;
use JaxkDev\DiscordBot\Models\Guild\MfaLevel;
use JaxkDev\DiscordBot\Models\Guild\NsfwLevel;
use JaxkDev\DiscordBot\Models\Guild\PremiumTier;
use JaxkDev\DiscordBot\Models\Guild\VerificationLevel;
use JaxkDev\DiscordBot\Models\Invite;
use JaxkDev\DiscordBot\Models\InviteTargetType;
use JaxkDev\DiscordBot\Models\Member;
use JaxkDev\DiscordBot\Models\Messages\Attachment;
use JaxkDev\DiscordBot\Models\Messages\Embed\Author;
use JaxkDev\DiscordBot\Models\Messages\Embed\Embed;
use JaxkDev\DiscordBot\Models\Messages\Embed\Field;
use JaxkDev\DiscordBot\Models\Messages\Embed\Footer;
use JaxkDev\DiscordBot\Models\Messages\Embed\Image;
use JaxkDev\DiscordBot\Models\Messages\Embed\Video;
use JaxkDev\DiscordBot\Models\Messages\Message;
use JaxkDev\DiscordBot\Models\Messages\Reply as ReplyMessage;
use JaxkDev\DiscordBot\Models\Messages\Webhook as WebhookMessage;
use JaxkDev\DiscordBot\Models\Permissions\ChannelPermissions;
use JaxkDev\DiscordBot\Models\Permissions\RolePermissions;
use JaxkDev\DiscordBot\Models\Presence\Activity\Activity;
use JaxkDev\DiscordBot\Models\Presence\Activity\ActivityButton;
use JaxkDev\DiscordBot\Models\Presence\Activity\ActivityType;
use JaxkDev\DiscordBot\Models\Role;
use JaxkDev\DiscordBot\Models\RoleTags;
use JaxkDev\DiscordBot\Models\User;
use JaxkDev\DiscordBot\Models\UserPremiumType;
use JaxkDev\DiscordBot\Models\VoiceState;
use JaxkDev\DiscordBot\Models\Webhook;
use JaxkDev\DiscordBot\Models\WebhookType;

abstract class ModelConverter{

    static public function genModelVoiceState(DiscordVoiceStateUpdate $stateUpdate): VoiceState{
        return new VoiceState($stateUpdate->guild_id, $stateUpdate->channel_id ?? null, $stateUpdate->user_id,
            $stateUpdate->session_id, $stateUpdate->deaf, $stateUpdate->mute, $stateUpdate->self_deaf,
            $stateUpdate->self_mute, $stateUpdate->self_stream, $stateUpdate->self_video, $stateUpdate->suppress,
            $stateUpdate->request_to_speak_timestamp?->getTimestamp());
    }

    static public function genModelWebhook(DiscordWebhook $webhook): Webhook{
        return new Webhook(WebhookType::from($webhook->type), $webhook->id, $webhook->guild_id ?? null,
            $webhook->channel_id ?? null, $webhook->user?->id, $webhook->name, $webhook->avatar, $webhook->token,
            $webhook->application_id, $webhook->source_guild?->id ?? null,
            $webhook->source_channel?->id ?? null);
    }

    static public function genModelActivity(DiscordActivity $discordActivity): Activity{
        /** @var ?object{end: int|null, start: int|null} $timestamps */
        $timestamps = $discordActivity->timestamps;
        /** @var ?object{id: string|null, size: int[]|null} $party */
        $party = $discordActivity->party;
        /** @var ?object{"large_image": string|null, "large_text": string|null, "small_image": string|null, "small_text": string|null} $assets */
        $assets = $discordActivity->assets;
        /** @var ?Emoji $emoji */
        $emoji = ($discordActivity->emoji === null ? null : self::genModelEmoji($discordActivity->emoji));
        /** @var ?object{"join": string|null, "spectate": string|null, "match": string|null} $secrets */
        $secrets = $discordActivity->secrets;
        /** @var ActivityButton[] $buttons */
        $buttons = ($discordActivity->buttons === null ? [] : array_map(fn($button) => new ActivityButton($button->label ?? "", $button->url ?? null), $discordActivity->buttons));

        return new Activity($discordActivity->name, ActivityType::from($discordActivity->type), $discordActivity->url ?? null,
            $discordActivity->created_at?->getTimestamp(), $timestamps?->start ?? null, $timestamps?->end ?? null,
            $discordActivity->application_id, $discordActivity->details ?? null, $discordActivity->state ?? null, $emoji,
            $party?->id ?? null, ($party?->size ?? [])[0] ?? null, ($party?->size ?? [])[1] ?? null,
            $assets?->large_image ?? null, $assets?->large_text ?? null, $assets?->small_image ?? null,
            $assets?->small_text ?? null, $secrets?->join ?? null, $secrets?->spectate ?? null,
            $secrets?->match ?? null, $discordActivity->instance, $discordActivity->flags, $buttons);
    }

    static public function genModelMember(DiscordMember $discordMember): Member{
        $m = new Member($discordMember->id, $discordMember->joined_at === null ? 0 : $discordMember->joined_at->getTimestamp(),
            $discordMember->guild_id, [], $discordMember->nick, $discordMember->premium_since === null ? null : $discordMember->premium_since->getTimestamp());

        /** @var DiscordRole|null $r */
        $r = $discordMember->guild->roles->offsetGet($discordMember->guild_id);
        if($r === null){
            throw new AssertionError("Everyone role not found for guild '".$discordMember->guild_id."'.");
        }
        $bitwise = $r->permissions->bitwise; //Everyone perms.
        $roles = [];

        //O(2n) -> O(n) by using same loop for permissions to add roles.
        if($discordMember->guild->owner_id === $discordMember->id){
            $bitwise |= 0x8; // Add administrator permission
            foreach($discordMember->roles ?? [] as $role){
                $roles[] = $role->id;
            }
        }else{
            /* @var DiscordRole */
            foreach($discordMember->roles ?? [] as $role){
                $roles[] = $role->id;
                $bitwise |= $role->permissions->bitwise;
            }
        }

        $m->setPermissions(new RolePermissions((($bitwise & RolePermissions::ROLE_PERMISSIONS["administrator"]) !== 0) ? 2147483647 : $bitwise));
        $m->setRoles($roles);
        return $m;
    }

    //TODO, DiscordUser->global_name is only available in v10-RC6+
    static public function genModelUser(DiscordUser $user): User{
        return new User($user->id, $user->username, $user->discriminator, null, $user->getAvatarAttribute(), $user->bot, $user->system,
            $user->mfa_enabled, $user->banner ?? null, $user->accent_color ?? null, $user->locale, $user->flags ?? 0, UserPremiumType::from($user->premium_type ?? 0),
            $user->public_flags ?? 0);
    }

    static public function genModelGuild(DiscordGuild $discordGuild): Guild{
        /** @var Emoji[] $emojis */
        $emojis = [];
        foreach($discordGuild->emojis as $emoji){
            $emojis[] = self::genModelEmoji($emoji);
        }
        //TODO Pending features
        $features = [];

        return new Guild($discordGuild->id, $discordGuild->name, $discordGuild->getIconAttribute(),
            $discordGuild->getSplashAttribute(), $discordGuild->discovery_splash, $discordGuild->owner_id,
            $discordGuild->afk_channel_id, $discordGuild->afk_timeout, $discordGuild->widget_enabled,
            $discordGuild->widget_channel_id ?? null, VerificationLevel::from($discordGuild->verification_level),
            DefaultMessageNotificationLevel::from($discordGuild->default_message_notifications),
            ExplicitContentFilterLevel::from($discordGuild->explicit_content_filter),
            $emojis, $features, MfaLevel::from($discordGuild->mfa_level), $discordGuild->application_id,
            $discordGuild->system_channel_id, $discordGuild->system_channel_flags, $discordGuild->rules_channel_id,
            $discordGuild->max_presences, $discordGuild->max_members, $discordGuild->vanity_url_code,
            $discordGuild->description, $discordGuild->banner, PremiumTier::from($discordGuild->premium_tier),
            $discordGuild->premium_subscription_count, $discordGuild->preferred_locale,
            $discordGuild->public_updates_channel_id, $discordGuild->max_video_channel_users,
            $discordGuild->max_stage_video_channel_users, NsfwLevel::from($discordGuild->nsfw_level),
            $discordGuild->premium_progress_bar_enabled, $discordGuild->safety_alerts_channel_id);
    }

    /**
     * @template T of GuildChannel
     * @param DiscordChannel $dc
     * @param T $c
     * @return T
     */
    static private function applyPermissionOverwrites(DiscordChannel $dc, $c){
        /** @var DiscordOverwrite $overwrite */
        foreach($dc->overwrites as $overwrite){
            $allowed = new ChannelPermissions((int)$overwrite->allow->bitwise);
            $denied = new ChannelPermissions((int)$overwrite->deny->bitwise);
            if($overwrite->type === DiscordOverwrite::TYPE_MEMBER){
                $c->setAllowedMemberPermissions($overwrite->id, $allowed);
                $c->setDeniedMemberPermissions($overwrite->id, $denied);
            }elseif($overwrite->type === DiscordOverwrite::TYPE_ROLE){
                $c->setAllowedRolePermissions($overwrite->id, $allowed);
                $c->setDeniedRolePermissions($overwrite->id, $denied);
            }else{
                throw new AssertionError("Overwrite type unknown ? ({$overwrite->type})");
            }
        }
        return $c;
    }

    /**
     * Generates a model based on whatever type $channel is. (Excludes game store/group type)
     * @param DiscordChannel $channel
     * @return ?GuildChannel Null if type is invalid/unused.
     */
    static public function genModelChannel(DiscordChannel $channel): ?GuildChannel{
        switch($channel->type){
            case DiscordChannel::TYPE_TEXT:
            case DiscordChannel::TYPE_NEWS:
                return self::genModelTextChannel($channel);
            case DiscordChannel::TYPE_VOICE:
                return self::genModelVoiceChannel($channel);
            case DiscordChannel::TYPE_CATEGORY:
                return self::genModelCategoryChannel($channel);
            default:
                return null;
        }
    }

    static public function genModelCategoryChannel(DiscordChannel $discordChannel): CategoryChannel{
        if($discordChannel->type !== DiscordChannel::TYPE_CATEGORY){
            throw new AssertionError("Discord channel type must be `category` to generate model category channel.");
        }
        if($discordChannel->guild_id === null){
            throw new AssertionError("Guild ID must be present.");
        }
        return self::applyPermissionOverwrites($discordChannel, new CategoryChannel($discordChannel->name, $discordChannel->position,
            $discordChannel->guild_id, $discordChannel->id));
    }

    static public function genModelVoiceChannel(DiscordChannel $discordChannel): VoiceChannel{
        if($discordChannel->type !== DiscordChannel::TYPE_VOICE){
            throw new AssertionError("Discord channel type must be `voice` to generate model voice channel.");
        }
        if($discordChannel->guild_id === null){
            throw new AssertionError("Guild ID must be present.");
        }
        $ids = array_map(function($id) use($discordChannel){
            return $discordChannel->guild->id.".$id";
        }, array_keys($discordChannel->members->toArray()));
        return self::applyPermissionOverwrites($discordChannel, new VoiceChannel($discordChannel->bitrate, $discordChannel->user_limit,
            $discordChannel->name, $discordChannel->position, $discordChannel->guild_id, $ids,
            $discordChannel->parent_id, $discordChannel->id));
    }

    static public function genModelTextChannel(DiscordChannel $discordChannel): TextChannel{
        if($discordChannel->type !== DiscordChannel::TYPE_TEXT and $discordChannel->type !== DiscordChannel::TYPE_NEWS){
            throw new AssertionError("Discord channel type must be `text|news` to generate model text channel.");
        }
        if($discordChannel->guild_id === null){
            throw new AssertionError("Guild ID must be present.");
        }
        return self::applyPermissionOverwrites($discordChannel, new TextChannel($discordChannel->topic ?? "", $discordChannel->name,
            $discordChannel->position, $discordChannel->guild_id, $discordChannel->nsfw ?? false, $discordChannel->rate_limit_per_user,
            $discordChannel->parent_id, $discordChannel->id));
    }

    //TODO allow several embeds in a single normal message.
    static public function genModelMessage(DiscordMessage $discordMessage): Message{
        if($discordMessage->author === null){
            throw new AssertionError("Discord message does not have a author, cannot generate model message.");
        }
        $attachments = [];
        foreach($discordMessage->attachments as $attachment){
            $attachments[] = self::genModelAttachment($attachment);
        }
        $guild_id = $discordMessage->guild_id ?? ($discordMessage->author instanceof DiscordMember ? $discordMessage->author->guild_id : null);
        if($discordMessage->type === DiscordMessage::TYPE_NORMAL or $discordMessage->type === DiscordMessage::TYPE_APPLICATION_COMMAND){ #TODO Decide on application commands.
            if($discordMessage->webhook_id === null){
                /** @var DiscordEmbed|null $e */
                $e = $discordMessage->embeds->first();
                if($e !== null){
                    $e = self::genModelEmbed($e);
                }
                $author = $guild_id === null ? $discordMessage->author->id : $guild_id.".".$discordMessage->author->id;
                return new Message($discordMessage->channel_id, $discordMessage->id, $discordMessage->content, $e,
                    $author, $guild_id, $discordMessage->timestamp->getTimestamp(), $attachments, $discordMessage->mention_everyone,
                    array_keys($discordMessage->mentions->toArray()), array_keys($discordMessage->mention_roles->toArray()),
                    array_keys($discordMessage->mention_channels->toArray()));
            }else{
                $embeds = [];
                foreach($discordMessage->embeds as $embed){
                    $embeds[] = self::genModelEmbed($embed);
                }
                $author = $guild_id === null ? $discordMessage->author->id : $guild_id.".".$discordMessage->author->id;
                return new WebhookMessage($discordMessage->channel_id, $discordMessage->webhook_id, $embeds, $discordMessage->id,
                    $discordMessage->content, $author, $guild_id, $discordMessage->timestamp->getTimestamp(), $attachments,
                    $discordMessage->mention_everyone, array_keys($discordMessage->mentions->toArray()),
                    array_keys($discordMessage->mention_roles->toArray()), array_keys($discordMessage->mention_channels->toArray()));
            }
        }elseif($discordMessage->type === DiscordMessage::TYPE_REPLY){
            /** @var DiscordEmbed|null $e */
            $e = $discordMessage->embeds->first();
            if($e !== null){
                $e = self::genModelEmbed($e);
            }
            $author = $guild_id === null ? $discordMessage->author->id : $guild_id.".".$discordMessage->author->id;
            return new ReplyMessage($discordMessage->channel_id, $discordMessage->referenced_message?->id, $discordMessage->id,
                $discordMessage->content, $e, $author, $guild_id, $discordMessage->timestamp->getTimestamp(), $attachments,
                $discordMessage->mention_everyone, array_keys($discordMessage->mentions->toArray()),
                array_keys($discordMessage->mention_roles->toArray()), array_keys($discordMessage->mention_channels->toArray()));
        }
        # TODO Better handling of other/future message types.
        throw new AssertionError("Discord message type (" . $discordMessage->type . ") not supported.");
    }

    static public function genModelAttachment(\stdClass $attachment): Attachment{
        return new Attachment($attachment->id, $attachment->filename, $attachment->content_type ?? null,
            $attachment->size, $attachment->url, $attachment->width ?? null, $attachment->height ?? null);
    }

    static public function genModelEmbed(DiscordEmbed $discordEmbed): Embed{
        $fields = [];
        foreach(array_values($discordEmbed->fields->toArray()) as $field){
            $fields[] = self::genModelEmbedField($field);
        }
        return new Embed($discordEmbed->title, $discordEmbed->type, $discordEmbed->description, $discordEmbed->url,
            $discordEmbed->timestamp instanceof Carbon ? $discordEmbed->timestamp->getTimestamp() : (int)$discordEmbed->timestamp,
            $discordEmbed->color, $discordEmbed->footer === null ? new Footer() : self::genModelEmbedFooter($discordEmbed->footer),
            $discordEmbed->image === null ? new Image() : self::genModelEmbedImage($discordEmbed->image),
            $discordEmbed->thumbnail === null ? new Image() : self::genModelEmbedImage($discordEmbed->thumbnail),
            $discordEmbed->video === null ? new Video() : self::genModelEmbedVideo($discordEmbed->video),
            $discordEmbed->author === null ? new Author() : self::genModelEmbedAuthor($discordEmbed->author),
            $fields);
    }

    static public function genModelEmbedFooter(DiscordFooter $footer): Footer{
        return new Footer($footer->text, $footer->icon_url);
    }

    static public function genModelEmbedImage(DiscordImage $image): Image{
        return new Image($image->url, $image->width, $image->height);
    }

    static public function genModelEmbedVideo(DiscordVideo $video): Video{
        return new Video($video->url, $video->width, $video->height);
    }

    static public function genModelEmbedAuthor(DiscordAuthor $author): Author{
        return new Author($author->name, $author->url, $author->icon_url);
    }

    static public function genModelEmbedField(DiscordField $field): Field{
        return new Field($field->name, $field->value, $field->inline);
    }

    static public function genModelRolePermission(DiscordRolePermission $rolePermission): RolePermissions{
        return new RolePermissions((int)$rolePermission->bitwise);
    }

    /**
     * Remember null = true, not present = false.
     * @link https://discord.com/developers/docs/topics/permissions#role-object-role-tags-structure
     * @param object{bot_id?: int, integration_id?: int, premium_subscriber?: null, subscription_listing_id?: int,
     *              available_for_purchase?: null, guild_connections?: null} $roleTags
     */
    static public function genModelRoleTags(object $roleTags): RoleTags{
        return new RoleTags($roleTags->bot_id ?? null, $roleTags->integration_id ?? null,
            ($roleTags->premium_subscriber ?? false) === null, $roleTags->subscription_listing_id ?? null,
            ($roleTags->available_for_purchase ?? false) === null, ($roleTags->guild_connections ?? false) === null);
    }

    static public function genModelRole(DiscordRole $discordRole): Role{
        if($discordRole->guild_id === null){
            throw new AssertionError("Guild ID is null, should never happen please report this issue. (".$discordRole->serialize().")");
        }
        $tags = ($discordRole->tags === null) ? null : self::genModelRoleTags($discordRole->tags);
        return new Role($discordRole->id, $discordRole->guild_id, $discordRole->name, $discordRole->color,
            $discordRole->hoist, $discordRole->getIconAttribute(), $discordRole->unicode_emoji ?? null,
            $discordRole->position, self::genModelRolePermission($discordRole->permissions), $discordRole->managed,
            $discordRole->mentionable, $tags);
    }

    static public function genModelInvite(DiscordInvite $invite): Invite{
        if($invite->channel_id === null){
            throw new AssertionError("Channel ID is null, should never happen please report this issue. (".$invite->serialize().")");
        }
        return new Invite($invite->code, $invite->guild_id, $invite->channel_id, $invite->inviter?->id,
            $invite->target_type === null ? null : InviteTargetType::from($invite->target_type), $invite->target_user?->id,
            $invite->expires_at?->getTimestamp());
    }

    static public function genModelBan(DiscordBan $ban): Ban{
        if($ban->guild_id === null){
            throw new AssertionError("Guild ID is null, should never happen please report this issue. (".$ban->serialize().")");
        }
        return new Ban($ban->guild_id, $ban->user_id, $ban->reason);
    }

    static public function genModelEmoji(DiscordEmoji $emoji): Emoji{
        $roles = [];
        foreach($emoji->roles as $role){
            $roles[] = $role->id;
        }
        return new Emoji($emoji->id, $emoji->name, $roles, $emoji->user?->id, $emoji->require_colons, $emoji->managed,
            $emoji->animated, $emoji->available);
    }
}