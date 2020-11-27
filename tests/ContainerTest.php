<?php

namespace MiladRahimi\PhpRouter\Tests;

use MiladRahimi\PhpContainer\Container;
use MiladRahimi\PhpRouter\Router;
use MiladRahimi\PhpRouter\Services\Publisher;
use MiladRahimi\PhpRouter\Tests\Testing\TrapPublisher;
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
