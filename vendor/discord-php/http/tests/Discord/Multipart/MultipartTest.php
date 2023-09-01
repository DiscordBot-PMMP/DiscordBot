<?php

/*
 * This file is a part of the DiscordPHP-Http project.
 *
 * Copyright (c) 2021-present David Cole <david.cole1340@gmail.com>
 *
 * This file is subject to the MIT license that is bundled
 * with this source code in the LICENSE file.
 */

namespace Tests\Discord\Http\Multipart;

use Discord\Http\Multipart\MultipartBody;
use Discord\Http\Multipart\MultipartField;
use Mockery;
use PHPUnit\Framework\TestCase;

class MultipartTest extends TestCase
{
    /**
     * @dataProvider multipartFieldStringConversionProvider
     */
    public function testMultipartFieldStringConversion(array $constructorArgs, string $expected)
    {
        $multipartField = new MultipartField(...$constructorArgs);

        $this->assertEquals($expected, (string) $multipartField);
    }

    public function multipartFieldStringConversionProvider(): array
    {
        return [
            'Completely filled' => [
                'args' => [
                    '::name::',
                    '::content::',
                    [
                        'Header-Name' => 'Value',
                    ],
                    '::filename::',
                ],

                'expected' => <<<EXPECTED
                Content-Disposition: form-data; name="::name::"; filename="%3A%3Afilename%3A%3A"
                Header-Name: Value

                ::content::

                EXPECTED
            ],
            'Missing filename' => [
                'args' => [
                    '::name::',
                    '::content::',
                    [
                        'Header-Name' => 'Value',
                    ],
                    null,
                ],

                'expected' => <<<EXPECTED
                Content-Disposition: form-data; name="::name::"
                Header-Name: Value

                ::content::

                EXPECTED
            ],
            'No headers' => [
                'args' => [
                    '::name::',
                    '::content::',
                    [],
                    '::filename::',
                ],

                'expected' => <<<EXPECTED
                Content-Disposition: form-data; name="::name::"; filename="%3A%3Afilename%3A%3A"

                ::content::

                EXPECTED
            ],
        ];
    }

    public function testMultipartBodyBuilding()
    {
        $fields = array_map(function (string $return) {
            $mock = Mockery::mock(MultipartField::class);
            $mock->shouldReceive('__toString')->andReturn($return);

            return $mock;
        }, ['::first field::', '::second field::', '::third field::']);

        $multipartBody = new MultipartBody($fields, '::boundary::');

        $this->assertEquals(
            <<<EXPECTED
            --::boundary::
            ::first field::
            --::boundary::
            ::second field::
            --::boundary::
            ::third field::
            --::boundary::--
            EXPECTED,
            (string) $multipartBody
        );

        $this->assertEquals([
            'Content-Type' => 'multipart/form-data; boundary=::boundary::',
            'Content-Length' => strlen((string) $multipartBody),
        ], $multipartBody->getHeaders());
    }

    public function testGeneratingBoundary()
    {
        $multipartBody = new MultipartBody([
            Mockery::mock(MultipartField::class),
        ]);

        $this->assertNotNull($multipartBody->boundary);
    }
}
