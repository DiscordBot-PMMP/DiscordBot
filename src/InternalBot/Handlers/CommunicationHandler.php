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

namespace JaxkDev\DiscordBot\InternalBot\Handlers;

use Discord\Builders\Components\ActionRow as DiscordActionRow;
use Discord\Builders\Components\Button as DiscordButton;
use Discord\Builders\Components\ChannelSelect as DiscordChannelSelect;
use Discord\Builders\Components\MentionableSelect as DiscordMentionableSelect;
use Discord\Builders\Components\Option as DiscordOption;
use Discord\Builders\Components\RoleSelect as DiscordRoleSelect;
use Discord\Builders\Components\StringSelect as DiscordStringSelect;
use Discord\Builders\Components\UserSelect as DiscordUserSelect;
use Discord\Builders\MessageBuilder as DiscordMessageBuilder;
use Discord\Helpers\Collection as DiscordCollection;
use Discord\Parts\Channel\Channel as DiscordChannel;
use Discord\Parts\Channel\Forum\Tag as DiscordForumTag;
use Discord\Parts\Channel\Invite as DiscordInvite;
use Discord\Parts\Channel\Message as DiscordMessage;
use Discord\Parts\Channel\Webhook as DiscordWebhook;
use Discord\Parts\Embed\Embed as DiscordEmbed;
use Discord\Parts\Guild\Emoji as DiscordEmoji;
use Discord\Parts\Guild\Guild as DiscordGuild;
use Discord\Parts\Guild\Role as DiscordRole;
use Discord\Parts\User\Activity as DiscordActivity;
use Discord\Parts\User\Member as DiscordMember;
use Discord\Parts\User\User as DiscordUser;
use JaxkDev\DiscordBot\Communication\Packets\Heartbeat;
use JaxkDev\DiscordBot\Communication\Packets\Packet;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestAddReaction;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestAddRole;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestBanMember;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestBroadcastTyping;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestBulkDeleteMessages;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestCreateChannel;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestCreateInvite;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestCreateRole;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestCreateThread;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestCreateThreadFromMessage;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestCreateWebhook;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestDeleteChannel;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestDeleteInvite;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestDeleteMessage;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestDeleteRole;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestDeleteWebhook;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestEditMessage;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestFetchBans;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestFetchChannel;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestFetchChannels;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestFetchGuild;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestFetchGuilds;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestFetchInvites;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestFetchMember;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestFetchMembers;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestFetchMessage;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestFetchPinnedMessages;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestFetchRole;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestFetchRoles;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestFetchUser;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestFetchUsers;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestFetchWebhooks;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestKickMember;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestLeaveGuild;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestPinMessage;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestRemoveAllReactions;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestRemoveReaction;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestRemoveRole;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestSendMessage;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestUnbanMember;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestUnpinMessage;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestUpdateBotPresence;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestUpdateChannel;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestUpdateNickname;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestUpdateRole;
use JaxkDev\DiscordBot\Communication\Packets\Plugin\RequestUpdateWebhook;
use JaxkDev\DiscordBot\Communication\Packets\Resolution;
use JaxkDev\DiscordBot\Communication\ThreadStatus;
use JaxkDev\DiscordBot\InternalBot\Client;
use JaxkDev\DiscordBot\InternalBot\ModelConverter;
use JaxkDev\DiscordBot\Models\Channels\ForumTag;
use JaxkDev\DiscordBot\Models\Messages\Component\Button;
use JaxkDev\DiscordBot\Models\Messages\Component\ComponentType;
use JaxkDev\DiscordBot\Models\Messages\Component\SelectMenu;
use JaxkDev\DiscordBot\Models\Presence\Status;
use JaxkDev\DiscordBot\Models\Role;
use JaxkDev\DiscordBot\Plugin\ApiRejection;
use Monolog\Logger;
use React\Promise\Deferred;
use React\Promise\PromiseInterface;
use function array_combine;
use function array_keys;
use function array_map;
use function array_values;
use function floor;
use function get_class;
use function microtime;
use function React\Promise\reject;

final class CommunicationHandler{

    private Client $client;

    private ?int $lastHeartbeat = null;

    private Logger $logger;

    public function __construct(Client $client){
        $this->client = $client;
        $this->logger = $client->getLogger();
    }

    //--- Handlers:

    public function handle(Packet $pk): void{
        //Internals:
        if($pk instanceof Heartbeat){
            $this->lastHeartbeat = $pk->getHeartbeat();
            return;
        }

        //API Check:
        if($this->client->getThread()->getStatus() !== ThreadStatus::RUNNING){
            $this->resolveRequest($pk->getUID(), false, "Thread not ready for API Requests.");
            return;
        }

        //API Packets:
        if($pk instanceof RequestUpdateBotPresence)           $this->handleUpdateBotPresence($pk);
        elseif($pk instanceof RequestFetchBans)               $this->handleFetchBans($pk);
        elseif($pk instanceof RequestFetchChannel)            $this->handleFetchChannel($pk);
        elseif($pk instanceof RequestFetchChannels)           $this->handleFetchChannels($pk);
        elseif($pk instanceof RequestFetchGuild)              $this->handleFetchGuild($pk);
        elseif($pk instanceof RequestFetchGuilds)             $this->handleFetchGuilds($pk);
        elseif($pk instanceof RequestFetchInvites)            $this->handleFetchInvites($pk);
        elseif($pk instanceof RequestFetchMember)             $this->handleFetchMember($pk);
        elseif($pk instanceof RequestFetchMembers)            $this->handleFetchMembers($pk);
        elseif($pk instanceof RequestFetchMessage)            $this->handleFetchMessage($pk);
        elseif($pk instanceof RequestFetchPinnedMessages)     $this->handleFetchPinnedMessages($pk);
        elseif($pk instanceof RequestFetchRole)               $this->handleFetchRole($pk);
        elseif($pk instanceof RequestFetchRoles)              $this->handleFetchRoles($pk);
        elseif($pk instanceof RequestFetchUser)               $this->handleFetchUser($pk);
        elseif($pk instanceof RequestFetchUsers)              $this->handleFetchUsers($pk);
        elseif($pk instanceof RequestFetchWebhooks)           $this->handleFetchWebhooks($pk);
        elseif($pk instanceof RequestUpdateNickname)          $this->handleUpdateNickname($pk);
        elseif($pk instanceof RequestBroadcastTyping)         $this->handleBroadcastTyping($pk);
        elseif($pk instanceof RequestSendMessage)             $this->handleSendMessage($pk);
        elseif($pk instanceof RequestEditMessage)             $this->handleEditMessage($pk);
        elseif($pk instanceof RequestAddReaction)             $this->handleAddReaction($pk);
        elseif($pk instanceof RequestRemoveReaction)          $this->handleRemoveReaction($pk);
        elseif($pk instanceof RequestRemoveAllReactions)      $this->handleRemoveAllReactions($pk);
        elseif($pk instanceof RequestDeleteMessage)           $this->handleDeleteMessage($pk);
        elseif($pk instanceof RequestBulkDeleteMessages)      $this->handleBulkDeleteMessages($pk);
        elseif($pk instanceof RequestPinMessage)              $this->handlePinMessage($pk);
        elseif($pk instanceof RequestUnpinMessage)            $this->handleUnpinMessage($pk);
        elseif($pk instanceof RequestAddRole)                 $this->handleAddRole($pk);
        elseif($pk instanceof RequestRemoveRole)              $this->handleRemoveRole($pk);
        elseif($pk instanceof RequestCreateRole)              $this->handleCreateRole($pk);
        elseif($pk instanceof RequestUpdateRole)              $this->handleUpdateRole($pk);
        elseif($pk instanceof RequestDeleteRole)              $this->handleDeleteRole($pk);
        elseif($pk instanceof RequestKickMember)              $this->handleKickMember($pk);
        elseif($pk instanceof RequestCreateInvite)            $this->handleCreateInvite($pk);
        elseif($pk instanceof RequestDeleteInvite)            $this->handleDeleteInvite($pk);
        elseif($pk instanceof RequestCreateChannel)           $this->handleCreateChannel($pk);
        elseif($pk instanceof RequestUpdateChannel)           $this->handleUpdateChannel($pk);
        elseif($pk instanceof RequestDeleteChannel)           $this->handleDeleteChannel($pk);
        elseif($pk instanceof RequestCreateThread)            $this->handleCreateThread($pk);
        elseif($pk instanceof RequestCreateThreadFromMessage) $this->handleCreateThreadFromMessage($pk);
        elseif($pk instanceof RequestBanMember)               $this->handleBanMember($pk);
        elseif($pk instanceof RequestUnbanMember)             $this->handleUnbanMember($pk);
        elseif($pk instanceof RequestCreateWebhook)           $this->handleCreateWebhook($pk);
        elseif($pk instanceof RequestUpdateWebhook)           $this->handleUpdateWebhook($pk);
        elseif($pk instanceof RequestDeleteWebhook)           $this->handleDeleteWebhook($pk);
        elseif($pk instanceof RequestLeaveGuild)              $this->handleLeaveGuild($pk);
    }

