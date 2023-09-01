<?php

namespace Tests\Discord\Http\Drivers;

use Discord\Http\DriverInterface;
use Discord\Http\Drivers\React;
use React\EventLoop\Loop;
use Tests\Discord\Http\DriverInterfaceTest;

class ReactTest extends DriverInterfaceTest
{
    protected function getDriver(): DriverInterface
    {
        return new React(Loop::get());
    }
}
