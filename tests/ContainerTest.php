<?php

namespace MiladRahimi\PhpRouter\Tests;

use MiladRahimi\PhpContainer\Container;
use MiladRahimi\PhpRouter\Router;
use MiladRahimi\PhpRouter\Tests\Testing\FakePublisher;
use Throwable;

class ContainerTest extends TestCase
{
    /**
     * @throws Throwable
     */
    public function test_setting_and_getting_container()
    {
        $container = new Container();
        $container->singleton('name', 'Pink Floyd');

        $router = new Router();
        $router->setPublisher(new FakePublisher());
        $router->setContainer($container);

        $router->get('/', function (Container $container) {
            return $container->get('name');
        })->dispatch();

        $this->assertEquals('Pink Floyd', $this->output($router));
    }
}