    private function handleFetchBans(RequestFetchBans $pk): void{
        $this->getGuild($pk, $pk->getGuildId(), function(DiscordGuild $guild) use($pk){
            $bans = [];
            foreach($guild->bans->toArray() as $ban){
                $bans[] = ModelConverter::genModelBan($ban);
            }
            $this->resolveRequest($pk->getUID(), true, "Fetched bans.", $bans);
        });
    }

    private function handleFetchChannel(RequestFetchChannel $pk): void{
        $this->getChannel($pk, $pk->getChannelId(), function(DiscordChannel $channel) use($pk){
            $this->resolveRequest($pk->getUID(), true, "Fetched channel.", [ModelConverter::genModelChannel($channel)]);
        });
    }

    private function handleFetchChannels(RequestFetchChannels $pk): void{
        $this->getGuild($pk, $pk->getGuildId(), function(DiscordGuild $guild) use($pk){
            $channels = [];
            foreach($guild->channels->toArray() as $channel){
                $channels[] = ModelConverter::genModelChannel($channel);
            }
            $this->resolveRequest($pk->getUID(), true, "Fetched channels.", $channels);
        });
    }

    private function handleFetchGuild(RequestFetchGuild $pk): void{
        $this->getGuild($pk, $pk->getGuildId(), function(DiscordGuild $guild) use($pk){
            $this->resolveRequest($pk->getUID(), true, "Fetched guild.", [ModelConverter::genModelGuild($guild)]);
        });
    }

    private function handleFetchGuilds(RequestFetchGuilds $pk): void{
        $guilds = [];
        foreach($this->client->getDiscordClient()->guilds->toArray() as $guild){
            $guilds[] = ModelConverter::genModelGuild($guild);
        }
        $this->resolveRequest($pk->getUID(), true, "Fetched guilds.", $guilds);
    }

    private function handleFetchInvites(RequestFetchInvites $pk): void{
        $this->getGuild($pk, $pk->getGuildId(), function(DiscordGuild $guild) use($pk){
            $invites = [];
            foreach($guild->invites->toArray() as $invite){
                $invites[] = ModelConverter::genModelInvite($invite);
            }
            $this->resolveRequest($pk->getUID(), true, "Fetched invites.", $invites);
        });
    }

    private function handleFetchMember(RequestFetchMember $pk): void{
        $this->getMember($pk, $pk->getGuildId(), $pk->getUserId(), function(DiscordMember $member) use($pk){
            $this->resolveRequest($pk->getUID(), true, "Fetched member.", [ModelConverter::genModelMember($member)]);
        });
    }

    private function handleFetchMembers(RequestFetchMembers $pk): void{
        $this->getGuild($pk, $pk->getGuildId(), function(DiscordGuild $guild) use($pk){
            $members = [];
            foreach($guild->members->toArray() as $member){
                $members[] = ModelConverter::genModelMember($member);
            }
            $this->resolveRequest($pk->getUID(), true, "Fetched members.", $members);
        });
    }

    private function handleFetchMessage(RequestFetchMessage $pk): void{
        $this->getMessage($pk, $pk->getChannelId(), $pk->getMessageId(), function(DiscordMessage $message) use($pk){
            $this->resolveRequest($pk->getUID(), true, "Fetched message.", [ModelConverter::genModelMessage($message)]);
        });
    }

    private function handleFetchPinnedMessages(RequestFetchPinnedMessages $pk): void{
        $this->getChannel($pk, $pk->getChannelId(), function(DiscordChannel $channel) use($pk){
            $channel->getPinnedMessages()->then(function(DiscordCollection $collection) use($pk){
                $messages = [];
                foreach($collection->toArray() as $message){
                    $messages[] = ModelConverter::genModelMessage($message);
                }
                $this->resolveRequest($pk->getUID(), true, "Fetched pinned messages.", $messages);
            }, function(\Throwable $e) use($pk){
                $this->resolveRequest($pk->getUID(), false, "Failed to fetch pinned messages.", [$e->getMessage(), $e->getTraceAsString()]);
                $this->logger->debug("Failed to fetch pinned messages ({$pk->getUID()}) - {$e->getMessage()}");
            });
        });
    }

    private function handleFetchRole(RequestFetchRole $pk): void{
        $this->getGuild($pk, $pk->getGuildId(), function(DiscordGuild $guild) use($pk){
            $guild->roles->fetch($pk->getRoleId())->then(function(DiscordRole $role) use($pk){
                $this->resolveRequest($pk->getUID(), true, "Fetched role.", [ModelConverter::genModelRole($role)]);
            }, function(\Throwable $e) use($pk){
                $this->resolveRequest($pk->getUID(), false, "Failed to fetch role.", [$e->getMessage(), $e->getTraceAsString()]);
                $this->logger->debug("Failed to fetch role ({$pk->getUID()}) - {$e->getMessage()}");
            });
        });
    }

    private function handleFetchRoles(RequestFetchRoles $pk): void{
        $this->getGuild($pk, $pk->getGuildId(), function(DiscordGuild $guild) use($pk){
            $roles = [];
            foreach($guild->roles->toArray() as $role){
                $roles[] = ModelConverter::genModelRole($role);
            }
            $this->resolveRequest($pk->getUID(), true, "Fetched roles.", $roles);
        });
    }

    private function handleFetchUser(RequestFetchUser $pk): void{
        $this->client->getDiscordClient()->users->fetch($pk->getUserId())->then(function(DiscordUser $user) use($pk){
            $this->resolveRequest($pk->getUID(), true, "Fetched user.", [ModelConverter::genModelUser($user)]);
        }, function(\Throwable $e) use($pk){
            $this->resolveRequest($pk->getUID(), false, "Failed to fetch user.", [$e->getMessage(), $e->getTraceAsString()]);
            $this->logger->debug("Failed to fetch user ({$pk->getUID()}) - {$e->getMessage()}");
        });
    }

    private function handleFetchUsers(RequestFetchUsers $pk): void{
        $users = [];
        foreach($this->client->getDiscordClient()->users->toArray() as $user){
            $users[] = ModelConverter::genModelUser($user);
        }
        $this->resolveRequest($pk->getUID(), true, "Fetched users.", $users);
    }

    private function handleFetchWebhooks(RequestFetchWebhooks $pk): void{
        if($pk->getChannelId() !== null){
            $this->getChannel($pk, $pk->getChannelId(), function(DiscordChannel $channel) use($pk){
                $webhooks = [];
                foreach($channel->webhooks->toArray() as $webhook){
                    $webhooks[] = ModelConverter::genModelWebhook($webhook);
                }
                $this->resolveRequest($pk->getUID(), true, "Fetched webhooks.", $webhooks);
            });
        }else{
            $this->getGuild($pk, $pk->getGuildId(), function(DiscordGuild $guild) use ($pk){
                $webhooks = [];
                foreach($guild->channels->toArray() as $channel){
                    foreach($channel->webhooks->toArray() as $webhook){
                        $webhooks[] = ModelConverter::genModelWebhook($webhook);
                    }
                }
                $this->resolveRequest($pk->getUID(), true, "Fetched webhooks.", $webhooks);
            });
        }
    }

