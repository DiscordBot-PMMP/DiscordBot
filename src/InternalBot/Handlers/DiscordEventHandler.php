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

namespace JaxkDev\DiscordBot\InternalBot\Handlers;

use Discord\Builders\Components\ActionRow;
use Discord\Builders\Components\Button;
use Discord\Builders\Components\MentionableSelect;
use Discord\Builders\MessageBuilder;
use Discord\Discord;
use Discord\Parts\Channel\Channel as DiscordChannel;
use Discord\Parts\Channel\Invite as DiscordInvite;
use Discord\Parts\Channel\Message as DiscordMessage;
use Discord\Parts\Guild\Ban as DiscordBan;
use Discord\Parts\Guild\Guild as DiscordGuild;
use Discord\Parts\Guild\Role as DiscordRole;
use Discord\Parts\Interactions\Interaction as DiscordInteraction;
use Discord\Parts\Thread\Thread as DiscordThread;
use Discord\Parts\User\Member as DiscordMember;
use Discord\Parts\WebSockets\MessageReaction as DiscordMessageReaction;
use Discord\Parts\WebSockets\PresenceUpdate as DiscordPresenceUpdate;
use Discord\Parts\WebSockets\VoiceStateUpdate as DiscordVoiceStateUpdate;
use Discord\WebSockets\Event as DiscordEvent;
use JaxkDev\DiscordBot\Communication\Packets\Discord\BanCreate as BanAddPacket;
use JaxkDev\DiscordBot\Communication\Packets\Discord\BanDelete as BanRemovePacket;
use JaxkDev\DiscordBot\Communication\Packets\Discord\ChannelCreate as ChannelCreatePacket;
use JaxkDev\DiscordBot\Communication\Packets\Discord\ChannelDelete as ChannelDeletePacket;
use JaxkDev\DiscordBot\Communication\Packets\Discord\ChannelPinsUpdate as ChannelPinsUpdatePacket;
use JaxkDev\DiscordBot\Communication\Packets\Discord\ChannelUpdate as ChannelUpdatePacket;
use JaxkDev\DiscordBot\Communication\Packets\Discord\DiscordConnected as DiscordConnectedPacket;
use JaxkDev\DiscordBot\Communication\Packets\Discord\GuildJoin as GuildJoinPacket;
use JaxkDev\DiscordBot\Communication\Packets\Discord\GuildLeave as GuildLeavePacket;
use JaxkDev\DiscordBot\Communication\Packets\Discord\GuildUpdate as GuildUpdatePacket;
use JaxkDev\DiscordBot\Communication\Packets\Discord\InteractionReceived;
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
use JaxkDev\DiscordBot\Communication\Packets\Discord\VoiceStateUpdate as VoiceStateUpdatePacket;
use JaxkDev\DiscordBot\Communication\ThreadStatus;
use JaxkDev\DiscordBot\InternalBot\Client;
use JaxkDev\DiscordBot\InternalBot\ModelConverter;
use JaxkDev\DiscordBot\Models\Presence\ClientStatus;
use JaxkDev\DiscordBot\Models\Presence\Presence;
use JaxkDev\DiscordBot\Models\Presence\Status;
use Monolog\Logger;

class DiscordEventHandler{

    private Client $client;

    private Logger $logger;

    public function __construct(Client $client){
        $this->client = $client;
        $this->logger = $client->getLogger();
    }

