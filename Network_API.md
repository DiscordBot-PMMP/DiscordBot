# Network API Documentation

<hr />

## Socket Format

Protocol: `TCP(Stream) - IPV4`

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

<hr />

## Network Protocol

Before any data is sent, the client once connected to the socket
must send a [Connect packet](TODO) to the server.

If the `version` of the client is not compatible with the server, the server will send a [Disconnect packet](TODO) to the client and close the connection.

If the `magic` is not correct, the server will send a [Disconnect packet](TODO) to the client and close the connection.