<?php

/*
 * DiscordBot, PocketMine-MP Plugin.
 *
 * Licensed under the Open Software License version 3.0 (OSL-3.0)
 * Copyright (C) 2020-present JaxkDev
 *
 * Discord :: JaxkDev
 * Email   :: JaxkDev@gmail.com
 */

namespace JaxkDev\DiscordBot\Plugin;

use JaxkDev\DiscordBot\Communication\Packets\Discord\BanCreate as BanAddPacket;
use JaxkDev\DiscordBot\Communication\Packets\Discord\BanDelete as BanRemovePacket;
use JaxkDev\DiscordBot\Communication\Packets\Discord\BotUserUpdate as BotUserUpdatePacket;
use JaxkDev\DiscordBot\Communication\Packets\Discord\ChannelCreate as ChannelCreatePacket;
use JaxkDev\DiscordBot\Communication\Packets\Discord\ChannelDelete as ChannelDeletePacket;
use JaxkDev\DiscordBot\Communication\Packets\Discord\ChannelPinsUpdate as ChannelPinsUpdatePacket;
use JaxkDev\DiscordBot\Communication\Packets\Discord\ChannelUpdate as ChannelUpdatePacket;
use JaxkDev\DiscordBot\Communication\Packets\Discord\DiscordConnected as DiscordReadyPacket;
use JaxkDev\DiscordBot\Communication\Packets\Discord\GuildJoin as GuildJoinPacket;
use JaxkDev\DiscordBot\Communication\Packets\Discord\GuildLeave as GuildLeavePacket;
use JaxkDev\DiscordBot\Communication\Packets\Discord\GuildUpdate as GuildUpdatePacket;
use JaxkDev\DiscordBot\Communication\Packets\Discord\InteractionReceived as InteractionReceivedPacket;
use JaxkDev\DiscordBot\Communication\Packets\Discord\InviteCreate as InviteCreatePacket;
use JaxkDev\DiscordBot\Communication\Packets\Discord\InviteDelete as InviteDeletePacket;
use JaxkDev\DiscordBot\Communication\Packets\Discord\MemberJoin as MemberJoinPacket;
use JaxkDev\DiscordBot\Communication\Packets\Discord\MemberLeave as MemberLeavePacket;
use JaxkDev\DiscordBot\Communication\Packets\Discord\MemberUpdate as MemberUpdatePacket;
use JaxkDev\DiscordBot\Communication\Packets\Discord\MessageDelete as MessageDeletePacket;
use JaxkDev\DiscordBot\Communication\Packets\Discord\MessageDeleteBulk as MessageDeleteBulkPacket;
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
use JaxkDev\DiscordBot\Communication\Packets\Discord\ThreadCreate as ThreadCreatePacket;
use JaxkDev\DiscordBot\Communication\Packets\Discord\ThreadDelete as ThreadDeletePacket;
use JaxkDev\DiscordBot\Communication\Packets\Discord\ThreadUpdate as ThreadUpdatePacket;
use JaxkDev\DiscordBot\Communication\Packets\Discord\VoiceStateUpdate as VoiceStateUpdatePacket;
use JaxkDev\DiscordBot\Communication\Packets\Discord\WebhooksUpdate as WebhooksUpdatePacket;
use JaxkDev\DiscordBot\Communication\Packets\Heartbeat as HeartbeatPacket;
use JaxkDev\DiscordBot\Communication\Packets\Packet;
use JaxkDev\DiscordBot\Communication\Packets\Resolution as ResolutionPacket;
use JaxkDev\DiscordBot\Models\Ban;
use JaxkDev\DiscordBot\Models\Presence\Activity\Activity;
use JaxkDev\DiscordBot\Models\Presence\Activity\ActivityType;
use JaxkDev\DiscordBot\Models\Presence\Status;
use JaxkDev\DiscordBot\Plugin\Events\BanCreated as BanCreatedEvent;
use JaxkDev\DiscordBot\Plugin\Events\BanDeleted as BanDeletedEvent;
use JaxkDev\DiscordBot\Plugin\Events\BotUserUpdated as BotUserUpdatedEvent;
use JaxkDev\DiscordBot\Plugin\Events\ChannelCreated as ChannelCreatedEvent;
use JaxkDev\DiscordBot\Plugin\Events\ChannelDeleted as ChannelDeletedEvent;
use JaxkDev\DiscordBot\Plugin\Events\ChannelPinsUpdated as ChannelPinsUpdatedEvent;
use JaxkDev\DiscordBot\Plugin\Events\ChannelUpdated as ChannelUpdatedEvent;
use JaxkDev\DiscordBot\Plugin\Events\DiscordReady as DiscordReadyEvent;
use JaxkDev\DiscordBot\Plugin\Events\GuildDeleted as GuildDeletedEvent;
use JaxkDev\DiscordBot\Plugin\Events\GuildJoined as GuildJoinedEvent;
use JaxkDev\DiscordBot\Plugin\Events\GuildUpdated as GuildUpdatedEvent;
use JaxkDev\DiscordBot\Plugin\Events\InteractionReceived as InteractionReceivedEvent;
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
use JaxkDev\DiscordBot\Plugin\Events\MessagesBulkDeleted as MessagesBulkDeletedEvent;
use JaxkDev\DiscordBot\Plugin\Events\MessageSent as MessageSentEvent;
use JaxkDev\DiscordBot\Plugin\Events\MessageUpdated as MessageUpdatedEvent;
use JaxkDev\DiscordBot\Plugin\Events\PresenceUpdated as PresenceUpdatedEvent;
use JaxkDev\DiscordBot\Plugin\Events\RoleCreated as RoleCreatedEvent;
use JaxkDev\DiscordBot\Plugin\Events\RoleDeleted as RoleDeletedEvent;
use JaxkDev\DiscordBot\Plugin\Events\RoleUpdated as RoleUpdatedEvent;
use JaxkDev\DiscordBot\Plugin\Events\ThreadCreated as ThreadCreatedEvent;
use JaxkDev\DiscordBot\Plugin\Events\ThreadDeleted as ThreadDeletedEvent;
use JaxkDev\DiscordBot\Plugin\Events\ThreadUpdated as ThreadUpdatedEvent;
use JaxkDev\DiscordBot\Plugin\Events\VoiceStateUpdated as VoiceStateUpdatedEvent;
use JaxkDev\DiscordBot\Plugin\Events\WebhooksUpdated as WebhooksUpdatedEvent;
use pocketmine\VersionInfo;
use function floor;
use function get_class;
use function microtime;

