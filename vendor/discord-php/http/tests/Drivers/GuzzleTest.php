<?php

namespace Tests\Discord\Http\Drivers;

use Discord\Http\DriverInterface;
use Discord\Http\Drivers\Guzzle;
use React\EventLoop\Loop;
use Tests\Discord\Http\DriverInterfaceTest;

class GuzzleTest extends DriverInterfaceTest
{
    protected function getDriver(): DriverInterface
    {
        return new Guzzle(Loop::get());
    }
}
