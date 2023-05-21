<?php

/*
 * This file is a part of the DiscordPHP-Http project.
 *
 * Copyright (c) 2021-present David Cole <david.cole1340@gmail.com>
 *
 * This file is subject to the MIT license that is bundled
 * with this source code in the LICENSE file.
 */

namespace Discord\Http;

use Discord\Http\Exceptions\ContentTooLongException;
use Discord\Http\Exceptions\InvalidTokenException;
use Discord\Http\Exceptions\NoPermissionsException;
use Discord\Http\Exceptions\NotFoundException;
use Discord\Http\Exceptions\RequestFailedException;
use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use React\EventLoop\LoopInterface;
use React\Promise\Deferred;
use React\Promise\ExtendedPromiseInterface;
use SplQueue;
use Throwable;

/**
 * Discord HTTP client.
 *
 * @author David Cole <david.cole1340@gmail.com>
 */
class Http
{
    /**
     * DiscordPHP-Http version.
     *
     * @var string
     */
    public const VERSION = 'v8.1.2';

    /**
     * Current Discord HTTP API version.
     *
     * @var string
     */
    public const HTTP_API_VERSION = 8;

    /**
     * Discord API base URL.
     *
     * @var string
     */
    public const BASE_URL = 'https://discord.com/api/v'.self::HTTP_API_VERSION;

    /**
     * The number of concurrent requests which can
     * be executed.
     *
     * @var int
     */
    public const CONCURRENT_REQUESTS = 5;

    /**
     * Authentication token.
     *
     * @var string
     */
    private $token;

    /**
     * Logger for HTTP requests.
     *
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * HTTP driver.
     *
     * @var DriverInterface
     */
    protected $driver;

    /**
     * ReactPHP event loop.
     *
     * @var LoopInterface
     */
    protected $loop;

    /**
     * Array of request buckets.
     *
     * @var Bucket[]
     */
    protected $buckets = [];

    /**
     * The current rate-limit.
     *
     * @var RateLimit
     */
    protected $rateLimit;

    /**
     * Timer that resets the current global rate-limit.
     *
     * @var TimerInterface
     */
    protected $rateLimitReset;

    /**
     * Request queue to prevent API
     * overload.
     *
     * @var SplQueue
     */
    protected $queue;

    /**
     * Number of requests that are waiting for a response.
     *
     * @var int
     */
    protected $waiting = 0;

    /**
     * Http wrapper constructor.
     *
     * @param string               $token
     * @param LoopInterface        $loop
     * @param DriverInterface|null $driver
     */
    public function __construct(string $token, LoopInterface $loop, LoggerInterface $logger, DriverInterface $driver = null)
    {
        $this->token = $token;
        $this->loop = $loop;
        $this->logger = $logger;
        $this->driver = $driver;
        $this->queue = new SplQueue;
    }

    /**
     * Sets the driver of the HTTP client.
     *
     * @param DriverInterface $driver
     */
    public function setDriver(DriverInterface $driver): void
    {
        $this->driver = $driver;
    }

    /**
     * Runs a GET request.
     *
     * @param string|Endpoint $url
     * @param mixed           $content
     * @param array           $headers
     *
     * @return ExtendedPromiseInterface
     */
    public function get($url, $content = null, array $headers = []): ExtendedPromiseInterface
    {
        if (! ($url instanceof Endpoint)) {
            $url = Endpoint::bind($url);
        }

        return $this->queueRequest('get', $url, $content, $headers);
    }

    /**
     * Runs a POST request.
     *
     * @param string|Endpoint $url
     * @param mixed           $content
     * @param array           $headers
     *
     * @return ExtendedPromiseInterface
     */
    public function post($url, $content = null, array $headers = []): ExtendedPromiseInterface
    {
        if (! ($url instanceof Endpoint)) {
            $url = Endpoint::bind($url);
        }

        return $this->queueRequest('post', $url, $content, $headers);
    }