    private function handleDeleteWebhook(RequestDeleteWebhook $pk): void{
        $this->getChannel($pk, $pk->getChannelId(), function(DiscordChannel $channel) use($pk){
            $channel->webhooks->delete($pk->getWebhookId(), $pk->getReason())->then(function(DiscordWebhook $webhook) use($pk){
                $this->resolveRequest($pk->getUID());
            }, function(\Throwable $e) use($pk){
                $this->resolveRequest($pk->getUID(), false, "Failed to delete webhook.", [$e->getMessage(), $e->getTraceAsString()]);
                $this->logger->debug("Failed to delete webhook ({$pk->getUID()}) - {$e->getMessage()}");
            });
        });
    }

    private function handleUpdateWebhook(RequestUpdateWebhook $pk): void{
        if($pk->getWebhook()->getChannelId() === null){
            throw new \AssertionError("Webhook channel ID must be present.");
        }
        $this->getChannel($pk, $pk->getWebhook()->getChannelId(), function(DiscordChannel $channel) use($pk){
            $channel->webhooks->fetch($pk->getWebhook()->getId())->then(function(DiscordWebhook $webhook) use($channel, $pk){
                $webhook->name = $pk->getWebhook()->getName();
                $webhook->avatar = $pk->getNewAvatarData() ?? $pk->getWebhook()->getAvatar();
                $channel->webhooks->save($webhook, $pk->getReason())->then(function(DiscordWebhook $webhook) use($pk){
                    $this->resolveRequest($pk->getUID(), true, "Successfully updated webhook.", [ModelConverter::genModelWebhook($webhook)]);
                }, function(\Throwable $e) use($pk){
                    $this->resolveRequest($pk->getUID(), false, "Failed to update webhook.", [$e->getMessage(), $e->getTraceAsString()]);
                    $this->logger->debug("Failed to update webhook ({$pk->getUID()}) - {$e->getMessage()}");
                });
            }, function(\Throwable $e) use($pk){
                $this->resolveRequest($pk->getUID(), false, "Failed to update webhook.", [$e->getMessage(), $e->getTraceAsString()]);
                $this->logger->debug("Failed to update webhook ({$pk->getUID()}) - fetch error: {$e->getMessage()}");
            });
        });
    }

    private function handleCreateWebhook(RequestCreateWebhook $pk): void{
        $this->getChannel($pk, $pk->getChannelId(), function(DiscordChannel $channel) use($pk){
            $channel->webhooks->save($channel->webhooks->create([
                'name' => $pk->getName(),
                'avatar' => $pk->getAvatarHash()
            ]), $pk->getReason())->then(function(DiscordWebhook $webhook) use($pk){
                $this->resolveRequest($pk->getUID(), true, "Successfully created webhook.", [ModelConverter::genModelWebhook($webhook)]);
            }, function(\Throwable $e) use($pk){
                $this->resolveRequest($pk->getUID(), false, "Failed to create webhook.", [$e->getMessage(), $e->getTraceAsString()]);
                $this->logger->debug("Failed to create webhook ({$pk->getUID()}) - {$e->getMessage()}");
            });
        });
    }

    private function handleUnpinMessage(RequestUnpinMessage $pk): void{
        $this->getChannel($pk, $pk->getChannelId(), function(DiscordChannel $channel) use($pk){
            $this->getMessage($pk, $pk->getChannelId(), $pk->getMessageId(), function(DiscordMessage $message) use($channel, $pk){
                $channel->unpinMessage($message)->then(function() use($pk){
                    $this->resolveRequest($pk->getUID(), true, "Successfully unpinned the message.");
                }, function(\Throwable $e) use($pk){
                    $this->resolveRequest($pk->getUID(), false, "Failed to unpin the message.", [$e->getMessage(), $e->getTraceAsString()]);
                    $this->logger->debug("Failed to pin the message ({$pk->getUID()}) - {$e->getMessage()}");
                });
            });
        });
    }

    private function handlePinMessage(RequestPinMessage $pk): void{
        $this->getChannel($pk, $pk->getChannelId(), function(DiscordChannel $channel) use($pk){
            $this->getMessage($pk, $pk->getChannelId(), $pk->getMessageId(), function(DiscordMessage $message) use($channel, $pk){
                $channel->pinMessage($message)->then(function() use($pk){
                    $this->resolveRequest($pk->getUID(), true, "Successfully pinned the message.");
                }, function(\Throwable $e) use($pk){
                    $this->resolveRequest($pk->getUID(), false, "Failed to pin the message.", [$e->getMessage(), $e->getTraceAsString()]);
                    $this->logger->debug("Failed to pin the message ({$pk->getUID()}) - {$e->getMessage()}");
                });
            });
        });
    }

    private function handleLeaveGuild(RequestLeaveGuild $pk): void{
        $this->getGuild($pk, $pk->getGuildId(), function(DiscordGuild $guild) use($pk){
            $this->client->getDiscordClient()->guilds->leave($guild)->then(function() use($pk){
                $this->resolveRequest($pk->getUID());
            }, function(\Throwable $e) use($pk){
                //Shouldn't happen unless not in guild/connection issues.
                $this->resolveRequest($pk->getUID(), false, "Failed to leave guild.", [$e->getMessage(), $e->getTraceAsString()]);
                $this->logger->debug("Failed to leave guild? ({$pk->getUID()}) - {$e->getMessage()}");
            });
        });
    }

    private function handleCreateRole(RequestCreateRole $pk): void{
        $this->getGuild($pk, $pk->getGuildId(), function(DiscordGuild $guild) use($pk){
            $guild->createRole([
                'name' => $pk->getName(),
                'color' => $pk->getColour(),
                'permissions' => $pk->getPermissions()->getBitwise(),
                'hoist' => $pk->getHoist(),
                'icon' => $pk->getIconHash(),
                'unicode_emoji' => $pk->getUnicodeEmoji(),
                'mentionable' => $pk->getMentionable()
            ], $pk->getReason())->then(function(DiscordRole $role) use($pk){
                $this->resolveRequest($pk->getUID(), true, "Created role.", [ModelConverter::genModelRole($role)]);
            }, function(\Throwable $e) use($pk){
                $this->resolveRequest($pk->getUID(), false, "Failed to create role.", [$e->getMessage(), $e->getTraceAsString()]);
                $this->logger->debug("Failed to create role ({$pk->getUID()}) - {$e->getMessage()}");
            });
        });
    }

    private function handleUpdateRolePosition(Role $role): PromiseInterface{
        if($role->getId() === $role->getGuildId()){
            return reject(new ApiRejection("Cannot move the default 'everyone' role."));
        }
        $promise = new Deferred();

        $this->client->getDiscordClient()->guilds->fetch($role->getGuildId())->done(function(DiscordGuild $guild) use($promise, $role){
            //Sort
            $arr = $guild->roles->toArray();
            $keys = array_values(array_map(function(DiscordRole $role){
                return $role->position;
            }, $arr));
            $val = array_keys($arr);
            try{
                $data = array_combine($keys, $val); //Throws valueError on >= PHP8, returns false on < PHP8.
            }catch(\ValueError $e){
                $promise->reject(new ApiRejection("Internal error occurred while updating role positions. (" . $e->getMessage() . ")"));
                return;
            }
            /** @var DiscordRole|null $k */
            $k = $arr[$role->getId()];
            if($k === null){
                $promise->reject(new ApiRejection("Cannot update role positions, role not found."));
                return;
            }
            //shift
            $diff = $role->getPosition() - $k->position; //How much are we shifting.
            if($diff === 0){
                $this->logger->debug("Not updating role position ({$k->id}), no difference found.");
                $promise->resolve();
                return;
            }
            $v = $k->id;
            $k = $k->position;
            if($diff > 0){
                for($i = $k + 1; $i <= $k + $diff; $i++){
                    $data[$i - 1] = $data[$i];
                }
                $data[$k + $diff] = $v;
            }else{
                for($i = $k - 1; $i >= $k + $diff; $i--){
                    $data[$i + 1] = $data[$i];
                }
                $data[$k + $diff] = $v;
            }
            //save
            $guild->updateRolePositions($data)->then(function(DiscordGuild $guild) use($promise){
                $promise->resolve();
            }, function(\Throwable $e) use($promise){
                $promise->reject(new ApiRejection("Failed to update role positions.", [$e->getMessage(), $e->getTraceAsString()]));
                $this->logger->debug("Failed to update role positions, error: {$e->getMessage()}");
            });
        }, function(\Throwable $e) use($promise){
            $promise->reject(new ApiRejection("Failed to fetch guild.", [$e->getMessage(), $e->getTraceAsString()]));
            $this->logger->debug("Failed to update role position - guild error: {$e->getMessage()}");
        });

        return $promise->promise();
    }

