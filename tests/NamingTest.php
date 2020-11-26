<?php

namespace MiladRahimi\PhpRouter\Tests;

use MiladRahimi\PhpRouter\Enums\HttpMethods;
use MiladRahimi\PhpRouter\Routes\Route;
use Throwable;

class NamingTest extends TestCase
{
    /**
     * @throws Throwable
     */
    public function test_a_named_route()
    {
        $router = $this->router();
        $router->get('/', function (Route $route) {
            return $route->getName();
        }, 'home');
        $router->dispatch();

        $this->assertEquals('home', $this->output($router));
    }
}
