<?php

namespace Tests\Discord\Http;

use Discord\Http\Endpoint;
use Discord\Http\Http;
use Discord\Http\Request;
use Mockery;
use PHPUnit\Framework\TestCase;
use React\Promise\Deferred;

class RequestTest extends TestCase
{
    private function getRequest(
        ?Deferred $deferred = null,
        string $method = '',
        ?Endpoint $url = null,
        string $content = '',
        array $headers = []
    ) {
        $url = $url ?? new Endpoint('');
        $deferred = $deferred ?? new Deferred();

        return new Request(
            $deferred,
            $method,
            $url,
            $content,
            $headers
        );
    }

    public function testGetDeferred()
    {
        $deferred = Mockery::mock(Deferred::class);
        $request = $this->getRequest($deferred);

        $this->assertEquals($deferred, $request->getDeferred());
    }

    public function testGetMethod()
    {
        $request = $this->getRequest(null, '::method::');

        $this->assertEquals('::method::', $request->getMethod());
    }

    public function testGetUrl()
    {
        $request = $this->getRequest(null, '', new Endpoint('::url::'));

        $this->assertEquals(Http::BASE_URL . '/::url::', $request->getUrl());
    }

    public function testGetContent()
    {
        $request = $this->getRequest(null, '', null, '::content::');

        $this->assertEquals('::content::', $request->getContent());
    }

    public function testGetHeaders()
    {
        $request = $this->getRequest(null, '', null, '::content::', ['something' => 'value']);

        $this->assertEquals(['something' => 'value'], $request->getHeaders());
    }

    public function testGetBucketId()
    {
        $endpoint = Mockery::mock(Endpoint::class);
        $endpoint->shouldReceive('toAbsoluteEndpoint')->andReturn('::endpoint::');

        $request = $this->getRequest(null, '::method::', $endpoint);

        $this->assertEquals('::method::::endpoint::', $request->getBucketID());
    }
}