    /**
     * Runs a PUT request.
     *
     * @param string|Endpoint $url
     * @param mixed           $content
     * @param array           $headers
     *
     * @return ExtendedPromiseInterface
     */
    public function put($url, $content = null, array $headers = []): ExtendedPromiseInterface
    {
        if (! ($url instanceof Endpoint)) {
            $url = Endpoint::bind($url);
        }

        return $this->queueRequest('put', $url, $content, $headers);
    }

    /**
     * Runs a PATCH request.
     *
     * @param string|Endpoint $url
     * @param mixed           $content
     * @param array           $headers
     *
     * @return ExtendedPromiseInterface
     */
    public function patch($url, $content = null, array $headers = []): ExtendedPromiseInterface
    {
        if (! ($url instanceof Endpoint)) {
            $url = Endpoint::bind($url);
        }

        return $this->queueRequest('patch', $url, $content, $headers);
    }

    /**
     * Runs a DELETE request.
     *
     * @param string|Endpoint $url
     * @param mixed           $content
     * @param array           $headers
     *
     * @return ExtendedPromiseInterface
     */
    public function delete($url, $content = null, array $headers = []): ExtendedPromiseInterface
    {
        if (! ($url instanceof Endpoint)) {
            $url = Endpoint::bind($url);
        }

        return $this->queueRequest('delete', $url, $content, $headers);
    }

    /**
     * Builds and queues a request.
     *
     * @param string   $method
     * @param Endpoint $url
     * @param mixed    $content
     * @param array    $headers
     *
     * @return ExtendedPromiseInterface
     */
    public function queueRequest(string $method, Endpoint $url, $content, array $headers = []): ExtendedPromiseInterface
    {
        $deferred = new Deferred();

        if (is_null($this->driver)) {
            $deferred->reject(new \Exception('HTTP driver is missing.'));

            return $deferred->promise();
        }

        $headers = array_merge($headers, [
            'User-Agent' => $this->getUserAgent(),
            'Authorization' => $this->token,
            'X-Ratelimit-Precision' => 'millisecond',
        ]);

        $baseHeaders = [
            'User-Agent' => $this->getUserAgent(),
            'Authorization' => $this->token,
            'X-Ratelimit-Precision' => 'millisecond',
        ];

        // If there is content and Content-Type is not set,
        // assume it is JSON.
        if (! is_null($content) && ! isset($headers['Content-Type'])) {
            $content = json_encode($content);

            $baseHeaders['Content-Type'] = 'application/json';
            $baseHeaders['Content-Length'] = strlen($content);
        }

        $headers = array_merge($baseHeaders, $headers);

        $request = new Request($deferred, $method, $url, $content ?? '', $headers);
        $this->sortIntoBucket($request);

        return $deferred->promise();
    }

    /**
     * Executes a request.
     *
     * @param Request  $request
     * @param Deferred $deferred
     *
     * @return ExtendedPromiseInterface
     */
    protected function executeRequest(Request $request, Deferred $deferred = null): ExtendedPromiseInterface
    {
        if ($deferred === null) {
            $deferred = new Deferred();
        }

        if ($this->rateLimit) {
            $deferred->reject($this->rateLimit);

            return $deferred->promise();
        }

        $this->driver->runRequest($request)->done(function (ResponseInterface $response) use ($request, $deferred) {
            $data = json_decode((string) $response->getBody());
            $statusCode = $response->getStatusCode();

            // Discord Rate-limit
            if ($statusCode == 429) {
                $rateLimit = new RateLimit($data->global, $data->retry_after);
                $this->logger->warning($request.' hit rate-limit: '.$rateLimit);

                if ($rateLimit->isGlobal() && ! $this->rateLimit) {
                    $this->rateLimit = $rateLimit;
                    $this->rateLimitReset = $this->loop->addTimer($rateLimit->getRetryAfter(), function () {
                        $this->rateLimit = null;
                        $this->rateLimitReset = null;
                        $this->logger->info('global rate-limit reset');

                        // Loop through all buckets and check for requests
                        foreach ($this->buckets as $bucket) {
                            $bucket->checkQueue();
                        }
                    });
                }

                $deferred->reject($rateLimit->isGlobal() ? $this->rateLimit : $rateLimit);
            }
            // Bad Gateway
            // Cloudflare SSL Handshake error
            // Push to the back of the bucket to be retried.
            elseif ($statusCode == 502 || $statusCode == 525) {
                $this->logger->warning($request.' 502/525 - retrying request');

                $this->executeRequest($request, $deferred);
            }
            // Any other unsuccessful status codes
            elseif ($statusCode < 200 || $statusCode >= 300) {
                $error = $this->handleError($response);
                $this->logger->warning($request.' failed: '.$error);

                $deferred->reject($error);
                $request->getDeferred()->reject($error);
            }
            // All is well
            else {
                $this->logger->debug($request.' successful');

                $deferred->resolve($response);
                $request->getDeferred()->resolve($data);
            }
        }, function (Exception $e) use ($request) {
            $this->logger->warning($request.' failed: '.$e->getMessage());

            $request->getDeferred()->reject($e);
        });

        return $deferred->promise();
    }