/** @internal */
final class BotCommunicationHandler{

    private Main $plugin;

    private ?int $lastHeartbeat = null;

    public function __construct(Main $plugin){
        $this->plugin = $plugin;
    }

    public function handle(Packet $packet): void{
        // If's instances instead of ID switching due to phpstan/types.
        if($packet instanceof HeartbeatPacket){
            $this->lastHeartbeat = $packet->getHeartbeat();
            return;
        }

        if($packet instanceof ResolutionPacket) ApiResolver::handleResolution($packet);
        elseif($packet instanceof PresenceUpdatePacket) $this->handlePresenceUpdate($packet);
        elseif($packet instanceof VoiceStateUpdatePacket) $this->handleVoiceStateUpdate($packet);
        elseif($packet instanceof MemberJoinPacket) $this->handleMemberJoin($packet);
        elseif($packet instanceof MemberLeavePacket) $this->handleMemberLeave($packet);
        elseif($packet instanceof MemberUpdatePacket) $this->handleMemberUpdate($packet);
        elseif($packet instanceof MessageSentPacket) $this->handleMessageSent($packet);
        elseif($packet instanceof MessageUpdatePacket) $this->handleMessageUpdate($packet);
        elseif($packet instanceof MessageDeletePacket) $this->handleMessageDelete($packet);
        elseif($packet instanceof MessageDeleteBulkPacket) $this->handleMessageDeleteBulk($packet);
        elseif($packet instanceof MessageReactionAddPacket) $this->handleMessageReactionAdd($packet);
        elseif($packet instanceof MessageReactionRemovePacket) $this->handleMessageReactionRemove($packet);
        elseif($packet instanceof MessageReactionRemoveAllPacket) $this->handleMessageReactionRemoveAll($packet);
        elseif($packet instanceof MessageReactionRemoveEmojiPacket) $this->handleMessageReactionRemoveEmoji($packet);
        elseif($packet instanceof InteractionReceivedPacket) $this->handleInteractionReceived($packet);
        elseif($packet instanceof ChannelCreatePacket) $this->handleChannelCreate($packet);
        elseif($packet instanceof ChannelUpdatePacket) $this->handleChannelUpdate($packet);
        elseif($packet instanceof ChannelDeletePacket) $this->handleChannelDelete($packet);
        elseif($packet instanceof ChannelPinsUpdatePacket) $this->handleChannelPinsUpdate($packet);
        elseif($packet instanceof ThreadCreatePacket) $this->handleThreadCreate($packet);
        elseif($packet instanceof ThreadUpdatePacket) $this->handleThreadUpdate($packet);
        elseif($packet instanceof ThreadDeletePacket) $this->handleThreadDelete($packet);
        elseif($packet instanceof RoleCreatePacket) $this->handleRoleCreate($packet);
        elseif($packet instanceof RoleUpdatePacket) $this->handleRoleUpdate($packet);
        elseif($packet instanceof RoleDeletePacket) $this->handleRoleDelete($packet);
        elseif($packet instanceof InviteCreatePacket) $this->handleInviteCreate($packet);
        elseif($packet instanceof InviteDeletePacket) $this->handleInviteDelete($packet);
        elseif($packet instanceof BanAddPacket) $this->handleBanAdd($packet);
        elseif($packet instanceof BanRemovePacket) $this->handleBanRemove($packet);
        elseif($packet instanceof GuildJoinPacket) $this->handleGuildJoin($packet);
        elseif($packet instanceof GuildLeavePacket) $this->handleGuildLeave($packet);
        elseif($packet instanceof GuildUpdatePacket) $this->handleGuildUpdate($packet);
        elseif($packet instanceof WebhooksUpdatePacket) $this->handleWebhooksUpdate($packet);
        elseif($packet instanceof DiscordReadyPacket) $this->handleReady($packet);
        elseif($packet instanceof BotUserUpdatePacket) $this->handleBotUserUpdate($packet);
        else $this->plugin->getLogger()->warning("Unhandled packet: " . get_class($packet));
    }

