<?php

use Discord\Http\Drivers\Guzzle;
use Discord\Http\Endpoint;
use Discord\Http\Http;
use Discord\Http\Multipart\MultipartBody;
use Discord\Http\Multipart\MultipartField;
use Psr\Log\NullLogger;
use React\EventLoop\Loop;

require './vendor/autoload.php';

$http = new Http(
    'Your token',
    Loop::get(),
    new NullLogger(),
    new Guzzle(
        Loop::get()
    )
);

$jsonPayloadField = new MultipartField(
    'json_payload',
    json_encode([
        'content' => 'Hello!',
    ]),
    ['Content-Type' => 'application/json']
);

$imageField = new MultipartField(
    'files[0]',
    file_get_contents('/path/to/image.png'),
    ['Content-Type' => 'image/png'],
    'image.png'
);

$multipart = new MultipartBody([
    $jsonPayloadField,
    $imageField
]);

$http->post(
    Endpoint::bind(
        Endpoint::CHANNEL_MESSAGES,
        'Channel ID'
    ),
    $multipart
)->then(
    function ($response) {
        // Do something with response..
    },
    function (Exception $e) {
        echo $e->getMessage(), PHP_EOL;
    }
);

Loop::run();