    private function handleUpdateRole(RequestUpdateRole $pk): void{
        $this->getGuild($pk, $pk->getRole()->getGuildId(), function(DiscordGuild $guild) use($pk){
            $guild->roles->fetch($pk->getRole()->getId())->then(function(DiscordRole $role) use($guild, $pk){
                $role->position = $pk->getRole()->getPosition();
                $role->hoist = $pk->getRole()->getHoist();
                /** @phpstan-ignore-next-line */
                $role->icon = $pk->getNewIconData() ?? $pk->getRole()->getIcon();
                /** @phpstan-ignore-next-line Setting undefined property. */
                $role->unicode_emoji = $pk->getRole()->getUnicodeEmoji();
                $role->mentionable = $pk->getRole()->getMentionable();
                $role->name = $pk->getRole()->getName();
                $role->color = $pk->getRole()->getColour();
                $role->permissions->bitwise = $pk->getRole()->getPermissions()->getBitwise();
                $guild->roles->save($role, $pk->getReason())->then(function(DiscordRole $role) use($pk){
                    if($pk->getRole()->getId() !== $pk->getRole()->getGuildId()){
                        $this->handleUpdateRolePosition($pk->getRole())->then(function() use ($role, $pk){
                            $this->resolveRequest($pk->getUID(), true, "Updated role & position.", [ModelConverter::genModelRole($role)]);
                        }, function(ApiRejection $rejection) use ($pk){
                            $this->resolveRequest($pk->getUID(), false, "Updated role however failed to update position: " . $rejection->getMessage(), [$rejection->getMessage(), $rejection->getTraceAsString()]);
                        });
                    }else{
                        $this->resolveRequest($pk->getUID(), true, "Updated role.", [ModelConverter::genModelRole($role)]);
                    }
                }, function(\Throwable $e) use($pk){
                    $this->resolveRequest($pk->getUID(), false, "Failed to update role.", [$e->getMessage(), $e->getTraceAsString()]);
                    $this->logger->debug("Failed to create role ({$pk->getUID()}) - {$e->getMessage()}");
                });
            }, function(\Throwable $e) use($pk){
                $this->resolveRequest($pk->getUID(), false, "Failed to update role.", [$e->getMessage(), $e->getTraceAsString()]);
                $this->logger->debug("Failed to update role ({$pk->getUID()}) - role error: {$e->getMessage()}");
            });
        });
    }

    private function handleDeleteRole(RequestDeleteRole $pk): void{
        $this->getGuild($pk, $pk->getGuildId(), function(DiscordGuild $guild) use($pk){
            $guild->roles->fetch($pk->getRoleId())->then(function(DiscordRole $role) use($pk, $guild){
                $guild->roles->delete($role, $pk->getReason())->then(function() use($pk){
                    $this->resolveRequest($pk->getUID(), true, "Deleted role.");
                }, function(\Throwable $e) use($pk){
                    $this->resolveRequest($pk->getUID(), false, "Failed to delete role.", [$e->getMessage(), $e->getTraceAsString()]);
                    $this->logger->debug("Failed to delete role ({$pk->getUID()}) - {$e->getMessage()}");
                });
            }, function(\Throwable $e) use($pk){
                $this->resolveRequest($pk->getUID(), false, "Failed to fetch role.", [$e->getMessage(), $e->getTraceAsString()]);
                $this->logger->debug("Failed to delete role ({$pk->getUID()}) - fetch role: {$e->getMessage()}");
            });
        });
    }

    private function handleRemoveRole(RequestRemoveRole $pk): void{
        $this->getMember($pk, $pk->getGuildId(), $pk->getUserId(), function(DiscordMember $dMember) use($pk){
            $dMember->removeRole($pk->getRoleId(), $pk->getReason())->done(function() use($pk){
                $this->resolveRequest($pk->getUID(), true, "Removed role.");
            }, function(\Throwable $e) use($pk){
                $this->resolveRequest($pk->getUID(), false, "Failed to remove role.", [$e->getMessage(), $e->getTraceAsString()]);
                $this->logger->debug("Failed to remove role ({$pk->getUID()}) - {$e->getMessage()}");
            });
        });
    }

    private function handleAddRole(RequestAddRole $pk): void{
        $this->getMember($pk, $pk->getGuildId(), $pk->getUserId(), function(DiscordMember $dMember) use($pk){
            $dMember->addRole($pk->getRoleId(), $pk->getReason())->done(function() use($pk){
                $this->resolveRequest($pk->getUID(), true, "Added role.");
            }, function(\Throwable $e) use($pk){
                $this->resolveRequest($pk->getUID(), false, "Failed to add role.", [$e->getMessage(), $e->getTraceAsString()]);
                $this->logger->debug("Failed to add role ({$pk->getUID()}) - {$e->getMessage()}");
            });
        });
    }

    private function handleRemoveReaction(RequestRemoveReaction $pk): void{
        $this->getMessage($pk, $pk->getChannelId(), $pk->getMessageId(), function(DiscordMessage $msg) use($pk){
            $msg->deleteReaction($pk->getUserId() === $this->client->getDiscordClient()->id ? DiscordMessage::REACT_DELETE_ME : DiscordMessage::REACT_DELETE_ID, $pk->getEmoji(), $pk->getUserId())->then(function() use($pk){
                $this->resolveRequest($pk->getUID(), true, "Successfully removed reaction.");
            }, function(\Throwable $e) use($pk){
                $this->resolveRequest($pk->getUID(), false, "Failed to remove reaction.", [$e->getMessage(), $e->getTraceAsString()]);
                $this->logger->debug("Failed to remove reaction ({$pk->getUID()}) - {$e->getMessage()}");
            });
        });
    }

    private function handleRemoveAllReactions(RequestRemoveAllReactions $pk): void{
        $this->getMessage($pk, $pk->getChannelId(), $pk->getMessageId(), function(DiscordMessage $msg) use($pk){
            $msg->deleteReaction(($e = $pk->getEmoji()) === null ? DiscordMessage::REACT_DELETE_ALL : DiscordMessage::REACT_DELETE_EMOJI, $e)->then(function() use($pk, $e){
                $this->resolveRequest($pk->getUID(), true, "Successfully bulk removed all " . ($e === null ? "" : "'$e' ") . "reactions");
            }, function(\Throwable $e) use($pk){
                $this->resolveRequest($pk->getUID(), false, "Failed to bulk remove reactions.", [$e->getMessage(), $e->getTraceAsString()]);
                $this->logger->debug("Failed to bulk remove reactions ({$pk->getUID()}) - {$e->getMessage()}");
            });
        });
    }

    private function handleAddReaction(RequestAddReaction $pk): void{
        $this->getMessage($pk, $pk->getChannelId(), $pk->getMessageId(), function(DiscordMessage $msg) use($pk){
            $msg->react($pk->getEmoji())->then(function() use($pk){
                $this->resolveRequest($pk->getUID(), true, "Reaction added.");
            }, function(\Throwable $e) use($pk){
                $this->resolveRequest($pk->getUID(), false, "Failed to react to message.", [$e->getMessage(), $e->getTraceAsString()]);
                $this->logger->debug("Failed to react to message ({$pk->getUID()}) - {$e->getMessage()}");
            });
        });
    }

    private function handleCreateThread(RequestCreateThread $pk): void{
        $this->getChannel($pk, $pk->getChannelId(), function(DiscordChannel $channel) use($pk){
            $channel->startThread([
                "name" => $pk->getName(),
                "invitable" => $pk->getInvitable(),
                "auto_archive_duration" => $pk->getAutoArchiveDuration(),
                "rate_limit_per_user" => $pk->getRateLimitPerUser(),
                "private" => ($pk->getType()->value === DiscordChannel::TYPE_PRIVATE_THREAD)
            ], $pk->getReason())->then(function(DiscordChannel $channel) use($pk){
                $this->resolveRequest($pk->getUID(), true, "Created thread.", [ModelConverter::genModelChannel($channel)]);
            }, function(\Throwable $e) use($pk){
                $this->resolveRequest($pk->getUID(), false, "Failed to create thread.", [$e->getMessage(), $e->getTraceAsString()]);
                $this->logger->debug("Failed to create thread ({$pk->getUID()}) - {$e->getMessage()}");
            });
        });
    }