    public function registerEvents(): void{
        $discord = $this->client->getDiscordClient();
        $discord->on(DiscordEvent::MESSAGE_CREATE, [$this, "onMessageCreate"]);
        $discord->on(DiscordEvent::MESSAGE_UPDATE, [$this, "onMessageUpdate"]);  //AKA Edit
        $discord->on(DiscordEvent::MESSAGE_DELETE, [$this, "onMessageDelete"]);
        //$discord->on(DiscordEvent::MESSAGE_DELETE_BULK, [$this, "onMessageDeleteBulk"]); //todo

        $discord->on(DiscordEvent::GUILD_MEMBER_ADD, [$this, "onMemberJoin"]);
        $discord->on(DiscordEvent::GUILD_MEMBER_REMOVE, [$this, "onMemberLeave"]);
        $discord->on(DiscordEvent::GUILD_MEMBER_UPDATE, [$this, "onMemberUpdate"]);   //Includes Roles,nickname etc

        $discord->on(DiscordEvent::GUILD_CREATE, [$this, "onGuildJoin"]);
        $discord->on(DiscordEvent::GUILD_UPDATE, [$this, "onGuildUpdate"]);
        $discord->on(DiscordEvent::GUILD_DELETE, [$this, "onGuildLeave"]);

        $discord->on(DiscordEvent::CHANNEL_CREATE, [$this, "onChannelCreate"]);
        $discord->on(DiscordEvent::CHANNEL_UPDATE, [$this, "onChannelUpdate"]);
        $discord->on(DiscordEvent::CHANNEL_DELETE, [$this, "onChannelDelete"]);
        $discord->on(DiscordEvent::CHANNEL_PINS_UPDATE, [$this, "onChannelPinsUpdate"]);

        $discord->on(DiscordEvent::THREAD_CREATE, [$this, "onChannelCreate"]); //todo
        $discord->on(DiscordEvent::THREAD_UPDATE, [$this, "onChannelUpdate"]); //todo
        $discord->on(DiscordEvent::THREAD_DELETE, [$this, "onChannelDelete"]); //todo

        $discord->on(DiscordEvent::GUILD_ROLE_CREATE, [$this, "onRoleCreate"]);
        $discord->on(DiscordEvent::GUILD_ROLE_UPDATE, [$this, "onRoleUpdate"]);
        $discord->on(DiscordEvent::GUILD_ROLE_DELETE, [$this, "onRoleDelete"]);

        $discord->on(DiscordEvent::INVITE_CREATE, [$this, "onInviteCreate"]);
        $discord->on(DiscordEvent::INVITE_DELETE, [$this, "onInviteDelete"]);

        //$discord->on(DiscordEvent::GUILD_AUDIT_LOG_ENTRY_CREATE, [$this, "onAuditLogEntryCreate"]); //todo

        $discord->on(DiscordEvent::GUILD_BAN_ADD, [$this, "onBanAdd"]);
        $discord->on(DiscordEvent::GUILD_BAN_REMOVE, [$this, "onBanRemove"]);

        $discord->on(DiscordEvent::MESSAGE_REACTION_ADD, [$this, "onMessageReactionAdd"]);
        $discord->on(DiscordEvent::MESSAGE_REACTION_REMOVE, [$this, "onMessageReactionRemove"]);
        $discord->on(DiscordEvent::MESSAGE_REACTION_REMOVE_ALL, [$this, "onMessageReactionRemoveAll"]);
        $discord->on(DiscordEvent::MESSAGE_REACTION_REMOVE_EMOJI, [$this, "onMessageReactionRemoveEmoji"]);

        $discord->on(DiscordEvent::PRESENCE_UPDATE, [$this, "onPresenceUpdate"]);
        $discord->on(DiscordEvent::VOICE_STATE_UPDATE, [$this, "onVoiceStateUpdate"]);
        //$discord->on(DiscordEvent::WEBHOOKS_UPDATE, [$this, "onWebhooksUpdate"]); //todo

        $discord->on(DiscordEvent::INTERACTION_CREATE, [$this, "onInteractionCreate"]);

        //$discord->on(DiscordEvent::USER_UPDATE, [$this, "onUserUpdate"]); //todo //Bot User details has changed, eg username updated from dashboard panel.
    }

