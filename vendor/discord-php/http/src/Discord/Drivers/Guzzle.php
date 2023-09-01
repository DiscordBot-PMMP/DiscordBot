<?php

/*
 * This file is a part of the DiscordPHP-Http project.
 *
 * Copyright (c) 2021-present David Cole <david.cole1340@gmail.com>
 *
 * This file is subject to the MIT license that is bundled
 * with this source code in the LICENSE file.
 */

namespace Discord\Http\Drivers;

use Discord\Http\DriverInterface;
use Discord\Http\Request;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use React\EventLoop\LoopInterface;
use React\Promise\Deferred;
use React\Promise\ExtendedPromiseInterface;

/**
 * guzzlehttp/guzzle driver for Discord HTTP client. (still with React Promise).
 *
 * @author SQKo
 */
class Guzzle implements DriverInterface
{
    /**
     * ReactPHP event loop.
     *
     * @var LoopInterface|null
     */
    protected $loop;

    /**
     * GuzzleHTTP/Guzzle client.
     *
     * @var Client
     */
    protected $client;

    /**
     * Constructs the Guzzle driver.
     *
     * @param LoopInterface|null $loop
     * @param array              $options
     */
    public function __construct(?LoopInterface $loop = null, array $options = [])
    {
        $this->loop = $loop;

        // Allow 400 and 500 HTTP requests to be resolved rather than rejected.
        $options['http_errors'] = false;
        $this->client = new Client($options);
    }

    public function runRequest(Request $request): ExtendedPromiseInterface
    {
        // Create a React promise
        $deferred = new Deferred();
        $reactPromise = $deferred->promise();

        $promise = $this->client->requestAsync($request->getMethod(), $request->getUrl(), [
                RequestOptions::HEADERS => $request->getHeaders(),
                RequestOptions::BODY => $request->getContent(),
            ])->then([$deferred, 'resolve'], [$deferred, 'reject']);

        if ($this->loop) {
            $this->loop->futureTick([$promise, 'wait']);
        } else {
            $promise->wait();
        }

        return $reactPromise;
    }
}
