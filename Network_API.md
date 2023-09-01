# Network API Documentation

<hr />

## Socket Format

Protocol: `TCP(Stream) - IPV4`

The plugin DiscordBot acts as a client,\
The server is the program hosting the bot and providing a socket to connect to.

Custom base data types are defined as follows:

```
Bool: [8-bit int, 1=true, 0=false]

String: [32-bit int-BE, length] [raw string of length bytes]

Array: [32-bit int-BE, count] [serialized raw data (deserialize with expected data type count times)]

Nullable: [Bool][not null if true (data present after), null if false (no data after)]
```

See below for some base type examples
```
string[] array = ["Hello", "Hi", "Hey"]
binaryArray = 32bitBE(3) + ((32bitBE(5) + "Hello") + (32bitBE(2) + "Hi") + (32bitBE(3) + "Hey"))
            //Size of array + ((size of string + string) repeated for size of array)

int[] array = [1,2,3]
binaryArray = 32bitBE(3) + (32bitBE(1) + 32bitBE(2) + 32bitBE(3))
            //Size of array + (int, repeated for size of array)

null|string data = null
binaryData = 8bit(0)
            //1 byte, 0 = null, 1 = value present.

null|string data = "Hello"
binaryData = 8bit(1) + (32bitBE(5) + "Hello")
            //(Nullable 1 byte, 0 = null, 1 = value present. + 32bitBE(5) + "Hello"

null|int data = null
binaryData = 8bit(0)
            //1 byte, 0 = null, 1 = value present.

null|int data = 5
binaryData = 8bit(1) + 32bitBE(5)
            //(Nullable 1 byte, 0 = null, 1 = value present. + 32bitBE(5)
```

For more information see `src/Communication/BinaryStream.php` [here](src/Communication/BinaryStream.php)

<hr />

## Network Protocol

### Writing/Reading Packets
```
[32-bit int-BE, packet size] [raw packet of length packet size]
```

### Packet Format
```
[16-bit int-BE, packet id] [raw packet data, see individual packet for format]
```

### Order of connect packets
The order in packets sent before main loop.
1. [Connect packet](src/Communication/Packets/External/Connect.php) (Plugin -> Server)
2. [Connect packet](src/Communication/Packets/External/Connect.php) (Server -> Plugin) (This should not ping back the packet we sent, but the servers response with its own values)


3. [Heartbeat packet](src/Communication/Packets/Heartbeat.php) - (Plugin -> Server, Server -> Plugin) Heartbeat every second for the duration of the connection now.


4. [DiscordReady packet](src/Communication/Packets/Discord/DiscordConnected.php) (Server -> Plugin) (This is sent after the bot is ready, and the server/plugin should not send any packets before this is received, excluding heartbeat)


5. Connection finished, now the server can send any packets it wants to the plugin, and the plugin can send any packets it wants to the server.

At any point either the server or plugin can send a [Disconnect packet](src/Communication/Packets/External/Disconnect.php) to close the connection.