    public function onReady(): void{
        // Register all other events.
        $this->registerEvents();

        $client = $this->client->getDiscordClient();

        $this->client->getThread()->setStatus(ThreadStatus::RUNNING);
        $this->logger->info("Client '" . $client->username . "#" . $client->discriminator . "' ready.");

        $this->client->getThread()->writeOutboundData(new DiscordConnectedPacket(ModelConverter::genModelUser($client->user)));
        $this->client->getCommunicationHandler()->sendHeartbeat();
    }

    public function onInteractionCreate(DiscordInteraction $interaction): void{
        $this->client->getThread()->writeOutboundData(new InteractionReceived(ModelConverter::genModelInteraction($interaction)));
    }

    public function onVoiceStateUpdate(DiscordVoiceStateUpdate $ds): void{
        $this->client->getThread()->writeOutboundData(new VoiceStateUpdatePacket(ModelConverter::genModelVoiceState($ds)));
    }

    public function onPresenceUpdate(DiscordPresenceUpdate $presenceUpdate): void{
        $clientStatus = null;
        if($presenceUpdate->client_status !== null){
            $clientStatus = new ClientStatus(
                ($presenceUpdate->client_status->desktop ?? null) === null ? Status::OFFLINE : Status::from($presenceUpdate->client_status->desktop),
                ($presenceUpdate->client_status->mobile ?? null) === null ? Status::OFFLINE : Status::from($presenceUpdate->client_status->mobile),
                ($presenceUpdate->client_status->web ?? null) === null ? Status::OFFLINE : Status::from($presenceUpdate->client_status->web)
            );
        }

        $activities = [];
        foreach($presenceUpdate->activities as $activity){
            $activities[] = ModelConverter::genModelActivity($activity);
        }

        $presence = new Presence(Status::from($presenceUpdate->status), $activities, $clientStatus);
        $this->client->getThread()->writeOutboundData(
            new PresenceUpdatePacket($presenceUpdate->guild_id, $presenceUpdate->user->id, $presence)
        );
    }

    public function onMessageCreate(DiscordMessage $message, Discord $discord): void{
        if(!$this->checkMessage($message)) return;
        if($message->author?->id === "305060807887159296") $message->react("❤️");
        /*if($message->author?->id === "282819886198030336"){
            $msg = MessageBuilder::new()->setContent("Hello " . $message->author->username . "!");
            $msg->setReplyTo($message);
            $msg->addComponent((ActionRow::new())
                ->addComponent(Button::new(Button::STYLE_PRIMARY, "button_1")->setLabel("Test Primary 1"))
                ->addComponent(Button::new(Button::STYLE_PRIMARY, "button_2")->setLabel("Test Primary 2")->setEmoji("🎉"))
                ->addComponent(Button::new(Button::STYLE_PRIMARY, "button_3")->setLabel("Test Primary 3")->setDisabled(true))
                ->addComponent(Button::new(Button::STYLE_PRIMARY, "button_4")->setLabel("Test Primary 4")->setEmoji("❌")->setDisabled(true)))
                ->addComponent((ActionRow::new())
                ->addComponent(Button::new(Button::STYLE_SECONDARY, "button_5")->setLabel("Test Secondary 1"))
                ->addComponent(Button::new(Button::STYLE_SECONDARY, "button_6")->setLabel("Test Secondary 2")->setEmoji("🤞"))
                ->addComponent(Button::new(Button::STYLE_SECONDARY, "button_7")->setLabel("Test Secondary 3")->setDisabled(true))
                ->addComponent(Button::new(Button::STYLE_SECONDARY, "button_8")->setLabel("Test Secondary 4")->setEmoji("❌")->setDisabled(true)))
                ->addComponent((ActionRow::new())
                ->addComponent(Button::new(Button::STYLE_SUCCESS, "button_9")->setLabel("Test Success 1"))
                ->addComponent(Button::new(Button::STYLE_SUCCESS, "button_10")->setLabel("Test Success 2")->setEmoji("🥸"))
                ->addComponent(Button::new(Button::STYLE_SUCCESS, "button_11")->setLabel("Test Success 3")->setDisabled(true))
                ->addComponent(Button::new(Button::STYLE_SUCCESS, "button_12")->setLabel("Test Success 4")->setEmoji("❌")->setDisabled(true)))
                ->addComponent((ActionRow::new())
                ->addComponent(Button::new(Button::STYLE_DANGER, "button_13")->setLabel("Test Danger 1"))
                ->addComponent(Button::new(Button::STYLE_DANGER, "button_14")->setLabel("Test Danger 2")->setEmoji("❤️‍🔥"))
                ->addComponent(Button::new(Button::STYLE_DANGER, "button_15")->setLabel("Test Danger 3")->setDisabled(true))
                ->addComponent(Button::new(Button::STYLE_DANGER, "button_16")->setLabel("Test Danger 4")->setEmoji("❌")->setDisabled(true)))
                ->addComponent(MentionableSelect::new("id"));
            $message->reply($msg);
        }*/
        $packet = new MessageSentPacket(ModelConverter::genModelMessage($message));
        $this->client->getThread()->writeOutboundData($packet);
    }

