# Changelog

## [3.0.3] - 2023-10-19

### Fixed

- Fix external client not closing socket correctly ([`029c621`](https://github.com/DiscordBot-PMMP/DiscordBot/commit/029c621b26fd919479e144784dca56efa980367f))

### Removed

- Removed hardcoded guild features list & assertions ([#111](https://github.com/DiscordBot-PMMP/DiscordBot/issues/111))

## [3.0.2] - 2023-09-04

### Changed

- Bump network version to `2`

### Fixed

- Fix Modal Text response conversion ([#104](https://github.com/DiscordBot-PMMP/DiscordBot/issues/104))
- Fix `TextInput` serialization ([#103](https://github.com/DiscordBot-PMMP/DiscordBot/issues/103))

## [3.0.1] - 2023-09-03

### Fixed

- Fix presence update event activity buttons ([#99](https://github.com/DiscordBot-PMMP/DiscordBot/issues/99))
- Fix channel update event causing chaos ([#98](https://github.com/DiscordBot-PMMP/DiscordBot/pull/98), [@Laith98Dev](https://github.com/Laith98Dev))

## [3.0.0] - 2023-09-01

_If you are upgrading: please see [`UPGRADING.md`](UPGRADING.md)_

### Changed

- _Breaking:_ Bump `team-reflex/discord-php` to `v10.x`
- Bump `react/promise` to `v2.10.0`

### Added

- Add PocketMine-MP `5.x` support
- Add PHP `8.1.x` support
- Add `composer/ca-bundle` version `^1.3`
- Add `pocketmine/binaryutils` version `0.2.4`

### Removed

- _Breaking:_ Drop PocketMine-MP `4.x` support.
- _Breaking:_ Drop PHP `8.0.x` support.

## [2.1.10] - 2023-05-28

### Changed

- Modify composer PHP version constraint to match `plugin.yml` ([`33e675d`](https://github.com/DiscordBot-PMMP/DiscordBot/commit/33e675d36bbd0e96ce65c3f517331aa3453918d5))

## [2.1.9] - 2023-05-21

### Fixed

- Fix invalid PHP version constrain, making [2.1.7](#217---2023-05-20) and [2.1.8](#218---2023-05-21) unusable ([`5080128`](https://github.com/DiscordBot-PMMP/DiscordBot/commit/5080128afc77c76d33ec1028f65130303c9136d1))

## [2.1.8] - 2023-05-21

_This release was pulled due to a bug causing it to not load, use v2.1.9_

### Fixed

- Fix invalid activity URL ([#84](https://github.com/DiscordBot-PMMP/DiscordBot/issues/84), [`e6390ba`](https://github.com/DiscordBot-PMMP/DiscordBot/commit/e6390baeef14b064f8e42faf993b5df7cea02f36))
- Fix unknown message type on delete ([#85](https://github.com/DiscordBot-PMMP/DiscordBot/issues/85), [`e5b7892`](https://github.com/DiscordBot-PMMP/DiscordBot/commit/e5b78923d3aef46548466a0615f5ca039ba22f54))

## [2.1.7] - 2023-05-20

_This release was pulled due to a bug causing it to not load, use v2.1.9_

### Changed

- Modify PHP upper version constraint ([`d535038`](https://github.com/DiscordBot-PMMP/DiscordBot/commit/d5350386ee8694b60fa901971d8c6c52a44cb1e5))

## [2.1.6] - 2022-12-23

### Fixed

- Fix empty attachment assertions ([#78](https://github.com/DiscordBot-PMMP/DiscordBot/issues/78), [`894d272`](https://github.com/DiscordBot-PMMP/DiscordBot/commit/894d272008c5d934f285fdacc5f6a3a8ed4160ec))
- Fix unknown message type on delete ([#79](https://github.com/DiscordBot-PMMP/DiscordBot/issues/79), [`9064092`](https://github.com/DiscordBot-PMMP/DiscordBot/commit/906409206c3846ad51b55993efaf6aa80f5738ac))

## [2.1.5] - 2022-08-28

### Changed

- Modify startup behaviour, `xdebug` extension is now allowed ([`57218df`](https://github.com/DiscordBot-PMMP/DiscordBot/commit/57218dfccc98e2757356e808db4cede023b273e5))

## [2.1.4] - 2022-07-22

### Changed

- Modify `debugdiscord` command to be async ([#63](https://github.com/DiscordBot-PMMP/DiscordBot/issues/63), [`a67b8ce`](https://github.com/DiscordBot-PMMP/DiscordBot/commit/a67b8ceab7f9109ad265cf454aae6c486e2e42e1))

### Fixed

- Fix new discord snowflakes crashing plugin ([`5992231`](https://github.com/DiscordBot-PMMP/DiscordBot/commit/5992231c003f59dda518e939a5275278229b8875))

## [2.1.3] - 2022-05-08

### Fixed

- Fix owners/admins joining voice channel ([#59](https://github.com/DiscordBot-PMMP/DiscordBot/issues/59), [`19c2905`](https://github.com/DiscordBot-PMMP/DiscordBot/commit/19c2905b7040130d412deeafbe783d8c5c698a2b))
- Fix nullable message attachment content type ([#55](https://github.com/DiscordBot-PMMP/DiscordBot/issues/55), [`a8de8ca`](https://github.com/DiscordBot-PMMP/DiscordBot/commit/a8de8ca7a898c4ed1fe3e0edb6f2381c58e8d82c))

## [2.1.2] - 2022-04-06

_This release removes the need for our custom promise library._

### Changed

- Bump `react/promise` to `v2.9.0` ([`a7ab550`](https://github.com/DiscordBot-PMMP/DiscordBot/commit/a7ab55072a6f3ee1a6d99f5dcfe7c47c554a5ba5))

### Removed

- Drop `discordbot-pmmp/promise` ([`a7ab550`](https://github.com/DiscordBot-PMMP/DiscordBot/commit/a7ab55072a6f3ee1a6d99f5dcfe7c47c554a5ba5))

### Fixed

- Fix invites created with no owner ([#52](https://github.com/DiscordBot-PMMP/DiscordBot/issues/52))

## [2.1.1] - 2021-12-12

### Changed

- Modify Activity `url` checks to be less strict ([#51](https://github.com/DiscordBot-PMMP/DiscordBot/issues/51), [`ca8d43e`](https://github.com/DiscordBot-PMMP/DiscordBot/commit/ca8d43ef6a5f32a83c0733982390573cdb0e8c1f))

### Added

- Add `MembersVoiceChannel` to the data dump ([`b7ff955`](https://github.com/DiscordBot-PMMP/DiscordBot/commit/b7ff9555c1f2ed9a352b8ff8090831a6bd269a37))

### Fixed

- Fix blank channel ID in `VoiceStateUpdate` ([#49](https://github.com/DiscordBot-PMMP/DiscordBot/issues/49))
- Fix referenced message assertion in `ModelConverter` ([#50](https://github.com/DiscordBot-PMMP/DiscordBot/issues/50))

## [2.1.0] - 2021-12-02

### Changed

- Deprecate `Storage::getServerBans` in favour of `Storage::getBansByServer` ([`a966944`](https://github.com/DiscordBot-PMMP/DiscordBot/commit/a9669441b4234bce6a4ca3ab7a3e75b3e7468c3e))
- Modify thread logger to not write to console.
- Modify plugin structure to use PSR-4 ([`d7c6ebe`](https://github.com/DiscordBot-PMMP/DiscordBot/commit/d7c6ebe2641c03048ac07a320649ba330ac72317))

### Added

- Add `servers` and `users` getters in `Storage` ([`a966944`](https://github.com/DiscordBot-PMMP/DiscordBot/commit/a9669441b4234bce6a4ca3ab7a3e75b3e7468c3e))
- Add PocketMine-MP 4.x support ([`1467c49`](https://github.com/DiscordBot-PMMP/DiscordBot/commit/1467c493d8ba67b830b783dd2334ffb79e6d0c87))
- Add PHP ^8.0.3 support ([`797f2d0`](https://github.com/DiscordBot-PMMP/DiscordBot/commit/797f2d0015881398a70644ff304168ed62bf94de))

### Removed

- Remove `logging.debug` option from config ([`608a5ae`](https://github.com/DiscordBot-PMMP/DiscordBot/commit/608a5ae64a0780044b8f441db980955915c58282))
- Drop PocketMine-MP 3.x support ([`1467c49`](https://github.com/DiscordBot-PMMP/DiscordBot/commit/1467c493d8ba67b830b783dd2334ffb79e6d0c87))
- Drop PHP 7.4 support ([`797f2d0`](https://github.com/DiscordBot-PMMP/DiscordBot/commit/797f2d0015881398a70644ff304168ed62bf94de))

## [2.0.4] - 2021-10-02

### Fixed

- Fix logger always displaying debug messages ([#41](https://github.com/DiscordBot-PMMP/DiscordBot/issues/41))

## [2.0.3] - 2021-09-04

### Changed

- Changed disable procedure to prevent errors on shutdown.

### Added

- Add more status checks during data dump to reduce hangs on quit.

## [2.0.2] - 2021-09-03

### Changed

- Bump `team-reflex/discord-php` to `v6.0.2`

### Added

- Add check for `vendor` conflicts with PocketMine-MP ([`808da34`](https://github.com/DiscordBot-PMMP/DiscordBot/commit/808da3407da3e771cf2d8ee2ea724bcfa5d99726))

### Removed

- Remove logger injection into PocketMines ([`88937c0`](https://github.com/DiscordBot-PMMP/DiscordBot/commit/88937c03ba007a6ca8c2cf29e0f206c9b7267ed3)) 
- Remove `reloadConfig` & `saveConfig` from `PluginBase` inheritance ([`85ad7e1`](https://github.com/DiscordBot-PMMP/DiscordBot/commit/85ad7e1c8564ad1a230ef0a088fd46df8da07216))

### Fixed

- Fix `opcache extension not found` error on GitHub Actions ([`ff2ebe8`](https://github.com/DiscordBot-PMMP/DiscordBot/commit/ff2ebe8a76300cf9708a72742e51c6adb2525643))

## [2.0.1] - 2021-08-26

### Changed

- Adjusted embed description limit from `2048` to `4096` characters ([`4abc639`](https://github.com/DiscordBot-PMMP/DiscordBot/commit/4abc639984f3cca4f89ce020ffd2228245cb24c4))
- Adjusted message content limit from `2000` to `4000` characters ([`e9df705`](https://github.com/DiscordBot-PMMP/DiscordBot/commit/e9df705d460f50bdf6507e8211623a2e119a1c33))

### Fixed

- Corrected composer type from `plugin` to `library` ([`def387e`](https://github.com/DiscordBot-PMMP/DiscordBot/commit/def387ebed769b6400dd773600fcc0efaa592fd8))

## [2.0.0] - 2021-07-28

_**Breaking:** Plugin re-released as a central API._

## [1.0.0] - 2020-12-01

🌱 _Initial Release._

## [1.0.0_A3] - 2020-11-28

❌ _This release was never published to public._

## [1.0.0_A2] - 2020-11-22

❌ _This release was never published to public._

## [1.0.0_A1] - 2020-11-19

❌ _This release was never published to public._

[3.0.3]: https://github.com/DiscordBot-PMMP/DiscordBot/releases/tag/3.0.3
[3.0.2]: https://github.com/DiscordBot-PMMP/DiscordBot/releases/tag/3.0.2
[3.0.1]: https://github.com/DiscordBot-PMMP/DiscordBot/releases/tag/3.0.1
[3.0.0]: https://github.com/DiscordBot-PMMP/DiscordBot/releases/tag/3.0.0
[2.1.10]: https://github.com/DiscordBot-PMMP/DiscordBot/releases/tag/2.1.10
[2.1.9]: https://github.com/DiscordBot-PMMP/DiscordBot/releases/tag/2.1.9
[2.1.8]: https://github.com/DiscordBot-PMMP/DiscordBot/releases/tag/2.1.8
[2.1.7]: https://github.com/DiscordBot-PMMP/DiscordBot/releases/tag/2.1.7
[2.1.6]: https://github.com/DiscordBot-PMMP/DiscordBot/releases/tag/2.1.6
[2.1.5]: https://github.com/DiscordBot-PMMP/DiscordBot/releases/tag/2.1.5
[2.1.4]: https://github.com/DiscordBot-PMMP/DiscordBot/releases/tag/2.1.4
[2.1.3]: https://github.com/DiscordBot-PMMP/DiscordBot/releases/tag/2.1.3
[2.1.2]: https://github.com/DiscordBot-PMMP/DiscordBot/releases/tag/2.1.2
[2.1.1]: https://github.com/DiscordBot-PMMP/DiscordBot/releases/tag/2.1.1
[2.1.0]: https://github.com/DiscordBot-PMMP/DiscordBot/releases/tag/2.1.0
[2.0.4]: https://github.com/DiscordBot-PMMP/DiscordBot/releases/tag/2.0.4
[2.0.3]: https://github.com/DiscordBot-PMMP/DiscordBot/releases/tag/2.0.3
[2.0.2]: https://github.com/DiscordBot-PMMP/DiscordBot/releases/tag/2.0.2
[2.0.1]: https://github.com/DiscordBot-PMMP/DiscordBot/releases/tag/2.0.1
[2.0.0]: https://github.com/DiscordBot-PMMP/DiscordBot/releases/tag/2.0.0
[1.0.0]: https://github.com/DiscordBot-PMMP/DiscordBot/releases/tag/1.0.0
[1.0.0_A3]: https://github.com/DiscordBot-PMMP/DiscordBot/releases/tag/1.0.0_A3
[1.0.0_A2]: https://github.com/DiscordBot-PMMP/DiscordBot/releases/tag/1.0.0_A2
[1.0.0_A1]: https://github.com/DiscordBot-PMMP/DiscordBot/releases/tag/1.0.0_A1
