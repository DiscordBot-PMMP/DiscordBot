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

namespace JaxkDev\DiscordBot\InternalBot;

use AssertionError;
use Carbon\Carbon;
use Discord\InteractionType as DiscordInteractionType;
use Discord\Parts\Channel\Attachment as DiscordAttachment;
use Discord\Parts\Channel\Channel as DiscordChannel;
use Discord\Parts\Channel\Forum\Tag as DiscordTag;
use Discord\Parts\Channel\Invite as DiscordInvite;
use Discord\Parts\Channel\Message as DiscordMessage;
use Discord\Parts\Channel\Overwrite as DiscordOverwrite;
use Discord\Parts\Channel\Reaction as DiscordReaction;
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
use Discord\Parts\Guild\Sticker as DiscordSticker;
use Discord\Parts\Interactions\Interaction as DiscordInteraction;
use Discord\Parts\Interactions\Request\Component as DiscordComponent;
use Discord\Parts\Interactions\Request\InteractionData;
use Discord\Parts\Permissions\RolePermission as DiscordRolePermission;
use Discord\Parts\Thread\Thread as DiscordThread;
use Discord\Parts\User\Activity as DiscordActivity;
use Discord\Parts\User\Member as DiscordMember;
use Discord\Parts\User\User as DiscordUser;
use Discord\Parts\WebSockets\VoiceStateUpdate as DiscordVoiceStateUpdate;
use JaxkDev\DiscordBot\Models\Ban;
use JaxkDev\DiscordBot\Models\Channels\Channel;
use JaxkDev\DiscordBot\Models\Channels\ChannelType;
use JaxkDev\DiscordBot\Models\Channels\ForumTag;
use JaxkDev\DiscordBot\Models\Channels\Overwrite;
use JaxkDev\DiscordBot\Models\Channels\OverwriteType;
use JaxkDev\DiscordBot\Models\Channels\ThreadMetadata;
use JaxkDev\DiscordBot\Models\Channels\VideoQualityMode;
use JaxkDev\DiscordBot\Models\Emoji;
use JaxkDev\DiscordBot\Models\Guild\DefaultMessageNotificationLevel;
use JaxkDev\DiscordBot\Models\Guild\ExplicitContentFilterLevel;
use JaxkDev\DiscordBot\Models\Guild\Guild;
use JaxkDev\DiscordBot\Models\Guild\MfaLevel;
use JaxkDev\DiscordBot\Models\Guild\NsfwLevel;
use JaxkDev\DiscordBot\Models\Guild\PremiumTier;
use JaxkDev\DiscordBot\Models\Guild\VerificationLevel;
use JaxkDev\DiscordBot\Models\Interactions\ApplicationCommandData;
use JaxkDev\DiscordBot\Models\Interactions\Interaction;
use JaxkDev\DiscordBot\Models\Interactions\InteractionType;
use JaxkDev\DiscordBot\Models\Interactions\MessageComponentData;
use JaxkDev\DiscordBot\Models\Interactions\ModalSubmitData;
use JaxkDev\DiscordBot\Models\Invite;
use JaxkDev\DiscordBot\Models\InviteTargetType;
use JaxkDev\DiscordBot\Models\Member;
use JaxkDev\DiscordBot\Models\Messages\Activity as MessageActivity;
use JaxkDev\DiscordBot\Models\Messages\ActivityType as MessageActivityType;
use JaxkDev\DiscordBot\Models\Messages\Attachment;
use JaxkDev\DiscordBot\Models\Messages\Component\ActionRow;
use JaxkDev\DiscordBot\Models\Messages\Component\Button;
use JaxkDev\DiscordBot\Models\Messages\Component\ButtonStyle;
use JaxkDev\DiscordBot\Models\Messages\Component\Component;
use JaxkDev\DiscordBot\Models\Messages\Component\ComponentType;
use JaxkDev\DiscordBot\Models\Messages\Component\SelectMenu;
use JaxkDev\DiscordBot\Models\Messages\Component\SelectOption;
use JaxkDev\DiscordBot\Models\Messages\Component\TextInput;
use JaxkDev\DiscordBot\Models\Messages\Component\TextInputStyle;
use JaxkDev\DiscordBot\Models\Messages\Embed\Author;
use JaxkDev\DiscordBot\Models\Messages\Embed\Embed;
use JaxkDev\DiscordBot\Models\Messages\Embed\Field;
use JaxkDev\DiscordBot\Models\Messages\Embed\Footer;
use JaxkDev\DiscordBot\Models\Messages\Embed\Image;
use JaxkDev\DiscordBot\Models\Messages\Embed\Provider;
use JaxkDev\DiscordBot\Models\Messages\Embed\Video;
use JaxkDev\DiscordBot\Models\Messages\Message;
use JaxkDev\DiscordBot\Models\Messages\MessageType;
use JaxkDev\DiscordBot\Models\Messages\Reaction;
use JaxkDev\DiscordBot\Models\Messages\Reference;
use JaxkDev\DiscordBot\Models\Permissions\ChannelPermissions;
use JaxkDev\DiscordBot\Models\Permissions\RolePermissions;
use JaxkDev\DiscordBot\Models\Presence\Activity\Activity;
use JaxkDev\DiscordBot\Models\Presence\Activity\ActivityButton;
use JaxkDev\DiscordBot\Models\Presence\Activity\ActivityType;
use JaxkDev\DiscordBot\Models\Presence\ClientStatus;
use JaxkDev\DiscordBot\Models\Presence\Presence;
use JaxkDev\DiscordBot\Models\Presence\Status;
use JaxkDev\DiscordBot\Models\Role;
use JaxkDev\DiscordBot\Models\RoleTags;
use JaxkDev\DiscordBot\Models\StickerFormatType;
use JaxkDev\DiscordBot\Models\StickerPartial;
use JaxkDev\DiscordBot\Models\User;
use JaxkDev\DiscordBot\Models\UserPremiumType;
use JaxkDev\DiscordBot\Models\VoiceState;
use JaxkDev\DiscordBot\Models\Webhook;
use JaxkDev\DiscordBot\Models\WebhookType;
use function array_map;
use function array_values;

