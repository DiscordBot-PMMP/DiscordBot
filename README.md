# DiscordBot
DiscordBot a replacement of MCPEToDiscord, Host your very own discord bot on your PocketMine server.

# Installation:
#### Releases
All releases that are published are ready to run out of the box, simply add the DiscordBot_vX_Y_Z.phar to your plugins directory.

*Note you must start the server with the plugin before you can edit the config files.*


#### Manually
The plugin can work with DevTools in folder structure however it is suggested to build before using it, see blow for instructions on building the plugin:
1. Run `composer install -o --no-dev` in the plugin directory to get all dependencies required.
2. Run `php BUILD.php`
3. Copy `DiscordBot.phar` to your plugins directory.