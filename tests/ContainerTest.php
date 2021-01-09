<?php

namespace MiladRahimi\PhpRouter\Tests;

use MiladRahimi\PhpContainer\Container;
use Throwable;

class ContainerTest extends TestCase
{
    /**
     * @throws Throwable
     */
    public function test_setting_and_getting_container()
    {
        $router = $this->router();
        $router->getContainer()->singleton('name', 'Pink Floyd');;

        $router->get('/', function (Container $container) {
            return $container->get('name');
        });

        $router->dispatch();

        $this->assertEquals('Pink Floyd', $this->output($router));
    }
}