abstract class ModelConverter{

    static public function genModelInteraction(DiscordInteraction $interaction): Interaction{
        if($interaction->data === null){
            $data = null;
        }elseif($interaction->type === DiscordInteractionType::APPLICATION_COMMAND || $interaction->type === DiscordInteractionType::APPLICATION_COMMAND_AUTOCOMPLETE){
            $data = self::genModelApplicationCommandData($interaction->data);
        }elseif($interaction->type === DiscordInteractionType::MESSAGE_COMPONENT){
            $data = self::genModelMessageComponentData($interaction->data);
        }elseif($interaction->type === DiscordInteractionType::MODAL_SUBMIT){
            $data = self::genModelModalSubmitData($interaction->data);
        }else{
            throw new AssertionError("Unknown interaction type {$interaction->type} with data.");
        }

        return new Interaction($interaction->id, $interaction->application_id, InteractionType::from($interaction->type),
            $data, $interaction->guild_id ?? null, $interaction->channel_id ?? null,
            $interaction->user?->id ?? $interaction->member?->id, $interaction->token, $interaction->version,
            ($interaction->message ?? null) === null ? null : self::genModelMessage($interaction->message),
            ($interaction->app_permissions ?? null) === null ? null : new ChannelPermissions((int)$interaction->app_permissions->bitwise),
            $interaction->locale ?? null, $interaction->guild_locale ?? null);
    }

    static public function genModelApplicationCommandData(InteractionData $data): ApplicationCommandData{
        return new ApplicationCommandData();//todo $data->id, $data->name, convert options);
    }

    static public function genModelMessageComponentData(InteractionData $data): MessageComponentData{
        if($data->custom_id === null){
            throw new AssertionError("Custom id is null for message component data.");
        }
        if($data->component_type === null){
            throw new AssertionError("Component type is null for message component data.");
        }
        return new MessageComponentData($data->custom_id, ComponentType::from($data->component_type), $data->values);
    }

    static public function genModelModalSubmitData(InteractionData $data): ModalSubmitData{
        if($data->custom_id === null){
            throw new AssertionError("Custom id is null for modal submit data.");
        }
        if($data->components === null){
            throw new AssertionError("Components is null for modal submit data.");
        }
        return new ModalSubmitData($data->custom_id, self::genModelComponents($data->components->toArray()));
    }

    /**
     * @param DiscordComponent[] $components
     * @return Component[]
     */
    static public function genModelComponents(array $components): array{
        $out = [];
        foreach($components as $component){
            $out[] = self::genModelComponent($component);
        }
        return $out;
    }

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
        /** @var object{"url": string|null, "label": string}[] $dButtons */
        $dButtons = $discordActivity->buttons ?? [];
        /** @var ActivityButton[] $buttons */
        $buttons = ($dButtons === [] ? [] : array_map(fn(/** @var ?object{"url": string|null, "label": string}[] $button */ $button) => new ActivityButton($button->label, $button->url ?? null), $dButtons));

