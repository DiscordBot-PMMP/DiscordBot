<?php

namespace Tests\Discord\Http;

use Discord\Http\DriverInterface;
use Discord\Http\Request;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use function React\Async\await;

abstract class DriverInterfaceTest extends TestCase
{
    abstract protected function getDriver(): DriverInterface;

    private function getRequest(
        string $method,
        string $url,
        string $content = '',
        array $headers = []
    ): Request {
        $request = Mockery::mock(Request::class);

        $request->shouldReceive([
            'getMethod' => $method,
            'getUrl' => $url,
            'getContent' => $content,
            'getHeaders' => $headers,
        ]);

        return $request;
    }

    /**
     * @dataProvider requestProvider
     */
    public function testRequest(string $method, string $url, array $content = [], array $verify = [])
    {
        $driver = $this->getDriver();
        $request = $this->getRequest(
            $method,
            $url,
            $content === [] ? '' : json_encode($content),
            empty($content) ? [] : ['Content-Type' => 'application/json']
        );

        /** @var ResponseInterface */
        $response = await($driver->runRequest($request));

        $this->assertNotEquals('', $response->getBody());
        $this->assertEquals(200, $response->getStatusCode());

        $jsonDecodedBody = json_decode($response->getBody(), true);

        $verify['method'] = $method;

        foreach ($verify as $field => $expectedValue) {
            $this->assertEquals(
                $expectedValue,
                $jsonDecodedBody[$field]
            );
        }
    }

    public function requestProvider(): array
    {
        $content = ['something' => 'value'];
        return [
            'Plain get' => [
                'method' => 'GET',
                'url' => 'http://127.0.0.1:8888',
            ],
            'Get with params' => [
                'method' => 'GET',
                'url' => 'http://127.0.0.1:8888?something=value',
                'verify' => [
                    'args' => $content,
                ],
            ],

            'Plain post' => [
                'method' => 'POST',
                'url' => 'http://127.0.0.1:8888',
            ],
            'Post with content' => [
                'method' => 'POST',
                'url' => 'http://127.0.0.1:8888',
                'content' => $content,
                'verify' => [
                    'json' => $content,
                ],
            ],

            'Plain put' => [
                'method' => 'PUT',
                'url' => 'http://127.0.0.1:8888',
            ],
            'Put with content' => [
                'method' => 'PUT',
                'url' => 'http://127.0.0.1:8888',
                'content' => $content,
                'verify' => [
                    'json' => $content,
                ],
            ],

            'Plain patch' => [
                'method' => 'PATCH',
                'url' => 'http://127.0.0.1:8888',
            ],
            'Patch with content' => [
                'method' => 'PATCH',
                'url' => 'http://127.0.0.1:8888',
                'content' => $content,
                'verify' => [
                    'json' => $content,
                ],
            ],

            'Plain delete' => [
                'method' => 'DELETE',
                'url' => 'http://127.0.0.1:8888',
            ],
            'Delete with content' => [
                'method' => 'DELETE',
                'url' => 'http://127.0.0.1:8888',
                'content' => $content,
                'verify' => [
                    'json' => $content,
                ],
            ],
        ];
    }
}
