<?php

namespace MiladRahimi\PhpRouter\Tests;

use MiladRahimi\PhpContainer\Container;
use Throwable;

class ContainerTest extends TestCase
{
    /**
     * @throws Throwable
     */
    public function test_binding_and_resolving_with_container()
    {
        $router = $this->router();

        $container = $router->getContainer();
        $container->singleton('name', 'Pink Floyd');
        $router->setContainer($container);

        $router->get('/', function (Container $container) {
            return $container->get('name');
        });

        $router->dispatch();

        $this->assertEquals('Pink Floyd', $this->output($router));
    }
}