    private function handleReady(DiscordReadyPacket $packet): void{
        //Default activity, Feel free to change in event / later time
        $ac = Activity::create("DiscordBot " . \JaxkDev\DiscordBot\VERSION . " | " . VersionInfo::NAME . " v" . VersionInfo::BASE_VERSION , ActivityType::GAME);

        $event = new DiscordReadyEvent($this->plugin, $packet->getBotUser(), $ac, Status::IDLE);
        $event->call();
        $this->plugin->getApi()->updateBotPresence($event->getStatus(), $event->getActivity())->otherwise(function(ApiRejection $a){
            $this->plugin->getLogger()->logException($a);
        });

        //TODO-Next-Minor, Commands here.
        //Use single event at ready event to register ALL commands.
        //This will stop duplicates reaching discord side.
    }

    private function handleThreadCreate(ThreadCreatePacket $packet): void{
        (new ThreadCreatedEvent($this->plugin, $packet->getThread()))->call();
    }

    private function handleThreadUpdate(ThreadUpdatePacket $packet): void{
        (new ThreadUpdatedEvent($this->plugin, $packet->getThread(), $packet->getOldThread()))->call();
    }

    private function handleThreadDelete(ThreadDeletePacket $packet): void{
        (new ThreadDeletedEvent($this->plugin, $packet->getType(), $packet->getId(), $packet->getGuildId(),
            $packet->getParentId(), $packet->getCachedThread()))->call();
    }