    private function handleCreateThreadFromMessage(RequestCreateThreadFromMessage $pk): void{
        $this->getMessage($pk, $pk->getChannelId(), $pk->getMessageId(), function(DiscordMessage $msg) use($pk){
            $msg->startThread([
                "name" => $pk->getName(),
                "auto_archive_duration" => $pk->getAutoArchiveDuration(),
                "rate_limit_per_user" => $pk->getRateLimitPerUser()
            ], $pk->getReason())->then(function(DiscordChannel $channel) use($pk){
                $this->resolveRequest($pk->getUID(), true, "Created thread.", [ModelConverter::genModelChannel($channel)]);
            }, function(\Throwable $e) use($pk){
                $this->resolveRequest($pk->getUID(), false, "Failed to create thread.", [$e->getMessage(), $e->getTraceAsString()]);
                $this->logger->debug("Failed to create thread ({$pk->getUID()}) - {$e->getMessage()}");
            });
        });
    }

    private function handleCreateChannel(RequestCreateChannel $pk): void{
        $this->getGuild($pk, $pk->getGuildId(), function(DiscordGuild $guild) use($pk){
            $tags = [];
            if(($atags = $pk->getAvailableTags()) !== null){
                $tags = array_map(function(ForumTag $tag){
                    return new DiscordForumTag($this->client->getDiscordClient(), [
                        "id" => $tag->getId(),
                        "name" => $tag->getName(),
                        "moderated" => $tag->getModerated(),
                        "emoji_id" => $tag->getEmojiId(),
                        "emoji_name" => $tag->getEmojiName()
                    ]);
                }, $atags);
            }
            /** @var DiscordChannel $dc */
            $dc = $guild->channels->create([
                "guild_id" => $pk->getGuildId(),
                "name" => $pk->getName(),
                "type" => $pk->getType()->value,
                "topic" => $pk->getTopic(),
                "bitrate" => $pk->getBitrate(),
                "user_limit" => $pk->getUserLimit(),
                "rate_limit_per_user" => $pk->getRateLimitPerUser(),
                "position" => $pk->getPosition(),
                "parent_id" => $pk->getParentId(),
                "nsfw" => $pk->getNsfw(),
                "rtc_region" => $pk->getRtcRegion(),
                "video_quality_mode" => $pk->getVideoQualityMode()?->value,
                "available_tags" => $tags
            ]);
            foreach($pk->getPermissionOverwrites() as $overwrite){
                $dc->overwrites->push($dc->overwrites->create([
                    'id' => $overwrite->getId(),
                    "type" => $overwrite->getType()->value,
                    "allow" => $overwrite->getAllow()->getBitwise(),
                    "deny" => $overwrite->getDeny()->getBitwise()
                ]));
            }
            $guild->channels->save($dc)->then(function(DiscordChannel $channel) use($pk){
                $this->resolveRequest($pk->getUID(), true, "Created channel.", [ModelConverter::genModelChannel($channel)]);
            }, function(\Throwable $e) use($pk){
                $this->resolveRequest($pk->getUID(), false, "Failed to create channel.", [$e->getMessage(), $e->getTraceAsString()]);
                $this->logger->debug("Failed to create channel ({$pk->getUID()}) - {$e->getMessage()}");
            });
        });
    }

    private function handleUpdateChannel(RequestUpdateChannel $pk): void{
        if($pk->getChannel()->getGuildId() === null){
            $this->resolveRequest($pk->getUID(), false, "Failed to update channel.", ["Guild ID must be present."]);
            return;
        }
        $this->getGuild($pk, $pk->getChannel()->getGuildId(), function(DiscordGuild $guild) use($pk){
            $guild->channels->fetch($pk->getChannel()->getId())->then(function(DiscordChannel $dc) use($guild, $pk){
                $channel = $pk->getChannel();
                /** @phpstan-ignore-next-line undefined property $dc->name */
                $dc->name = $channel->getName();
                $dc->position = $channel->getPosition();
                /** @phpstan-ignore-next-line undefined property $dc->topic */
                $dc->topic = $channel->getTopic();
                $dc->nsfw = $channel->getNsfw();
                $dc->rate_limit_per_user = $channel->getRateLimitPerUser();
                $dc->bitrate = $channel->getBitrate();
                $dc->user_limit = $channel->getUserLimit();
                /** @phpstan-ignore-next-line undefined property $dc->parent_id */
                $dc->parent_id = $channel->getParentId();
                /** @phpstan-ignore-next-line undefined property $dc->rtc_region */
                $dc->rtc_region = $channel->getRtcRegion();
                $dc->video_quality_mode = $channel->getVideoQualityMode()?->value;
                $dc->flags = $channel->getFlags();
                $dc->available_tags->clear();
                if(($atags = $channel->getAvailableTags()) !== null){
                    foreach($atags as $tag){
                        $dc->available_tags->push(new DiscordForumTag($this->client->getDiscordClient(), [
                            "id" => $tag->getId(),
                            "name" => $tag->getName(),
                            "moderated" => $tag->getModerated(),
                            "emoji_id" => $tag->getEmojiId(),
                            "emoji_name" => $tag->getEmojiName()
                        ]));
                    }
                }

                $dc->overwrites->cache->clear()->then(function() use($guild, $pk, $channel, $dc){
                    foreach(($channel->getPermissionOverwrites() ?? []) as $overwrite){
                        $dc->overwrites->push($dc->overwrites->create([
                            'id' => $overwrite->getId(),
                            "type" => $overwrite->getType()->value,
                            "allow" => $overwrite->getAllow()->getBitwise(),
                            "deny" => $overwrite->getDeny()->getBitwise()
                        ]));
                    }
                    $guild->channels->save($dc, $pk->getReason())->then(function(DiscordChannel $channel) use($pk){
                        $this->resolveRequest($pk->getUID(), true, "Updated channel.", [ModelConverter::genModelChannel($channel)]);
                    }, function(\Throwable $e) use($pk){
                        $this->resolveRequest($pk->getUID(), false, "Failed to update channel.", [$e->getMessage(), $e->getTraceAsString()]);
                        $this->logger->debug("Failed to update channel ({$pk->getUID()}) - {$e->getMessage()}");
                    });
                }, function(\Throwable $e) use($pk){
                    $this->logger->error("Failed to clear channel overwrite cache - {$e->getMessage()}");
                    $this->resolveRequest($pk->getUID(), false, "Failed to update channel.", ["Channel permission overwrites failed to update."]);
                    return;
                });
            }, function(\Throwable $e) use($pk){
                $this->resolveRequest($pk->getUID(), false, "Failed to update channel.", [$e->getMessage(), $e->getTraceAsString()]);
                $this->logger->debug("Failed to update channel ({$pk->getUID()}) - channel error: {$e->getMessage()}");
            });
        });
    }

    private function handleDeleteChannel(RequestDeleteChannel $pk): void{
        $this->getGuild($pk, $pk->getGuildId(), function(DiscordGuild $guild) use($pk){
            $this->getChannel($pk, $pk->getChannelId(), function(DiscordChannel $channel) use($guild, $pk){
                $guild->channels->delete($channel, $pk->getReason())->then(function() use($pk){
                    $this->resolveRequest($pk->getUID(), true, "Channel deleted.");
                }, function(\Throwable $e) use($pk){
                    $this->resolveRequest($pk->getUID(), false, "Failed to delete channel.", [$e->getMessage(), $e->getTraceAsString()]);
                    $this->logger->debug("Failed to delete channel ({$pk->getUID()}) - {$e->getMessage()}");
                });
            });
        });
    }

