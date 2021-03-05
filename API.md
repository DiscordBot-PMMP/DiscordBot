# DiscordBot API

#### Version 2.0.x documentation.

---

## API Structure
Firstly a quick few notes about the API and what it covers.

+ These namespaces are **INTERNAL ONLY**:
    + `JaxkDev\DiscordBot\Bot`
    + `JaxkDev\DiscordBot\Communication`
    + vendor libs, (Not actually available in the main thread)


 + All API Calls can be made through `JaxkDev\DiscordBot\Plugin\Api.php` an instance is available from the plugin instance
(`JaxkDev\DiscordBot\Plugin\Main.php`)


+ All discord data types such as Member, Role, Channel etc
That is all located in the namespace `JaxkDev\DiscordBot\Models` all type info and required data can be found
in the relevant file.


+ All the actual discord data such as servers, roles members can be found in plugin storage anywhere from 
5seconds to 500seconds after the plugin enables.

    + To see if the initial data has been received you can check the `timestamp` through `JaxkDev\DiscordBot\Plugin\Storage.php`
if it is null, no data has been received yet, otherwise it will have the timestamp of when that initial data dump came in.
    + An alternative is to listen to `JaxkDev\DiscordBot\Plugin\Events\DiscordReady.php` this is emitted when bot is connected
**and** initial data has been received.

---

## API Promises

When you make an API call to do anything discord side, for example send a message to a channel.
It will return something called a `Promise`, And this promise will allow you to know the end result, in this case did
the message send successfully, or did it fail to send?

The reason DiscordBot uses promises is that it cannot return the result 'instantly', as the plugin has to send it to
another thread (discord bot) and then receive the result which is naturally about 3-10 ticks later.

So using a promise we can return an interface that allows you the person sending the request to attach a callback for
when the request is `resolved` or `rejected`.

if its `resolved` it means the request was handled successfully, in this scenario it means message was sent successfully.

if its `rejected` it means something happened, and it failed to finish the request successfully, in this scenario it means
the message did not get sent, and a Throwable exception will be passed back.

#### API Promise example:

```php
/** @var $api \JaxkDev\DiscordBot\Plugin\Api */
$api = $DiscordBotPlugin->getApi();
/** @var $promise \JaxkDev\DiscordBot\Libs\React\Promise\PromiseInterface */
$promise = $api->sendMessage(/*Message model from $api->createMessage()*/);

// You could do other things here if necessary
// but be sure to register your callbacks before finishing.

//To handle both resolved and rejected:
$promise->then(function($resolvedData){
    echo "Resolved !";
    //Yay, it worked and the message was sent successfully.
    //See API Documentation for potential resolvedData.
}, function(\Throwable $rejectedError){
    echo "Rejected :(";
    //Oh no, It failed and $rejectedError can tell you why.
});

//Or handle just resolved:
$promise->then(function($resolvedData){
    echo "Resolved !";
    //Yay, it worked and the message was sent successfully.
    //See API Documentation for potential resolvedData.
});

//Or handle just rejected:
$promise->otherwise(function(\Throwable $rejectedError){
    echo "Rejected :(";
    //Oh no, It failed and $rejectedError can tell you why.
});
//same as:
$promise->then(null, function(\Throwable $rejectedError){
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

For information on the argument types for `$resolvedData` and `$rejectedError` see below.

---

## API documentation

This section documents every API method, and the possible input, return/resolution, and behaviour.

`JaxkDev\DiscordBot\Plugin\Api.php`

#### createMessage
+ **Signature** - `createMessage(Channel|string $channel, string $content): ?Message`
+ **Input** - A Channel model,channel ID or user ID(DM Message) along with the content to send (max 2000 characters).
+ **Output** - A Message model or null if channel/user cannot be found by ID.


#### sendMessage
+ **Signature** - `sendMessage(Message $message): PromiseInterface`
+ **Input** - Message model, see `createMessage`.
+ **Output** - PromiseInterface
    + **ResolvedData** - Model message with updated values like ID, timestamp etc.
    + **RejectedError** - Throwable error (Exception).


TODO More.

---

## Discord api - weird things

Discord does do some fancy weird things a lot like minecraft, so I've listed a few things that are important to note.

`discord gateway/http api v6` The default and current version DiscordBot/DiscordPHP uses.

+ DM Channels
    + Each DM has its own channel however because discord sends a channel create event before any update to a DM such
      as sending a message, pinning etc. we cannot reliably store a DM Channel.


+ Messages
    + Embeds
        + Messages containing embeds send a message update event straight after a create event, even if it has not been updated.
    + Replies
        + I have not looked into this at all (TODO), however I am aware the API to reply to a message is still using the old format. (new format in discord api v8 iirc)
        + New replies may not be handled as a message or not being picked up at all (TODO Check).


+ Server Channels
    + Each server channel has its own pinned messages however discord does not say what message has been pinned/unpinned/deleted so we cannot reliably track/store pins yet.
        + Note, However you can still attempt to pin/unpin a message.


+ Voice Channels
    + Due to largely sized binaries required for different operating systems the bot cannot connect/play music in a VC.
