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

namespace JaxkDev\DiscordBot\Bot;

use AssertionError;
use Carbon\Carbon;
use Discord\Parts\Channel\Channel as DiscordChannel;
use Discord\Parts\Channel\Message as DiscordMessage;
use Discord\Parts\Channel\Overwrite;
use Discord\Parts\Embed\Author as DiscordAuthor;
use Discord\Parts\Embed\Embed as DiscordEmbed;
use Discord\Parts\Embed\Field as DiscordField;
use Discord\Parts\Embed\Footer as DiscordFooter;
use Discord\Parts\Embed\Image as DiscordImage;
use Discord\Parts\Embed\Video as DiscordVideo;
use Discord\Parts\Guild\Ban as DiscordBan;
use Discord\Parts\Guild\Invite as DiscordInvite;
use Discord\Parts\Guild\Role as DiscordRole;
use Discord\Parts\Permissions\RolePermission as DiscordRolePermission;
use Discord\Parts\User\Activity as DiscordActivity;
use Discord\Parts\User\Member as DiscordMember;
use Discord\Parts\User\User as DiscordUser;
use Discord\Parts\Guild\Guild as DiscordServer;
use JaxkDev\DiscordBot\Models\Activity;
use JaxkDev\DiscordBot\Models\Ban;
use JaxkDev\DiscordBot\Models\Channels\CategoryChannel;
use JaxkDev\DiscordBot\Models\Channels\ServerChannel;
use JaxkDev\DiscordBot\Models\Channels\TextChannel;
use JaxkDev\DiscordBot\Models\Channels\VoiceChannel;
use JaxkDev\DiscordBot\Models\Embed\Author;
use JaxkDev\DiscordBot\Models\Embed\Embed;
use JaxkDev\DiscordBot\Models\Embed\Field;
use JaxkDev\DiscordBot\Models\Embed\Footer;
use JaxkDev\DiscordBot\Models\Embed\Image;
use JaxkDev\DiscordBot\Models\Embed\Video;
use JaxkDev\DiscordBot\Models\Invite;
use JaxkDev\DiscordBot\Models\Member;
use JaxkDev\DiscordBot\Models\Messages\Message;
use JaxkDev\DiscordBot\Models\Messages\Reply;
use JaxkDev\DiscordBot\Models\Messages\Webhook;
use JaxkDev\DiscordBot\Models\Permissions\ChannelPermissions;
use JaxkDev\DiscordBot\Models\Permissions\RolePermissions;
use JaxkDev\DiscordBot\Models\Role;
use JaxkDev\DiscordBot\Models\Server;
use JaxkDev\DiscordBot\Models\User;

abstract class ModelConverter{