    private function handleWebhooksUpdate(WebhooksUpdatePacket $packet): void{
        (new WebhooksUpdatedEvent($this->plugin, $packet->getGuildId(), $packet->getChannelId()))->call();
    }

    private function handleBotUserUpdate(BotUserUpdatePacket $packet): void{
        (new BotUserUpdatedEvent($this->plugin, $packet->getBot()))->call();
    }

    private function handleInteractionReceived(InteractionReceivedPacket $packet): void{
        (new InteractionReceivedEvent($this->plugin, $packet->getInteraction()))->call();
    }

    private function handleVoiceStateUpdate(VoiceStateUpdatePacket $packet): void{
        (new VoiceStateUpdatedEvent($this->plugin, $packet->getVoiceState()))->call();
    }

    private function handlePresenceUpdate(PresenceUpdatePacket $packet): void{
        (new PresenceUpdatedEvent($this->plugin, $packet->getGuildId(), $packet->getUserId(), $packet->getPresence()))->call();
    }

    private function handleMessageSent(MessageSentPacket $packet): void{
        (new MessageSentEvent($this->plugin, $packet->getMessage()))->call();
    }

    private function handleMessageUpdate(MessageUpdatePacket $packet): void{
        (new MessageUpdatedEvent($this->plugin, $packet->getGuildId(), $packet->getChannelId(), $packet->getMessageId(),
            $packet->getNewMessage(), $packet->getOldMessage()))->call();
    }

    private function handleMessageDelete(MessageDeletePacket $packet): void{
        (new MessageDeletedEvent($this->plugin, $packet->getGuildId(), $packet->getChannelId(), $packet->getMessageId(),
            $packet->getCachedMessage()))->call();
    }

    private function handleMessageDeleteBulk(MessageDeleteBulkPacket $packet): void{
        (new MessagesBulkDeletedEvent($this->plugin, $packet->getGuildId(), $packet->getChannelId(),
            $packet->getMessageIds(), $packet->getMessages()))->call();
    }

    private function handleMessageReactionAdd(MessageReactionAddPacket $packet): void{
        (new MessageReactionAddEvent($this->plugin, $packet->getGuildId(), $packet->getChannelId(),
            $packet->getMessageId(), $packet->getEmoji(), $packet->getUserId()))->call();
    }

    private function handleMessageReactionRemove(MessageReactionRemovePacket $packet): void{
        (new MessageReactionRemoveEvent($this->plugin, $packet->getGuildId(), $packet->getChannelId(),
            $packet->getMessageId(), $packet->getEmoji(), $packet->getUserId()))->call();
    }

    private function handleMessageReactionRemoveAll(MessageReactionRemoveAllPacket $packet): void{
        (new MessageReactionRemoveAllEvent($this->plugin, $packet->getGuildId(), $packet->getChannelId(),
            $packet->getMessageId()))->call();
    }

    private function handleMessageReactionRemoveEmoji(MessageReactionRemoveEmojiPacket $packet): void{
        (new MessageReactionRemoveEmojiEvent($this->plugin, $packet->getGuildId(), $packet->getChannelId(),
            $packet->getMessageId(), $packet->getEmoji()))->call();
    }

    private function handleChannelCreate(ChannelCreatePacket $packet): void{
        (new ChannelCreatedEvent($this->plugin, $packet->getChannel()))->call();
    }

    private function handleChannelUpdate(ChannelUpdatePacket $packet): void{
        (new ChannelUpdatedEvent($this->plugin, $packet->getChannel(), $packet->getOldChannel()))->call();
    }