    private function handleBroadcastTyping(RequestBroadcastTyping $pk): void{
        $this->getChannel($pk, $pk->getChannelId(), function(DiscordChannel $channel) use($pk){
            $channel->broadcastTyping()->done(function() use($pk){
                $this->resolveRequest($pk->getUID());
                $this->logger->debug("BroadcastTyping - success ({$pk->getUID()})");
            }, function(\Throwable $e) use($pk){
                $this->resolveRequest($pk->getUID(), false, "Failed to broadcast typing.", [$e->getMessage(), $e->getTraceAsString()]);
                $this->logger->debug("Failed to broadcast typing ({$pk->getUID()}) - {$e->getMessage()}");
            });
        });
    }

    private function handleUpdateNickname(RequestUpdateNickname $pk): void{
        $this->getMember($pk, $pk->getGuildId(), $pk->getUserId(), function(DiscordMember $dMember) use($pk){
            $dMember->setNickname($pk->getNickname(), $pk->getReason())->done(function() use($pk){
                $this->resolveRequest($pk->getUID(), true, "Updated nickname.");
            }, function(\Throwable $e) use($pk){
                $this->resolveRequest($pk->getUID(), false, "Failed to update nickname.", [$e->getMessage(), $e->getTraceAsString()]);
                $this->logger->debug("Failed to update nickname ({$pk->getUID()}) - {$e->getMessage()}");
            });
        });
    }

    private function handleUpdateBotPresence(RequestUpdateBotPresence $pk): void{
        $presence = $pk->getPresence();
        $activity = $presence->getActivities()[0] ?? null;
        $dactivity = null;
        if($activity !== null){
            $dactivity = new DiscordActivity($this->client->getDiscordClient(), [
                'name' => $activity->getName(),
                'type' => $activity->getType()->value,
                'url' => $activity->getUrl()
            ]);
        }

        try{
            $this->client->getDiscordClient()->updatePresence($dactivity, $presence->getStatus() === Status::IDLE, $presence->getStatus()->value);
            $this->resolveRequest($pk->getUID());
        }catch (\Throwable $e){
            $this->resolveRequest($pk->getUID(), false, $e->getMessage());
        }
    }

    private function handleSendMessage(RequestSendMessage $pk): void{
        $this->getChannel($pk, $pk->getChannelId(), function(DiscordChannel $channel) use($pk){
            $message = DiscordMessageBuilder::new();
            if(($content = $pk->getContent()) !== null){
                $message->setContent($content);
            }
            if(($tts = $pk->getTts()) !== null){
                $message->setTts($tts);
            }
            foreach(($pk->getEmbeds() ?? []) as $embed){
                $e = new DiscordEmbed($this->client->getDiscordClient());
                if(($title = $embed->getTitle()) !== null){
                    $e->setTitle($title);
                }
                if(($colour = $embed->getColour()) !== null){
                    $e->setColor($colour);
                }
                if(($desc = $embed->getDescription()) !== null){
                    $e->setDescription($desc);
                }
                if(($url = $embed->getUrl()) !== null){
                    $e->setURL($url);
                }
                if(($time = $embed->getTimestamp()) !== null){
                    try{
                        $e->setTimestamp($time);
                    }catch(\Throwable){}
                }
                if(($author = $embed->getAuthor()) !== null){
                    $e->setAuthor($author->getName(), $author->getIconUrl(), $author->getUrl());
                }
                if(($footer = $embed->getFooter()) !== null){
                    $e->setFooter($footer->getText(), $footer->getIconUrl());
                }
                if(($image = $embed->getImage()) !== null){
                    $e->setImage($image->getUrl());
                }
                if(($thumb = $embed->getThumbnail()) !== null){
                    $e->setThumbnail($thumb->getUrl());
                }
                foreach($embed->getFields() as $field){
                    $e->addFieldValues($field->getName(), $field->getValue(), $field->getInline());
                }
                $message->addEmbed($e);
            }
            foreach(($pk->getComponents() ?? []) as $component){
                $all = $component->getComponents();
                //A bit annoying but DiscordPHP doesn't do it like discord does, they put SelectMenu into ActionRow for us...
                //So we have to take it OUT of our ActionRow so DiscordPHP can put it back in...
                if(($raw_c = $all[0] ?? null) instanceof SelectMenu){
                    $c = null;
                    if($raw_c->getType() === ComponentType::CHANNEL_SELECT){
                        $c = new DiscordChannelSelect($raw_c->getCustomId());
                        $c->setChannelTypes(array_map(fn($v) => $v->value, $raw_c->getChannelTypes()));
                    }elseif($raw_c->getType() === ComponentType::ROLE_SELECT){
                        $c = new DiscordRoleSelect($raw_c->getCustomId());
                    }elseif($raw_c->getType() === ComponentType::USER_SELECT){
                        $c = new DiscordUserSelect($raw_c->getCustomId());
                    }elseif($raw_c->getType() === ComponentType::MENTIONABLE_SELECT){
                        $c = new DiscordMentionableSelect($raw_c->getCustomId());
                    }elseif($raw_c->getType() === ComponentType::STRING_SELECT){
                        $c = new DiscordStringSelect($raw_c->getCustomId());
                        foreach($raw_c->getOptions() as $option){
                            $opt = new DiscordOption($option->getLabel(), $option->getValue());
                            $opt->setDescription($option->getDescription());
                            if(($emoji = $option->getEmoji()) !== null){
                                $e = new DiscordEmoji($this->client->getDiscordClient(), [
                                    "id" => $emoji->getId(),
                                    "name" => $emoji->getName(),
                                    "animated" => $emoji->getAnimated()
                                ]);
                                $opt->setEmoji($e);
                            }
                            if(($def = $option->getDefault()) !== null){
                                $opt->setDefault($def);
                            }
                            $c->addOption($opt);
                        }
                    }else{
                        $this->logger->warning("Unknown select menu type: {$raw_c->getType()->name}");
                        continue;
                    }
                    $c->setPlaceholder($raw_c->getPlaceholder());
                    $c->setMinValues($raw_c->getMinValues());
                    $c->setMaxValues($raw_c->getMaxValues());
                    $c->setDisabled($raw_c->getDisabled());
                    $message->addComponent($c);
                    continue;
                }elseif($raw_c !== null && !($raw_c instanceof Button)){
                    $this->logger->warning("Unknown component type: " . get_class($raw_c));
                    continue;
                }
                $c = new DiscordActionRow();
                /** @var Button $raw */
                foreach($all as $raw){
                    $button = new DiscordButton($raw->getStyle()->value, $raw->getCustomId());
                    $button->setDisabled($raw->getDisabled());
                    $button->setLabel($raw->getLabel());
                    if(($emoji = $raw->getEmoji()) !== null){
                        $e = new DiscordEmoji($this->client->getDiscordClient(), [
                            "id" => $emoji->getId(),
                            "name" => $emoji->getName(),
                            "animated" => $emoji->getAnimated()
                        ]);
                        $button->setEmoji($e);
                    }
                    if($raw->getUrl() !== null){
                        $button->setUrl($raw->getUrl());
                    }
                    $c->addComponent($button);
                }
                $message->addComponent($c);
            }
            $message->setStickers($pk->getStickerIds() ?? []);
            foreach(($pk->getFiles() ?? []) as $file_name => $file_data){
                $message->addFileFromContent($file_name, $file_data);
            }

            if($pk->getReplyMessageId() !== null){
                $this->getMessage($pk, $pk->getChannelId(), $pk->getReplyMessageId(), function(DiscordMessage $reply) use($channel, $message, $pk){
                    $message->setReplyTo($reply);
                    $channel->sendMessage($message)->then(function(DiscordMessage $message) use($pk){
                        $this->resolveRequest($pk->getUID(), true, "Successfully sent message.", [ModelConverter::genModelMessage($message)]);
                    }, function(\Throwable $e) use($pk){
                        $this->resolveRequest($pk->getUID(), false, "Failed to send message.", [$e->getMessage(), $e->getTraceAsString()]);
                        $this->logger->debug("Failed to send message ({$pk->getUID()}) - {$e->getMessage()}");
                    });
                });
            }else{
                $channel->sendMessage($message)->then(function(DiscordMessage $message) use($pk){
                    $this->resolveRequest($pk->getUID(), true, "Successfully sent message.", [ModelConverter::genModelMessage($message)]);
                }, function(\Throwable $e) use($pk){
                    $this->resolveRequest($pk->getUID(), false, "Failed to send message.", [$e->getMessage(), $e->getTraceAsString()]);
                    $this->logger->debug("Failed to send message ({$pk->getUID()}) - {$e->getMessage()}");
                });
            }
        });
    }