    /**
     * Sorts a request into a bucket.
     *
     * @param Request $request
     */
    protected function sortIntoBucket(Request $request): void
    {
        $bucket = $this->getBucket($request->getBucketID());
        $bucket->enqueue($request);
    }

    /**
     * Gets a bucket.
     *
     * @param string $key
     *
     * @return Bucket
     */
    protected function getBucket(string $key): Bucket
    {
        if (! isset($this->buckets[$key])) {
            $bucket = new Bucket($key, $this->loop, $this->logger, function (Request $request) {
                $deferred = new Deferred();
                $this->queue->enqueue([$request, $deferred]);
                $this->checkQueue();

                return $deferred->promise();
            });

            $this->buckets[$key] = $bucket;
        }

        return $this->buckets[$key];
    }

    /**
     * Checks the request queue to see if more requests can be
     * sent out.
     */
    protected function checkQueue(): void
    {
        if ($this->waiting >= static::CONCURRENT_REQUESTS || $this->queue->isEmpty()) {
            $this->logger->debug('http not checking', ['waiting' => $this->waiting, 'empty' => $this->queue->isEmpty()]);
            return;
        }

        /**
         * @var Request  $request
         * @var Deferred $deferred
         */
        [$request, $deferred] = $this->queue->dequeue();
        ++$this->waiting;

        $this->executeRequest($request)->then(function ($result) use ($deferred) {
            --$this->waiting;
            $this->checkQueue();
            $deferred->resolve($result);
        }, function ($e) use ($deferred) {
            --$this->waiting;
            $this->checkQueue();
            $deferred->reject($e);
        });
    }

    /**
     * Returns an exception based on the request.
     *
     * @param ResponseInterface $response
     *
     * @return Throwable
     */
    public function handleError(ResponseInterface $response): Throwable
    {
        $reason = $response->getReasonPhrase().' - ';

        // attempt to prettyify the response content
        if (($content = json_decode((string) $response->getBody())) !== null) {
            $reason .= json_encode($content, JSON_PRETTY_PRINT);
        } else {
            $reason .= (string) $response->getBody();
        }

        switch ($response->getStatusCode()) {
            case 401:
                return new InvalidTokenException($reason);
            case 403:
                return new NoPermissionsException($reason);
            case 404:
                return new NotFoundException($reason);
            case 500:
                if (strpos(strtolower((string) $response->getBody()), 'longer than 2000 characters') !== false ||
                    strpos(strtolower((string) $response->getBody()), 'string value is too long') !== false) {
                    // Response was longer than 2000 characters and was blocked by Discord.
                    return new ContentTooLongException('Response was more than 2000 characters. Use another method to get this data.');
                }
            default:
                return new RequestFailedException($reason);
        }
    }

    /**
     * Returns the User-Agent of the HTTP client.
     *
     * @return string
     */
    public function getUserAgent(): string
    {
        return 'DiscordBot (https://github.com/discord-php/DiscordPHP-HTTP, '.self::VERSION.')';
    }
}