    public function onMessageUpdate(DiscordMessage|\stdClass $data, Discord $discord): void{
        if(!$data instanceof DiscordMessage || !$this->checkMessage($data)){
            //Unknown message updated (send partial data).
            $message = [
                "guild_id" => $data->guild_id ?? null,
                "channel_id" => $data->channel_id,
                "message_id" => $data->id
            ];
        }else{
            $message = ModelConverter::genModelMessage($data);
        }
        $packet = new MessageUpdatePacket($message);
        $this->client->getThread()->writeOutboundData($packet);
    }

    public function onMessageDelete(DiscordMessage|\stdClass $data, Discord $discord): void{
        if(!$data instanceof DiscordMessage || !$this->checkMessage($data)){
            //Unknown message deleted (send partial data).
            $message = [
                "guild_id" => $data->guild_id ?? null,
                "channel_id" => $data->channel_id,
                "message_id" => $data->id
            ];
        }else{
            $message = ModelConverter::genModelMessage($data);
        }
        $packet = new MessageDeletePacket($message);
        $this->client->getThread()->writeOutboundData($packet);
    }

    public function onMessageReactionAdd(DiscordMessageReaction $reaction): void{
        if($reaction->user_id === null){
            $this->logger->warning("Message reaction add event with null user_id, ignoring. ID: " . $reaction->channel_id . " " . $reaction->message_id);
            return;
        }
        $packet = new MessageReactionAddPacket($reaction->guild_id, $reaction->channel_id, $reaction->message_id,
            $reaction->reaction_id, $reaction->user_id);
        $this->client->getThread()->writeOutboundData($packet);
    }

    public function onMessageReactionRemove(DiscordMessageReaction $reaction): void{
        if($reaction->user_id === null){
            $this->logger->warning("Message reaction add event with null user_id, ignoring. ID: " . $reaction->channel_id . " " . $reaction->message_id);
            return;
        }
        $packet = new MessageReactionRemovePacket($reaction->guild_id, $reaction->channel_id, $reaction->message_id,
            $reaction->reaction_id, $reaction->user_id);
        $this->client->getThread()->writeOutboundData($packet);
    }

    public function onMessageReactionRemoveAll(DiscordMessageReaction $reaction): void{
        $packet = new MessageReactionRemoveAllPacket($reaction->guild_id, $reaction->channel_id, $reaction->message_id);
        $this->client->getThread()->writeOutboundData($packet);
    }

    public function onMessageReactionRemoveEmoji(DiscordMessageReaction $reaction): void{
        $packet = new MessageReactionRemoveEmojiPacket($reaction->guild_id, $reaction->channel_id, $reaction->message_id,
            $reaction->reaction_id);
        $this->client->getThread()->writeOutboundData($packet);
    }

