# DiscordBot API

Would love to have this as a GitHub site, However I don't have the time.

**Version 3.x documentation.**

---

## API Structure
Firstly a quick few notes about the API and what it covers.

+ These namespaces are **INTERNAL ONLY** and not to be used:
    + `JaxkDev\DiscordBot\Bot`
    + `JaxkDev\DiscordBot\Communication`
    + `JaxkDev\DiscordBot\ExternalBot`
    + `JaxkDev\DiscordBot\InternalBot`
    + vendor libs, (Not actually loaded in the main thread)


 + All API Calls can be made through `JaxkDev\DiscordBot\Plugin\Api.php` an instance is available from the plugin instance
(`JaxkDev\DiscordBot\Plugin\Main.php` - `getApi()`)


+ All discord data types such as Member, Role, Channel etc
That is all located in the namespace `JaxkDev\DiscordBot\Models` all type info and required data can be found
in the relevant file.


+ All the actual discord data such as guilds, roles, members etc. can be obtained via `Api->fetchXYZ()` methods after 
the API is ready for requests (`Api->isReady()`).

    + Listen to `JaxkDev\DiscordBot\Plugin\Events\DiscordReady.php` this is emitted when bot is connected
, only use the API after this event.

---

## API Promises

When you make an API call to do anything discord side, for example send a message to a channel.
It will return something called a `Promise`, And this promise will allow you to know the end result, in this case did
the message send successfully, or did it fail to send?

The reason DiscordBot uses promises is that it cannot return the result 'instantly', as the plugin has to send it to
another thread (discord bot) and then receive the result which is naturally about 3-10 ticks later.

So using a promise we can return an interface that allows you the person sending the request to attach a callback for
when the request is `resolved` or `rejected`.

if its `resolved` it means the request was handled successfully, in this scenario it means message was sent successfully
and a `ApiResolution` will be passed back.

if its `rejected` it means something happened, and it failed to finish the request successfully, in this scenario it means
the message did not get sent, and a `ApiRejection` (exception) will be passed back.

#### API Promise example:

```php
/** @var $api \JaxkDev\DiscordBot\Plugin\Api */
$api = $DiscordBotPluginInstance->getApi();
/** @var $promise \JaxkDev\DiscordBot\Libs\React\Promise\PromiseInterface */
$promise = $api->sendMessage("guild_id or null for DMs", "channel_id or user_id for DMs", "Hello world !"));

// You could do other things here if necessary
// but be sure to register your callbacks before finishing.

//To handle both resolved and rejected:
$promise->then(function(\JaxkDev\DiscordBot\Plugin\ApiResolution $resolution){
    //Yay, it worked and the message was sent successfully.
    echo "Resolved !";
    var_dump($resolution->getData()[0]); // will dump the Message model in array index 0.
}, function(\JaxkDev\DiscordBot\Plugin\ApiRejection $rejectedError){
    echo "Rejected :(";
    //Oh no, It failed and $rejectedError can tell you why.
});

//Or handle just resolved:
$promise->then(function(\JaxkDev\DiscordBot\Plugin\ApiResolution $resolution){
    echo "Resolved !";
    //Yay, it worked and the message was sent successfully.
});

//Or handle just rejected:
$promise->otherwise(function(\JaxkDev\DiscordBot\Plugin\ApiRejection $rejectedError){
    echo "Rejected :(";
    //Oh no, It failed and $rejectedError can tell you why.
});
//same as:
$promise->then(null, function(\JaxkDev\DiscordBot\Plugin\ApiRejection $rejectedError){
    echo "Rejected :(";
    //Oh no, It failed and $rejectedError can tell you why.
});

echo "Finished";
```

Please note the order of execution:
1. "Finished"
2. *Server/Plugin continues as normal, for at least 2/3 ticks*
3. "Resolved !" or "Rejected :(" - **Never both**

For more in depth details about the promise interface see `JaxkDev\DiscordBot\Libs\React\Promise\PromiseInterface.php`

For information on the data thats returned with `$resolution->getData()` see the API methods phpdoc.

---

## Discord API - weird things

Discord does do some fancy weird things, so I've listed a few things that are important to note below.

`Discord Gateway v10` The current version DiscordBot-InternalBot/DiscordPHP uses.

+ DM Channels
    + Each DM has its own channel however because discord makes ID's for those channels irrelevant of any user IDs so dphp sends a channel create event before any update to a DM such
      as sending a message, pinning etc. we cannot reliably store a DM Channel because of its unique ID's.

+ Voice Channels
    + Due to binaries required and resources this plugin does not support any interaction with voice channels (joining/playing sounds/leaving)