    private function handleEditMessage(RequestEditMessage $pk): void{
        $message = $pk->getMessage();
        $this->getMessage($pk, $message->getChannelId(), $message->getId(), function(DiscordMessage $dMessage) use($pk, $message){
            $builder = DiscordMessageBuilder::new();
            if(($content = $message->getContent()) !== null){
                $builder->setContent($content);
            }
            $builder->setTts($message->getTts());
            foreach($message->getEmbeds() as $embed){
                $e = new DiscordEmbed($this->client->getDiscordClient());
                if(($title = $embed->getTitle()) !== null){
                    $e->setTitle($title);
                }
                if(($colour = $embed->getColour()) !== null){
                    $e->setColor($colour);
                }
                if(($desc = $embed->getDescription()) !== null){
                    $e->setDescription($desc);
                }
                if(($url = $embed->getUrl()) !== null){
                    $e->setURL($url);
                }
                if(($time = $embed->getTimestamp()) !== null){
                    try{
                        $e->setTimestamp($time);
                    }catch(\Throwable){}
                }
                if(($author = $embed->getAuthor()) !== null){
                    $e->setAuthor($author->getName(), $author->getIconUrl(), $author->getUrl());
                }
                if(($footer = $embed->getFooter()) !== null){
                    $e->setFooter($footer->getText(), $footer->getIconUrl());
                }
                if(($image = $embed->getImage()) !== null){
                    $e->setImage($image->getUrl());
                }
                if(($thumb = $embed->getThumbnail()) !== null){
                    $e->setThumbnail($thumb->getUrl());
                }
                foreach($embed->getFields() as $field){
                    $e->addFieldValues($field->getName(), $field->getValue(), $field->getInline());
                }
                $builder->addEmbed($e);
            }
            foreach($message->getComponents() as $component){
                $all = $component->getComponents();
                //A bit annoying but DiscordPHP doesn't do it like discord does, they put SelectMenu into ActionRow for us...
                //So we have to take it OUT of our ActionRow so DiscordPHP can put it back in...
                if(($raw_c = $all[0] ?? null) instanceof SelectMenu){
                    $c = null;
                    if($raw_c->getType() === ComponentType::CHANNEL_SELECT){
                        $c = new DiscordChannelSelect($raw_c->getCustomId());
                        $c->setChannelTypes(array_map(fn($v) => $v->value, $raw_c->getChannelTypes()));
                    }elseif($raw_c->getType() === ComponentType::ROLE_SELECT){
                        $c = new DiscordRoleSelect($raw_c->getCustomId());
                    }elseif($raw_c->getType() === ComponentType::USER_SELECT){
                        $c = new DiscordUserSelect($raw_c->getCustomId());
                    }elseif($raw_c->getType() === ComponentType::MENTIONABLE_SELECT){
                        $c = new DiscordMentionableSelect($raw_c->getCustomId());
                    }elseif($raw_c->getType() === ComponentType::STRING_SELECT){
                        $c = new DiscordStringSelect($raw_c->getCustomId());
                        foreach($raw_c->getOptions() as $option){
                            $opt = new DiscordOption($option->getLabel(), $option->getValue());
                            $opt->setDescription($option->getDescription());
                            if(($emoji = $option->getEmoji()) !== null){
                                $e = new DiscordEmoji($this->client->getDiscordClient(), [
                                    "id" => $emoji->getId(),
                                    "name" => $emoji->getName(),
                                    "animated" => $emoji->getAnimated()
                                ]);
                                $opt->setEmoji($e);
                            }
                            if(($def = $option->getDefault()) !== null){
                                $opt->setDefault($def);
                            }
                            $c->addOption($opt);
                        }
                    }else{
                        $this->logger->warning("Unknown select menu type: {$raw_c->getType()->name}");
                        continue;
                    }
                    $c->setPlaceholder($raw_c->getPlaceholder());
                    $c->setMinValues($raw_c->getMinValues());
                    $c->setMaxValues($raw_c->getMaxValues());
                    $c->setDisabled($raw_c->getDisabled());
                    $builder->addComponent($c);
                    continue;
                }elseif($raw_c !== null && !($raw_c instanceof Button)){
                    $this->logger->warning("Unknown component type: " . get_class($raw_c));
                    continue;
                }
                $c = new DiscordActionRow();
                /** @var Button $raw */
                foreach($all as $raw){
                    $button = new DiscordButton($raw->getStyle()->value, $raw->getCustomId());
                    $button->setDisabled($raw->getDisabled());
                    $button->setLabel($raw->getLabel());
                    if(($emoji = $raw->getEmoji()) !== null){
                        $e = new DiscordEmoji($this->client->getDiscordClient(), [
                            "id" => $emoji->getId(),
                            "name" => $emoji->getName(),
                            "animated" => $emoji->getAnimated()
                        ]);
                        $button->setEmoji($e);
                    }
                    if($raw->getUrl() !== null){
                        $button->setUrl($raw->getUrl());
                    }
                    $c->addComponent($button);
                }
                $builder->addComponent($c);
            }
            //TODO Stickers & Files.

            if($message->getMessageReference()?->getChannelId() !== null && $message->getMessageReference()->getMessageId() !== null){
                $this->getMessage($pk, $message->getMessageReference()->getChannelId(), $message->getMessageReference()->getMessageId(), function(DiscordMessage $reply) use($dMessage, $builder, $pk){
                    $builder->setReplyTo($reply);
                    $dMessage->edit($builder)->then(function(DiscordMessage $message) use($pk){
                        $this->resolveRequest($pk->getUID(), true, "Successfully edited message.", [ModelConverter::genModelMessage($message)]);
                    }, function(\Throwable $e) use($pk){
                        $this->resolveRequest($pk->getUID(), false, "Failed to edit message.", [$e->getMessage(), $e->getTraceAsString()]);
                        $this->logger->debug("Failed to edit message ({$pk->getUID()}) - {$e->getMessage()}");
                    });
                });
            }else{
                $dMessage->edit($builder)->then(function(DiscordMessage $message) use($pk){
                    $this->resolveRequest($pk->getUID(), true, "Successfully edited message.", [ModelConverter::genModelMessage($message)]);
                }, function(\Throwable $e) use($pk){
                    $this->resolveRequest($pk->getUID(), false, "Failed to edit message.", [$e->getMessage(), $e->getTraceAsString()]);
                    $this->logger->debug("Failed to edit message ({$pk->getUID()}) - {$e->getMessage()}");
                });
            }
        });
    }

    private function handleDeleteMessage(RequestDeleteMessage $pk): void{
        $this->getChannel($pk, $pk->getChannelId(), function(DiscordChannel $channel) use($pk){
            $channel->messages->delete($pk->getMessageId(), $pk->getReason())->done(function() use($pk){
                $this->resolveRequest($pk->getUID(), true, "Message deleted.");
            }, function(\Throwable $e) use ($pk){
                $this->resolveRequest($pk->getUID(), false, "Failed to delete message.", [$e->getMessage(), $e->getTraceAsString()]);
                $this->logger->debug("Failed to delete message ({$pk->getUID()}) - {$e->getMessage()}");
            });
        });
    }

    private function handleBulkDeleteMessages(RequestBulkDeleteMessages $pk): void{
        $this->getChannel($pk, $pk->getChannelId(), function(DiscordChannel $channel) use($pk){
            $channel->deleteMessages($pk->getMessageIds(), $pk->getReason())->done(function() use($pk){
                $this->resolveRequest($pk->getUID(), true, "Messages bulk deleted.");
            }, function(\Throwable $e) use ($pk){
                $this->resolveRequest($pk->getUID(), false, "Failed to bulk delete messages.", [$e->getMessage(), $e->getTraceAsString()]);
                $this->logger->debug("Failed to delete messages ({$pk->getUID()}) - {$e->getMessage()}");
            });
        });
    }

