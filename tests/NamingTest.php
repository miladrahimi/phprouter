<?php

namespace MiladRahimi\PhpRouter\Tests;

use MiladRahimi\PhpRouter\Enums\HttpMethods;
use MiladRahimi\PhpRouter\Route;
use Throwable;

class NamingTest extends TestCase
{
    /**
     * @throws Throwable
     */
    public function test_a_named_route()
    {
        $router = $this->router()
            ->get('/', function (Route $route) {
                return $route->getName();
            }, 'home')
            ->dispatch();

        $this->assertEquals('home', $this->output($router));
    }
}