    private function handleChannelDelete(ChannelDeletePacket $packet): void{
        (new ChannelDeletedEvent($this->plugin, $packet->getGuildId(), $packet->getChannelId(), $packet->getCachedChannel()))->call();
    }

    private function handleChannelPinsUpdate(ChannelPinsUpdatePacket $packet): void{
        (new ChannelPinsUpdatedEvent($this->plugin, $packet->getGuildId(), $packet->getChannelId()))->call();
    }

    private function handleRoleCreate(RoleCreatePacket $packet): void{
        (new RoleCreatedEvent($this->plugin, $packet->getRole()))->call();
    }

    private function handleRoleUpdate(RoleUpdatePacket $packet): void{
        (new RoleUpdatedEvent($this->plugin, $packet->getRole(), $packet->getOldRole()))->call();
    }

    private function handleRoleDelete(RoleDeletePacket $packet): void{
        (new RoleDeletedEvent($this->plugin, $packet->getGuildId(), $packet->getRoleId(), $packet->getCachedRole()))->call();
    }

    private function handleInviteCreate(InviteCreatePacket $packet): void{
        (new InviteCreatedEvent($this->plugin, $packet->getInvite()))->call();
    }

    private function handleInviteDelete(InviteDeletePacket $packet): void{
        (new InviteDeletedEvent($this->plugin, $packet->getGuildId(), $packet->getChannelId(), $packet->getInviteCode()))->call();
    }

    private function handleBanAdd(BanAddPacket $packet): void{
        (new BanCreatedEvent($this->plugin, $packet->getBan()))->call();
    }

    private function handleBanRemove(BanRemovePacket $packet): void{
        (new BanDeletedEvent($this->plugin, new Ban($packet->getGuildId(), $packet->getUserId())))->call();
    }

    private function handleMemberJoin(MemberJoinPacket $packet): void{
        (new MemberJoinedEvent($this->plugin, $packet->getMember()))->call();
    }

    private function handleMemberUpdate(MemberUpdatePacket $packet): void{
        (new MemberUpdatedEvent($this->plugin, $packet->getMember(), $packet->getOldMember()))->call();
    }

    private function handleMemberLeave(MemberLeavePacket $packet): void{
        //When leaving guild this is emitted.
        if($this->plugin->getApi()->getBotUser()->getId() === $packet->getUserId()) return;

        (new MemberLeftEvent($this->plugin, $packet->getGuildId(), $packet->getUserId(), $packet->getCachedMember()))->call();
    }

    private function handleGuildJoin(GuildJoinPacket $packet): void{
        (new GuildJoinedEvent($this->plugin, $packet->getGuild()))->call();
    }

    private function handleGuildUpdate(GuildUpdatePacket $packet): void{
        (new GuildUpdatedEvent($this->plugin, $packet->getGuild(), $packet->getOldGuild()))->call();
    }

    private function handleGuildLeave(GuildLeavePacket $packet): void{
        (new GuildDeletedEvent($this->plugin, $packet->getGuildId(), $packet->getCachedGuild()))->call();
    }

    public function resetHeartbeat(): void{
        $this->lastHeartbeat = null;
    }

    /**
     * Checks last KNOWN Heartbeat timestamp with current time, does not check pre-start condition.
     */
    public function checkHeartbeat(): void{
        if($this->lastHeartbeat === null) return;
        if(($diff = microtime(true) - $this->lastHeartbeat) > $this->plugin->getPluginConfig()["protocol"]["general"]["heartbeat_allowance"]){
            $this->plugin->getLogger()->emergency("DiscordBot has not responded for {$diff} seconds, disabling plugin.");
            $this->plugin->getServer()->getPluginManager()->disablePlugin($this->plugin);
        }
    }

    public function sendHeartbeat(): void{
        $this->plugin->writeOutboundData(new HeartbeatPacket((int)floor(microtime(true))));
    }

    public function getLastHeartbeat(): ?int{
        return $this->lastHeartbeat;
    }
}