    private function handleKickMember(RequestKickMember $pk): void{
        $this->getMember($pk, $pk->getGuildId(), $pk->getUserId(), function(DiscordMember $member, DiscordGuild $guild) use($pk){
            $guild->members->kick($member, $pk->getReason())->then(function() use($pk){
                $this->resolveRequest($pk->getUID(), true, "Member kicked.");
            }, function(\Throwable $e) use($pk){
                $this->resolveRequest($pk->getUID(), false, "Failed to kick member.", [$e->getMessage(), $e->getTraceAsString()]);
                $this->logger->debug("Failed to kick member ({$pk->getUID()}) - {$e->getMessage()}");
            });
        });
    }

    private function handleBanMember(RequestBanMember $pk): void{
        $this->getGuild($pk, $pk->getGuildId(), function(DiscordGuild $guild) use($pk){
            $guild->bans->ban($pk->getUserId(), [
                "delete_message_seconds" => $pk->getDeleteMessageSeconds(),
            ], $pk->getReason())->then(function() use($pk){
                $this->resolveRequest($pk->getUID(), true, "Member banned.");
            }, function(\Throwable $e) use($pk){
                $this->resolveRequest($pk->getUID(), false, "Failed to ban member.", [$e->getMessage(), $e->getTraceAsString()]);
                $this->logger->debug("Failed to ban member ({$pk->getUID()}) - {$e->getMessage()}");
            });
        });
    }

    private function handleUnbanMember(RequestUnbanMember $pk): void{
        $this->getGuild($pk, $pk->getGuildId(), function(DiscordGuild $guild) use($pk){
            $guild->bans->unban($pk->getUserId(), $pk->getReason())->then(function() use($pk){
                $this->resolveRequest($pk->getUID(), true, "Member unbanned.");
            }, function(\Throwable $e) use($pk){
                $this->resolveRequest($pk->getUID(), false, "Failed to unban member.", [$e->getMessage(), $e->getTraceAsString()]);
                $this->logger->debug("Failed to unban member ({$pk->getUID()}) - {$e->getMessage()}");
            });
        });
    }

    private function handleCreateInvite(RequestCreateInvite $pk): void{
        $this->getChannel($pk, $pk->getChannelId(), function(DiscordChannel $channel) use($pk){
            /** @phpstan-ignore-next-line Incorrect typehints used by DiscordPHP */
            $channel->createInvite([
                "max_age" => $pk->getMaxAge(),
                "max_uses" => $pk->getMaxUses(),
                "temporary" => $pk->getTemporary(),
                "unique" => $pk->getUnique()
            ], $pk->getReason())->done(function(DiscordInvite $dInvite) use($pk){
                $this->resolveRequest($pk->getUID(), true, "Invite created.", [ModelConverter::genModelInvite($dInvite)]);
                $this->logger->debug("Invite created ({$pk->getUID()})");
            }, function(\Throwable $e) use($pk){
                $this->resolveRequest($pk->getUID(), false, "Failed to create Invite.", [$e->getMessage(), $e->getTraceAsString()]);
                $this->logger->debug("Failed to create Invite ({$pk->getUID()}) - {$e->getMessage()}");
            });
        });
    }

    private function handleDeleteInvite(RequestDeleteInvite $pk): void{
        $this->getGuild($pk, $pk->getGuildId(), function(DiscordGuild $guild) use($pk){
            $guild->invites->delete($pk->getInviteCode(), $pk->getReason())->done(function(DiscordInvite $dInvite) use($pk){
                    $this->resolveRequest($pk->getUID(), true, "Invite deleted.");
                    $this->logger->debug("Invite deleted ({$pk->getUID()})");
            }, function(\Throwable $e) use($pk){
                $this->resolveRequest($pk->getUID(), false, "Failed to delete Invite.", [$e->getMessage(), $e->getTraceAsString()]);
                $this->logger->debug("Failed to delete Invite ({$pk->getUID()}) - {$e->getMessage()}");
            });
        });
    }

    //---------------------------------------------------

    /**
     * @param callable(DiscordGuild): void $cb
     * @noinspection PhpDocMissingThrowsInspection (fetch does not throw it but reject promise, but that is handled.)
     */
    private function getGuild(Packet $pk, string $guild_id, callable $cb): void{
        $this->client->getDiscordClient()->guilds->fetch($guild_id)->done(function(DiscordGuild $guild) use($cb){
            $cb($guild);
        }, function(\Throwable $e) use($pk){
            $this->resolveRequest($pk->getUID(), false, "Failed to fetch guild.", [$e->getMessage(), $e->getTraceAsString()]);
            $this->logger->debug("Failed to process request (" . get_class($pk) . "|{$pk->getUID()}) - guild error: {$e->getMessage()}");
        });
    }

    /**
     * Includes DMs
     * @param callable(DiscordChannel): void $cb
     */
    private function getChannel(Packet $pk, string $channel_id, callable $cb): void{
        $c = $this->client->getDiscordClient()->getChannel($channel_id);
        if($c === null){
            /** @var DiscordUser|null $u */
            $u = $this->client->getDiscordClient()->users->get("id", $channel_id);
            if($u === null){
                $this->resolveRequest($pk->getUID(), false, "Failed to find channel/user.", ["Failed to find channel from local storage."]);
                $this->logger->debug("Failed to process request (" . get_class($pk) . "|{$pk->getUID()}) - channel error: Failed to find channel from local storage.");
            }else{
                $u->getPrivateChannel()->then(function(DiscordChannel $channel) use($cb){
                    $cb($channel);
                }, function(\Throwable $e) use($pk){
                    $this->resolveRequest($pk->getUID(), false, "Failed to fetch private channel..", [$e->getMessage(), $e->getTraceAsString()]);
                    $this->logger->debug("Failed to process request (" . get_class($pk) . "|{$pk->getUID()}) - private channel error: {$e->getMessage()}");
                });
            }
        }else{
            $cb($c);
        }
    }

    /**
     * @param callable(DiscordMessage): void $cb
     * @noinspection PhpDocMissingThrowsInspection (fetch does not throw it but reject promise, but that is handled.)
     */
    private function getMessage(Packet $pk, string $channel_id, string $message_id, callable $cb): void{
        $this->getChannel($pk, $channel_id, function(DiscordChannel $channel) use($pk, $message_id, $cb){
            $channel->messages->fetch($message_id)->done(function(DiscordMessage $dMessage) use ($cb){
                $cb($dMessage);
            }, function(\Throwable $e) use ($pk){
                $this->resolveRequest($pk->getUID(), false, "Failed to fetch message.", [$e->getMessage(), $e->getTraceAsString()]);
                $this->logger->debug("Failed to process request (" . get_class($pk) . "|{$pk->getUID()}) - message error: {$e->getMessage()}");
            });
        });
    }

    /**
     * @param callable(DiscordMember, DiscordGuild): void $cb
     */
    private function getMember(Packet $pk, string $guild_id, string $user_id, callable $cb): void{
        $this->getGuild($pk, $guild_id, function(DiscordGuild $guild) use($pk, $user_id, $cb){
            $guild->members->fetch($user_id)->then(function(DiscordMember $member) use($guild, $cb){
                $cb($member, $guild);
            }, function(\Throwable $e) use($pk){
                $this->resolveRequest($pk->getUID(), false, "Failed to fetch member.", [$e->getMessage(), $e->getTraceAsString()]);
                $this->logger->debug("Failed to process request (" . get_class($pk) . "|{$pk->getUID()}) - member error: {$e->getMessage()}");
            });
        });
    }

    //---------------------------------------------------

    private function resolveRequest(int $pid, bool $successful = true, string $response = "Success.", array $data = []): void{
        $pk = new Resolution($pid, $successful, $response, $data);
        $this->client->getThread()->writeOutboundData($pk);
    }

    public function sendHeartbeat(): void{
        $pk = new Heartbeat((int)floor(microtime(true)));
        $this->client->getThread()->writeOutboundData($pk);
    }

    public function checkHeartbeat(): void{
        if($this->lastHeartbeat === null) return;
        if(($diff = (microtime(true) - $this->lastHeartbeat)) > $this->client->getConfig()['protocol']["general"]['heartbeat_allowance']){
            $this->logger->emergency("Plugin has not responded for {$diff} seconds, closing thread.");
            $this->client->close();
        }
    }

    public function getLastHeartbeat(): ?int{
        return $this->lastHeartbeat;
    }
}