	static public function genModelMember(DiscordMember $discordMember): Member{
		$m = new Member();
		$m->setUserId($discordMember->id);
		$m->setServerId($discordMember->guild_id);
		$m->setNickname($discordMember->nick);
		$m->setJoinTimestamp($discordMember->joined_at === null ? 0 : $discordMember->joined_at->getTimestamp());
		$m->setBoostTimestamp($discordMember->premium_since === null ? null : $discordMember->premium_since->getTimestamp());

		$bitwise = $discordMember->guild->roles->offsetGet($discordMember->guild_id)->permissions->bitwise; //Everyone perms.
		$roles = [];

		//O(2n) -> O(n) by using same loop for permissions to add roles.
		if($discordMember->guild->owner_id == $discordMember->id){
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

		$newPermission = new RolePermissions();
		if(($bitwise & RolePermissions::ROLE_PERMISSIONS["administrator"]) !== 0){
			$newPermission->setBitwise(2147483647, false); //All perms.
		}else{
			$newPermission->setBitwise($bitwise, false);
		}

		$m->setPermissions($newPermission);
		$m->setRolesId($roles);
		//todo activities.
		return $m;
	}

	static public function genModelUser(DiscordUser $user): User{
		return new User($user->id, $user->username, $user->discriminator, $user->avatar, (int)$user->createdTimestamp(),
			$user->bot??false, $user->public_flags??0, false);
	}

	static public function genModelServer(DiscordServer $discordServer): Server{
		return new Server($discordServer->id, $discordServer->name, $discordServer->region, $discordServer->owner_id,
			$discordServer->createdTimestamp(), $discordServer->large, $discordServer->member_count, $discordServer->icon);
	}

	/**
	 * @template T of ServerChannel
	 * @param DiscordChannel $dc
	 * @param T $c
	 * @return T
	 */
	static private function applyServerChannelDetails(DiscordChannel $dc, $c){
		if($dc->guild_id === null){
			throw new AssertionError("Guild ID must be present here.");
		}
		$c->setId($dc->id);
		$c->setName($dc->name);
		$c->setPosition($dc->position);
		$c->setServerId($dc->guild_id);
		/** @var Overwrite $overwrite */
		foreach($dc->overwrites as $overwrite){
			$allowed = new ChannelPermissions();
			$allowed->setBitwise($overwrite->allow->bitwise);
			$denied = new ChannelPermissions();
			$denied->setBitwise($overwrite->deny->bitwise);
			if($overwrite->type === Overwrite::TYPE_MEMBER){
				$c->setAllowedMemberPermissions($overwrite->id, $allowed);
				$c->setDeniedMemberPermissions($overwrite->id, $denied);
			}elseif($overwrite->type === Overwrite::TYPE_ROLE){
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
	 * @return ?ServerChannel Null if type is invalid/unused.
	 */
	static public function genModelChannel(DiscordChannel $channel): ?ServerChannel{
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
		return self::applyServerChannelDetails($discordChannel, new CategoryChannel());
	}

	static public function genModelVoiceChannel(DiscordChannel $discordChannel): VoiceChannel{
		if($discordChannel->type !== DiscordChannel::TYPE_VOICE){
			throw new AssertionError("Discord channel type must be `voice` to generate model voice channel.");
		}
		$c = self::applyServerChannelDetails($discordChannel, new VoiceChannel());
		$c->setBitrate($discordChannel->bitrate);
		$c->setMemberLimit($discordChannel->user_limit);
		$c->setMembers(array_keys($discordChannel->members->toArray()));
		$c->setCategoryId($discordChannel->parent_id);
		return $c;
	}

	/**
	 * Excludes pins, that requires a fetch.
	 * @param DiscordChannel $discordChannel
	 * @return TextChannel
	 */
	static public function genModelTextChannel(DiscordChannel $discordChannel): TextChannel{
		if($discordChannel->type !== DiscordChannel::TYPE_TEXT and $discordChannel->type !== DiscordChannel::TYPE_NEWS){
			throw new AssertionError("Discord channel type must be `text|news` to generate model text channel.");
		}
		$c = self::applyServerChannelDetails($discordChannel, new TextChannel());
		$c->setTopic($discordChannel->topic??"");
		$c->setNsfw($discordChannel->nsfw??false);
		$c->setRateLimit($discordChannel->rate_limit_per_user);
		$c->setCategoryId($discordChannel->parent_id);
		//Pins require a fetch.
		return $c;
	}

	static public function genModelMessage(DiscordMessage $discordMessage): Message{
		if($discordMessage->channel->guild_id === null){
			throw new AssertionError("Discord message does not have a guild_id, cannot generate model message.");
		}
		if($discordMessage->author === null){
			throw new AssertionError("Discord message does not have a author, cannot generate model message.");
		}
		if($discordMessage->type === DiscordMessage::TYPE_NORMAL){
			if($discordMessage->webhook_id === null){
				$m = new Message();
				$e = $discordMessage->embeds->first();
				if($e !== null){
					$m->setEmbed(self::genModelEmbed($e));
				}
			}else{
				$m = new Webhook();
				$m->setWebhookId($discordMessage->webhook_id);
				$embeds = [];
				foreach($discordMessage->embeds as $embed){
					$embeds[] = self::genModelEmbed($embed);
				}
				$m->setEmbeds($embeds);
			}
		}elseif($discordMessage->type === DiscordMessage::TYPE_REPLY){
			if($discordMessage->referenced_message === null){
				throw new AssertionError("Error code 0x003 no ref on reply message, if your seeing this please report it on github.");
			}
			$m = new Reply();
			$m->setReferencedMessageId($discordMessage->referenced_message->id);
			$e = $discordMessage->embeds->first();
			if($e !== null){
				$m->setEmbed(self::genModelEmbed($e));
			}
		}else{
			throw new AssertionError("Discord message type not supported.");
		}
		$m->setId($discordMessage->id);
		$m->setTimestamp($discordMessage->timestamp->getTimestamp());
		$m->setAuthorId(($discordMessage->channel->guild_id.".".$discordMessage->author->id));
		$m->setChannelId($discordMessage->channel_id);
		$m->setServerId($discordMessage->channel->guild_id);
		$m->setEveryoneMentioned($discordMessage->mention_everyone);
		$m->setContent($discordMessage->content??"");
		$m->setChannelsMentioned(array_keys($discordMessage->mention_channels->toArray()));
		$m->setRolesMentioned(array_keys($discordMessage->mention_roles->toArray()));
		$m->setUsersMentioned(array_keys($discordMessage->mentions->toArray()));
		return $m;
	}

	static public function genModelEmbed(DiscordEmbed $discordEmbed): Embed{
		$e = new Embed();
		$e->setTitle($discordEmbed->title);
		$e->setType($discordEmbed->type);
		$e->setColour($discordEmbed->color);
		$e->setDescription($discordEmbed->description);
		$e->setUrl($discordEmbed->url);
		$e->setTimestamp($discordEmbed->timestamp instanceof Carbon ? $discordEmbed->timestamp->getTimestamp() : (int)$discordEmbed->timestamp);
		$e->setFooter($discordEmbed->footer === null ? new Footer() : self::genModelEmbedFooter($discordEmbed->footer));
		$e->setImage($discordEmbed->image === null ? new Image() : self::genModelEmbedImage($discordEmbed->image));
		$e->setThumbnail($discordEmbed->thumbnail === null ? new Image() : self::genModelEmbedImage($discordEmbed->thumbnail));
		$e->setVideo($discordEmbed->video === null ? new Video() : self::genModelEmbedVideo($discordEmbed->video));
		$e->setAuthor($discordEmbed->author === null ? new Author() : self::genModelEmbedAuthor($discordEmbed->author));
		$fields = [];
		foreach(array_values($discordEmbed->fields->toArray()) as $field){
			$fields[] = self::genModelEmbedField($field);
		}
		$e->setFields($fields);
		return $e;
	}

	static public function genModelEmbedFooter(DiscordFooter $footer): Footer{
		$f = new Footer();
		$f->setText($footer->text);
		$f->setIconUrl($footer->icon_url);
		return $f;
	}

	static public function genModelEmbedImage(DiscordImage $image): Image{
		$i = new Image();
		$i->setUrl($image->url);
		$i->setWidth($image->width);
		$i->setHeight($image->height);
		return $i;
	}

	static public function genModelEmbedVideo(DiscordVideo $video): Video{
		$v = new Video();
		$v->setUrl($video->url);
		$v->setWidth($video->width);
		$v->setHeight($video->height);
		return $v;
	}

	static public function genModelEmbedAuthor(DiscordAuthor $author): Author{
		$a = new Author();
		$a->setName($author->name);
		$a->setUrl($author->url);
		$a->setIconUrl($author->icon_url);
		return $a;
	}

	static public function genModelEmbedField(DiscordField $field): Field{
		$f = new Field();
		$f->setName($field->name);
		$f->setValue($field->value);
		$f->setInline($field->inline??false);
		return $f;
	}

	static public function genModelRolePermission(DiscordRolePermission $rolePermission): RolePermissions{
		$p = new RolePermissions();
		$p->setBitwise($rolePermission->bitwise);
		return $p;
	}

	static public function genModelRole(DiscordRole $discordRole): Role{
		$r = new Role();
		$r->setId($discordRole->id);
		$r->setServerId($discordRole->guild_id);
		$r->setName($discordRole->name);
		$r->setPermissions(self::genModelRolePermission($discordRole->permissions));
		$r->setMentionable($discordRole->mentionable);
		$r->setHoistedPosition($discordRole->position);
		$r->setColour($discordRole->color);
		return $r;
	}

	static public function genModelInvite(DiscordInvite $invite): Invite{
		return new Invite($invite->guild_id, $invite->channel_id, $invite->max_age, $invite->max_uses, $invite->temporary,
		$invite->code, $invite->created_at->getTimestamp(), $invite->guild_id.".".$invite->inviter->id, $invite->uses);
	}

	static public function genModelBan(DiscordBan $ban): Ban{
		$b = new Ban();
		$b->setServerId($ban->guild_id);
		$b->setUserId($ban->user_id);
		$b->setReason($ban->reason);
		return $b;
	}

	/**
	 * @description NOTICE, setStatus() from User after generating.
	 * @param DiscordActivity $discordActivity
	 * @return Activity
	 */
	static public function genModelActivity(DiscordActivity $discordActivity): Activity{
		$a = new Activity();
		$a->setType($discordActivity->type);
		$a->setMessage($discordActivity->state);
		$a->setStatus(Activity::STATUS_OFFLINE); //Not included in discord activity must be set from user.
		return $a;
	}
}