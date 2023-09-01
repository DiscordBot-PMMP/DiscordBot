<?php

namespace Tests\Discord\Http;

use Discord\Http\Endpoint;
use PHPUnit\Framework\TestCase;

class EndpointTest extends TestCase
{
    /**
     * @dataProvider majorParamProvider
     */
    public function testBindMajorParams(string $uri, array $replacements, string $expected)
    {
        $endpoint = new Endpoint($uri);
        $endpoint->bindArgs(...$replacements);

        $this->assertEquals(
            $endpoint->toAbsoluteEndpoint(true),
            $expected
        );
    }

    public function majorParamProvider(): array
    {
        return [
            'Several major params' => [
                'uri' => 'something/:guild_id/:channel_id/:webhook_id',
                'replacements' => ['::guild id::', '::channel id::', '::webhook id::'],
                'expected' => 'something/::guild id::/::channel id::/::webhook id::',
            ],
            'Single major param' => [
                'uri' => 'something/:guild_id',
                'replacements' => ['::guild id::'],
                'expected' => 'something/::guild id::',
            ],
            'Single major param, some minor params' => [
                'uri' => 'something/:guild_id/:some_param/:something_else',
                'replacements' => ['::guild id::', '::some_param::', '::something else::'],
                'expected' => 'something/::guild id::/:some_param/:something_else',
            ],
            'Only minor params' => [
                'uri' => 'something/:something/:some_param/:something_else',
                'replacements' => ['::something::', '::some_param::', '::something else::'],
                'expected' => 'something/:something/:some_param/:something_else',
            ],
            'Minor and major params in weird order' => [
                'uri' => 'something/:something/:guild_id/:something_else/:channel_id',
                'replacements' => ['::something::', '::guild id::', '::something else::', '::channel id::'],
                'expected' => 'something/:something/::guild id::/:something_else/::channel id::',
            ],
        ];
    }

    /**
     * @dataProvider allParamProvider
     */
    public function testBindAllParams(string $uri, array $replacements, string $expected)
    {
        $endpoint = new Endpoint($uri);
        $endpoint->bindArgs(...$replacements);

        $this->assertEquals(
            $expected,
            $endpoint->toAbsoluteEndpoint()
        );
    }

    public function allParamProvider(): array
    {
        return [
            'Several major params' => [
                'uri' => 'something/:guild_id/:channel_id/:webhook_id',
                'replacements' => ['::guild id::', '::channel id::', '::webhook id::'],
                'expected' => 'something/::guild id::/::channel id::/::webhook id::',
            ],
            'Single major param' => [
                'uri' => 'something/:guild_id',
                'replacements' => ['::guild id::'],
                'expected' => 'something/::guild id::',
            ],
            'Single major param, some minor params' => [
                'uri' => 'something/:guild_id/:some_param/:something_else',
                'replacements' => ['::guild id::', '::some param::', '::something else::'],
                'expected' => 'something/::guild id::/::some param::/::something else::',
            ],
            'Only minor params' => [
                'uri' => 'something/:something/:some_param/:other',
                'replacements' => ['::something::', '::some param::', '::something else::'],
                'expected' => 'something/::something::/::some param::/::something else::',
            ],
            'Minor and major params in weird order' => [
                'uri' => 'something/:something/:guild_id/:other/:channel_id',
                'replacements' => ['::something::', '::guild id::', '::something else::', '::channel id::'],
                'expected' => 'something/::something::/::guild id::/::something else::/::channel id::',
            ],

            // @see https://github.com/discord-php/DiscordPHP-Http/issues/16
            // 'Params with same prefix, short first' => [
            //     'uri' => 'something/:thing/:thing_other',
            //     'replacements' => ['::thing::', '::thing other::'],
            //     'expected' => 'something/::thing::/::thing other::',
            // ],
            // 'Params with same prefix, short first' => [
            //     'uri' => 'something/:thing_other/:thing',
            //     'replacements' => ['::thing other::', '::thing::'],
            //     'expected' => 'something/::thing other::/::thing::',
            // ],
        ];
    }

    public function testBindAssoc()
    {
        $endpoint = new Endpoint('something/:first/:second');
        $endpoint->bindAssoc([
            'second' => '::second::',
            'first' => '::first::',
        ]);

        $this->assertEquals(
            'something/::first::/::second::',
            $endpoint->toAbsoluteEndpoint()
        );
    }

    public function testItConvertsToString()
    {
        $this->assertEquals(
            'something/::first::/::second::',
            (string) Endpoint::bind(
                'something/:first/:second',
                '::first::',
                '::second::'
            )
        );
    }

    public function itCanAddQueryParams()
    {
        $endpoint = new Endpoint('something/:param');
        $endpoint->bindArgs('param');

        $endpoint->addQuery('something', 'value');
        $endpoint->addQuery('boolval', true);

        $this->assertEquals(
            'something/param?something=value&boolval=1',
            $endpoint->toAbsoluteEndpoint()
        );
    }

    public function itDoesNotAddQueryParamsForMajorParameters()
    {
        $endpoint = new Endpoint('something/:guild_id');
        $endpoint->bindArgs('param');

        $endpoint->addQuery('something', 'value');
        $endpoint->addQuery('boolval', true);

        $this->assertEquals(
            'something/param',
            $endpoint->toAbsoluteEndpoint(true)
        );
    }
}