        return new Activity($discordActivity->name, ActivityType::from($discordActivity->type), $discordActivity->url ?? null,
            $discordActivity->created_at?->getTimestamp(), $timestamps?->start ?? null, $timestamps?->end ?? null,
            $discordActivity->application_id, $discordActivity->details ?? null, $discordActivity->state ?? null, $emoji,
            $party?->id ?? null, ($party?->size ?? [])[0] ?? null, ($party?->size ?? [])[1] ?? null,
            $assets?->large_image ?? null, $assets?->large_text ?? null, $assets?->small_image ?? null,
            $assets?->small_text ?? null, $secrets?->join ?? null, $secrets?->spectate ?? null,
            $secrets?->match ?? null, $discordActivity->instance, $discordActivity->flags, $buttons);
    }

    /**
     * @link https://discord.com/developers/docs/topics/gateway-events#client-status-object
     * @param object{desktop?: string, mobile?: string, web?: string} $clientStatus
     */
    static public function genModelClientStatus(object $clientStatus): ClientStatus{
        return new ClientStatus(($clientStatus->desktop ?? null) === null ? Status::OFFLINE : Status::from($clientStatus->desktop),
            ($clientStatus->mobile ?? null) === null ? Status::OFFLINE : Status::from($clientStatus->mobile),
            ($clientStatus->web ?? null) === null ? Status::OFFLINE : Status::from($clientStatus->web));
    }

    static public function genModelMember(DiscordMember $discordMember): Member{
        if($discordMember->guild_id === null){
            throw new AssertionError("Guild id is null for member. (" . $discordMember->serialize() . ")");
        }

        /** @var DiscordRole|null $r */
        $r = $discordMember->guild?->roles?->get("id", $discordMember->guild_id);
        if($r === null){
            throw new AssertionError("Everyone role not found for guild '" . $discordMember->guild_id . "'.");
        }
        $bitwise = (int)$r->permissions->bitwise; //Everyone perms.
        $roles = [];

        //O(2n) -> O(n) by using same loop for permissions to add roles.
        if($discordMember->guild?->owner_id === $discordMember->id){
            $bitwise |= 0x8; // Add administrator permission
            foreach($discordMember->roles ?? [] as $role){
                $roles[] = $role->id;
            }
        }else{
            /* @var DiscordRole */
            foreach($discordMember->roles ?? [] as $role){
                $roles[] = $role->id;
                $bitwise |= (int)$role->permissions->bitwise;
            }
        }

        $presence = null;
        if($discordMember->status !== null){
            /** @var Activity[] $activities */
            $activities = [];
            foreach($discordMember->activities as $activity){
                $activities[] = self::genModelActivity($activity);
            }
            $presence = new Presence(Status::from($discordMember->status), $activities,
                $discordMember->client_status === null ? null : self::genModelClientStatus($discordMember->client_status));
        }

        $perms = new RolePermissions((($bitwise & RolePermissions::ROLE_PERMISSIONS["administrator"]) !== 0) ? 2147483647 : $bitwise);

        return new Member($discordMember->guild_id, $discordMember->id, $discordMember->nick ?? null,
            $discordMember->avatar_hash/** @phpstan-ignore-line */, $roles, $discordMember->joined_at?->getTimestamp(),
            $discordMember->premium_since?->getTimestamp(), $discordMember->deaf, $discordMember->mute, $discordMember->flags,
            $discordMember->pending ?? null, $perms, $discordMember->communication_disabled_until?->getTimestamp(), $presence);
    }

    static public function genModelUser(DiscordUser $user): User{
        $discriminator = ($user->discriminator === "0" ? "0000" : $user->discriminator); //Assume it got cast to int somewhere in lib.
        return new User($user->id, $user->username, $discriminator, $user->global_name, $user->avatar_hash, $user->bot, $user->system,
            $user->mfa_enabled, $user->banner_hash, $user->accent_color ?? null, $user->locale, $user->flags ?? 0, UserPremiumType::from($user->premium_type ?? 0),
            $user->public_flags ?? 0);
    }

    static public function genModelGuild(DiscordGuild $discordGuild): Guild{
        /** @var Emoji[] $emojis */
        $emojis = [];
        foreach($discordGuild->emojis as $emoji){
            $emojis[] = self::genModelEmoji($emoji);
        }

        $features = [];
        foreach($discordGuild->features as $feature){
            $features[] = $feature;
        }

        return new Guild($discordGuild->id, $discordGuild->name, $discordGuild->icon_hash/** @phpstan-ignore-line */,
            $discordGuild->splash_hash/** @phpstan-ignore-line */, $discordGuild->discovery_splash, $discordGuild->owner_id,
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

    static public function genModelChannel(DiscordChannel|DiscordThread $channel): Channel{
        $overwrites = [];
        foreach((($channel->overwrites ?? null)?->toArray() ?? []) as $overwrite){
            $overwrites[] = self::genModelOverwrite($overwrite);
        }
        $recipients = [];
        foreach(($channel->recipients ?? []) as $user){
            $recipients[] = $user->id;
        }
        $tags = [];
        foreach(($channel->available_tags ?? []) as $tag){
            $tags[] = self::genModelForumTag($tag);
        }
        return new Channel($channel->id, ChannelType::from($channel->type), $channel->guild_id ?? null, $channel->position ?? null,
            $overwrites, $channel->name ?? null, $channel->topic ?? null, $channel->nsfw ?? null, $channel->last_message_id ?? null,
            $channel->bitrate ?? null, $channel->user_limit ?? null, $channel->rate_limit_per_user ?? null, $recipients,
            $channel->icon ?? null, $channel->owner_id ?? null, $channel->application_id ?? null, $channel->managed ?? null,
            $channel->parent_id ?? null, ($channel->last_pin_timestamp ?? null)?->getTimestamp(), $channel->rtc_region ?? null,
            ($channel->video_quality_mode ?? null) === null ? null : VideoQualityMode::from($channel->video_quality_mode),
            /** @phpstan-ignore-next-line Poorly documented thread_metadata from DiscordPHP. */
            ($channel->thread_metadata ?? null) === null ? null : self::genModelThreadMetadata($channel->thread_metadata),
            $channel->flags ?? null, $tags, $channel->applied_tags ?? null);
    }

    static public function genModelForumTag(DiscordTag $tag): ForumTag{
        return new ForumTag($tag->id, $tag->name, $tag->moderated, $tag->emoji_id ?? null, $tag->emoji_name ?? null);
    }

    /** @param object{"archived": bool, "auto_archive_duration": int, "archive_timestamp": string, "locked": bool,
     *     "invitable": ?bool, "create_timestamp": ?string} $metadata */
    static public function genModelThreadMetadata(object $metadata): ThreadMetadata{
        return new ThreadMetadata($metadata->archived,
            $metadata->auto_archive_duration, Carbon::createFromTimeString($metadata->archive_timestamp)->getTimestamp(),
            $metadata->locked, $metadata->invitable ?? null,
            ($metadata->create_timestamp ?? null) === null ? null : Carbon::createFromTimeString($metadata->create_timestamp)->getTimestamp());
    }

    static public function genModelOverwrite(DiscordOverwrite $overwrite): Overwrite{
        $type = OverwriteType::from($overwrite->type);
        if($type === OverwriteType::ROLE){
            return new Overwrite($overwrite->id, $type, new RolePermissions((int)$overwrite->allow->bitwise),
                new RolePermissions((int)$overwrite->deny->bitwise));
        }
        return new Overwrite($overwrite->id, $type, new ChannelPermissions((int)$overwrite->allow->bitwise),
            new ChannelPermissions((int)$overwrite->deny->bitwise));
    }

    static public function genModelMessage(DiscordMessage $discordMessage): Message{
        $attachments = [];
        foreach($discordMessage->attachments as $attachment){
            $attachments[] = self::genModelAttachment($attachment);
        }
        $embeds = [];
        foreach($discordMessage->embeds as $embed){
            $embeds[] = self::genModelEmbed($embed);
        }
        $reactions = [];
        foreach(($discordMessage->reactions ?? []) as $reaction){
            $reactions[] = self::genModelMessageReaction($reaction);
        }
        $components = [];
        foreach(($discordMessage->components ?? []) as $component){
            $components[] = self::genModelComponentActionRow($component);
        }
        $stickers = [];
        foreach(($discordMessage->sticker_items ?? []) as $sticker){
            $stickers[] = self::genModelSticker($sticker);
        }
        $mentions = [];
        foreach($discordMessage->mentions as $user){
            $mentions[] = $user->id;
        }
        $mention_roles = [];
        foreach($discordMessage->mention_roles as $role){
           $mention_roles[] = $role->id;
        }
        /** @phpstan-ignore-next-line Poorly documented DiscordPHP objects */
        $activity = ($discordMessage->activity ?? null) === null ? null : self::genModelMessageActivity($discordMessage->activity);
        /** @phpstan-ignore-next-line Poorly documented DiscordPHP objects */
        $reference = ($discordMessage->message_reference ?? null) === null ? null : self::genModelMessageReference($discordMessage->message_reference);
        $referenced_message = ($discordMessage->referenced_message ?? null) === null ? null : self::genModelMessage($discordMessage->referenced_message);
        return new Message(
            MessageType::from($discordMessage->type), $discordMessage->id, $discordMessage->channel_id,
            ($discordMessage->author ?? null)?->id, $discordMessage->content, $discordMessage->timestamp->getTimestamp(),
            ($discordMessage->edited_timestamp ?? null)?->getTimestamp(), $discordMessage->tts,
            $discordMessage->mention_everyone, $mentions, $mention_roles, $attachments, $embeds, $reactions,
            $discordMessage->pinned, $discordMessage->webhook_id ?? null, $activity,
                $discordMessage->application_id ?? null, $reference, $discordMessage->flags ?? null, $referenced_message,
            ($discordMessage->thread ?? null)?->id, $components, $stickers
        );
    }

    /** @param object{"guild_id": ?string, "channel_id": ?string, "message_id": ?string, "fail_if_not_exists": ?bool} $ref */
    static public function genModelMessageReference(object $ref): Reference{
        return new Reference($ref->guild_id ?? null, $ref->channel_id ?? null, $ref->message_id ?? null, $ref->fail_if_not_exists ?? null);
    }

    /** @param object{"type": int, "party_id": ?string} $activity */
    static public function genModelMessageActivity(object $activity): MessageActivity{
        return new MessageActivity(MessageActivityType::from($activity->type), $activity->party_id ?? null);
    }

    static public function genModelComponentActionRow(DiscordComponent $component): ActionRow{
        if($component->type !== ComponentType::ACTION_ROW->value){
            throw new AssertionError("Failed to generate action row component, different type provided - " . $component->type);
        }
        $sub = [];
        foreach($component->components ?? [] as $sub_component){
            $sub[] = self::genModelComponent($sub_component);
        }
        return new ActionRow($sub);
    }

    static public function genModelComponent(DiscordComponent $component): Button|TextInput|SelectMenu{
        switch($component->type){
            case ComponentType::BUTTON->value:
                if($component->style === null){
                    throw new AssertionError("Button style must be present.");
                }
                return new Button(ButtonStyle::from($component->style), $component->label ?? null,
                    ($component->emoji ?? null) === null ? null : self::genModelEmoji($component->emoji),
                    $component->custom_id ?? null, $component->url ?? null, $component->disabled ?? false);
            case ComponentType::TEXT_INPUT->value:
                if($component->style === null){
                    throw new AssertionError("Text input style must be present.");
                }
                if($component->custom_id === null){
                    throw new AssertionError("Text input custom_id must be present.");
                }
                if($component->label === null){
                    throw new AssertionError("Text input label must be present.");
                }
                if($component->min_length === null){
                    throw new AssertionError("Text input min_length must be present.");
                }
                if($component->max_length === null){
                    throw new AssertionError("Text input max_length must be present.");
                }
                return new TextInput($component->custom_id, TextInputStyle::from($component->style),
                    $component->label, $component->min_length, $component->max_length,
                    $component->required ?? false, $component->value ?? null, $component->placeholder ?? null);
            case ComponentType::STRING_SELECT->value:
            case ComponentType::CHANNEL_SELECT->value:
            case ComponentType::ROLE_SELECT->value:
            case ComponentType::USER_SELECT->value:
            case ComponentType::MENTIONABLE_SELECT->value:
                if($component->custom_id === null){
                    throw new AssertionError("Select menu custom_id must be present.");
                }
                $options = [];
                /** @var object{"label": string, "value": string, "description": ?string, "emoji": ?object{"id": ?string, "name": ?string, "animated": ?bool}, "default": ?bool} $option */
                foreach(($component->options ?? []) as $option){
                    $options[] = new SelectOption($option->label, $option->value, $option->description ?? null,
                        ($option->emoji ?? null) === null ? null : new Emoji($option->emoji->id ?? null, $option->emoji->name ?? null, null, null, null, null, $option->emoji->animated ?? null, null),
                        $option->default ?? null);
                }
                //no channel_types on receive.
                return new SelectMenu(ComponentType::from($component->type), $component->custom_id, $options, [],
                    $component->placeholder ?? null, $component->min_values ?? 1, $component->max_values ?? 1,
                    $component->disabled ?? false);
            default:
                //Unknown type.
                throw new AssertionError("Unknown component type provided - " . $component->type);
        }
    }

    static public function genModelSticker(DiscordSticker $sticker): StickerPartial{
        return new StickerPartial($sticker->id, $sticker->name, StickerFormatType::from($sticker->format_type));
    }

    static public function genModelMessageReaction(DiscordReaction $reaction): Reaction{
        return new Reaction($reaction->count, $reaction->me, self::genModelEmoji($reaction->emoji));
    }

    static public function genModelAttachment(DiscordAttachment $attachment): Attachment{
        return new Attachment($attachment->id, $attachment->filename, $attachment->description ?? null,
            $attachment->content_type ?? null, $attachment->size, $attachment->url, $attachment->proxy_url,
            $attachment->height ?? null, $attachment->width ?? null, $attachment->ephemeral ?? null);
    }

    static public function genModelEmbed(DiscordEmbed $discordEmbed): Embed{
        $fields = [];
        foreach(array_values($discordEmbed->fields->toArray()) as $field){
            $fields[] = self::genModelEmbedField($field);
        }
        return new Embed(
            $discordEmbed->title,
            $discordEmbed->description,
            $discordEmbed->url,
            $discordEmbed->timestamp instanceof Carbon ? $discordEmbed->timestamp->getTimestamp() : (int)$discordEmbed->timestamp,
            $discordEmbed->color, $discordEmbed->footer === null ? null : self::genModelEmbedFooter($discordEmbed->footer),
            $discordEmbed->image === null ? null : self::genModelEmbedImage($discordEmbed->image),
            $discordEmbed->thumbnail === null ? null : self::genModelEmbedImage($discordEmbed->thumbnail),
            $discordEmbed->video === null ? null : self::genModelEmbedVideo($discordEmbed->video),
            /** @phpstan-ignore-next-line Poorly documented provider object */
            $discordEmbed->provider === null ? null : new Provider($discordEmbed->provider?->name, $discordEmbed->provider?->url),
            $discordEmbed->author === null ? null : self::genModelEmbedAuthor($discordEmbed->author),
            $fields);
    }

    static public function genModelEmbedFooter(DiscordFooter $footer): Footer{
        return new Footer($footer->text, $footer->icon_url, $footer->proxy_icon_url);
    }

    static public function genModelEmbedImage(DiscordImage $image): Image{
        return new Image($image->url, $image->proxy_url, $image->width, $image->height);
    }

    static public function genModelEmbedVideo(DiscordVideo $video): Video{
        return new Video($video->url, $video->proxy_url, $video->width, $video->height);
    }

    static public function genModelEmbedAuthor(DiscordAuthor $author): Author{
        return new Author($author->name, $author->url, $author->icon_url, $author->proxy_icon_url);
    }

    static public function genModelEmbedField(DiscordField $field): Field{
        return new Field($field->name, $field->value, $field->inline ?? false);
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
            throw new AssertionError("Guild ID is null, should never happen please report this issue. (" . $discordRole->serialize() . ")");
        }
        $tags = ($discordRole->tags === null) ? null : self::genModelRoleTags($discordRole->tags);
        return new Role($discordRole->id, $discordRole->guild_id, $discordRole->name, $discordRole->color,
            $discordRole->hoist, $discordRole->icon_hash, $discordRole->unicode_emoji ?? null,
            $discordRole->position, self::genModelRolePermission($discordRole->permissions), $discordRole->managed,
            $discordRole->mentionable, $tags);
    }

    static public function genModelInvite(DiscordInvite $invite): Invite{
        if($invite->channel_id === null){
            throw new AssertionError("Channel ID is null, should never happen please report this issue. (" . $invite->serialize() . ")");
        }
        return new Invite($invite->code, $invite->guild_id, $invite->channel_id, $invite->inviter?->id,
            $invite->target_type === null ? null : InviteTargetType::from($invite->target_type), $invite->target_user?->id,
            $invite->expires_at?->getTimestamp());
    }

    static public function genModelBan(DiscordBan $ban): Ban{
        if($ban->guild_id === null){
            throw new AssertionError("Guild ID is null, should never happen please report this issue. (" . $ban->serialize() . ")");
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