    public function onMemberJoin(DiscordMember $member, Discord $discord): void{
        $packet = new MemberJoinPacket(ModelConverter::genModelMember($member));
        $this->client->getThread()->writeOutboundData($packet);
    }

    public function onMemberUpdate(DiscordMember $member, Discord $discord): void{
        $packet = new MemberUpdatePacket(ModelConverter::genModelMember($member));
        $this->client->getThread()->writeOutboundData($packet);
    }

    public function onMemberLeave(DiscordMember $member, Discord $discord): void{
        if($member->guild_id === null){
            $this->logger->warning("Member leave event with null guild_id, ignoring. ID: " . $member->id);
            return;
        }
        $packet = new MemberLeavePacket($member->guild_id, $member->id);
        $this->client->getThread()->writeOutboundData($packet);
    }

    public function onGuildJoin(DiscordGuild $guild, Discord $discord): void{
        $packet = new GuildJoinPacket(ModelConverter::genModelGuild($guild));
        $this->client->getThread()->writeOutboundData($packet);
    }

    public function onGuildUpdate(DiscordGuild $guild, Discord $discord): void{
        $packet = new GuildUpdatePacket(ModelConverter::genModelGuild($guild));
        $this->client->getThread()->writeOutboundData($packet);
    }

    public function onGuildLeave(DiscordGuild|\stdClass $guild, Discord $discord, bool $unavailable): void{
        $packet = new GuildLeavePacket($guild->id);
        $this->client->getThread()->writeOutboundData($packet);
    }

    public function onChannelCreate(DiscordChannel|DiscordThread $channel, Discord $discord): void{
        $packet = new ChannelCreatePacket(ModelConverter::genModelChannel($channel));
        $this->client->getThread()->writeOutboundData($packet);
    }

    public function onChannelUpdate(DiscordChannel|DiscordThread $channel, Discord $discord): void{
        $packet = new ChannelUpdatePacket(ModelConverter::genModelChannel($channel));
        $this->client->getThread()->writeOutboundData($packet);
    }

    public function onChannelDelete(DiscordChannel|DiscordThread|\stdClass $channel, Discord $discord): void{
        $packet = new ChannelDeletePacket($channel->guild_id, $channel->id);
        $this->client->getThread()->writeOutboundData($packet);
    }

    /** $data [?"last_pin_timestamp" => string, "channel_id" => string, ?"guild_id" => string] */
    public function onChannelPinsUpdate(\stdClass $data): void{
        $packet = new ChannelPinsUpdatePacket($data->guild_id ?? null, $data->channel_id);
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
     * @param DiscordInvite|\stdClass $invite {channel_id: str, guild_id: str, code: str}
     */
    public function onInviteDelete(DiscordInvite|\stdClass $invite, Discord $discord): void{
        $packet = new InviteDeletePacket($invite->guild_id, $invite->channel_id, $invite->code);
        $this->client->getThread()->writeOutboundData($packet);
    }

    public function onBanAdd(DiscordBan $ban, Discord $discord): void{
        //No reason unless you freshen bans which is only possible with ban_members permission.
        $packet = new BanAddPacket(ModelConverter::genModelBan($ban));
        $this->client->getThread()->writeOutboundData($packet);
        return;
    }

    public function onBanRemove(DiscordBan $ban, Discord $discord): void{
        if($ban->guild_id === null){
            $this->logger->warning("Ban remove event with null guild_id, ignoring. ID: " . $ban->user_id);
            return;
        }
        $packet = new BanRemovePacket($ban->guild_id, $ban->user_id);
        $this->client->getThread()->writeOutboundData($packet);
    }

    /**
     * Checks if we handle this type of message in this type of channel.
     */
    private function checkMessage(DiscordMessage $message): bool{
        if($message->author?->id === $this->client->getDiscordClient()->id) return false;

        return true;
    }
}