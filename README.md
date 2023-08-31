# DiscordBot
DiscordBot is not a plugin that provides anything for players/users, this plugin provides ***Developers***
with an extensive API to interact with a Discord Bot via DiscordPHP

Here are a list of plugins that provide user functionality:
- [Chat Bridge](https://github.com/DiscordBot-PMMP/ChatBridge) | Bi-directional chat between Discord and Minecraft
- [Discord Account](https://github.com/DiscordBot-PMMP/DiscordAccount) | Link your Discord account to your Minecraft account
- *More to be listed (If you use this plugin open a PR to add your plugin here)*

> Developers, see [API.md](API.md) for more information on how to use the DiscordBot API \
> Advanced Developers, see [Network_API.md](Network_API.md) for more information on the network protocol / external bots.
# Requirements

---
| Name          | Version    | Included in releases |
|---------------|------------|:--------------------:|
| PHP           | ^ 8.1      |          ❌           |
| PocketMine-MP | ^ 5.0      |          ❌           |
| DiscordPHP    | 10.0.0-RC6 |          ✅           |
| Promise       | 2.10       |          ✅           |
# Installation

---
### PocketMine-MP
All Releases v2.0.0 onwards have been tested and released on Poggit, you can download the latest release from
[Poggit](https://poggit.pmmp.io/p/DiscordBot) or from
[GitHub](https://github.com/DiscordBot-PMMP/DiscordBot/releases/latest)

> *GitHub release being slightly more optimised, useless files are not included.*

### Composer
This plugin is also available via composer to use as a dev-dependency when using PHPStan, PHPUnit, or similar.

You can install it simply with `composer require --dev jaxkdev/discordbot`

To see more information about the project via composer/packagist please see [here](https://packagist.org/packages/jaxkdev/discordbot).

>Please note the package name is `jaxkdev/discordbot` and not `discordbot-pmmp/discordbot`

### Source
No support is given for users or developers running from source,
I myself build the plugin into a phar before testing.

If you do become an active contributor to the plugin I will help you set up efficient workflows to ease testing.

# Credits

---
### Contributors
- [@JaxkDev](https://github.com/JaxkDev) - Lead developer
- [@dktapps](https://github.com/dktapps) - ext-pmmpthread support

### Libraries
- [Discord-PHP/DiscordPHP](https://github.com/DiscordPHP/DiscordPHP) - Used internally to communicate with discord.
- [ReactPHP/Promise](https://github.com/reactphp/promise) - Used to provide a promise API Library to developers.
- [[Dev] PHPStan/PHPStan](https://github.com/phpstan/phpstan) - Used to analyse the plugins code for any potential problems.
- [[Dev] PHP-CS-Fixer/PHP-CS-Fixer](https://github.com/PHP-CS-Fixer/PHP-CS-Fixer) - Used to maintain the plugins code to a standard.

- [[Dev] ![Common Changelog](https://common-changelog.org/badge.svg)](https://common-changelog.org) - Used to maintain a changelog format.

And many more sub-dependencies that allow the above to function.
