<?php

namespace MiladRahimi\PhpRouter\Tests\Features;

use Laminas\Diactoros\ServerRequest;
use MiladRahimi\PhpContainer\Container;
use MiladRahimi\PhpRouter\Routing\Route;
use MiladRahimi\PhpRouter\Tests\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

class InjectionTest extends TestCase
{
    /**
     * @throws Throwable
     */
    public function test_injecting_request()
    {
        $router = $this->router();
        $router->get('/', function (ServerRequest $request) {
            return get_class($request);
        });
        $router->dispatch();

        $this->assertEquals(ServerRequest::class, $this->output($router));
    }

    /**
     * @throws Throwable
     */
    public function test_injecting_request_by_interface()
    {
        $router = $this->router();
        $router->get('/', function (ServerRequestInterface $request) {
            return get_class($request);
        });
        $router->dispatch();

        $this->assertEquals(ServerRequest::class, $this->output($router));
    }

    /**
     * @throws Throwable
     */
    public function test_injecting_route()
    {
        $router = $this->router();
        $router->get('/', function (Route $route) {
            return $route->getPath();
        });
        $router->dispatch();

        $this->assertEquals('/', $this->output($router));
    }

    /**
     * @throws Throwable
     */
    public function test_injecting_container()
    {
        $router = $this->router();
        $router->get('/', function (Container $container) {
            return get_class($container);
        });
        $router->dispatch();

        $this->assertEquals(Container::class, $this->output($router));
    }

    /**
     * @throws Throwable
     */
    public function test_injecting_container_by_interface()
    {
        $router = $this->router();
        $router->get('/', function (ContainerInterface $container) {
            return get_class($container);
        });
        $router->dispatch();

        $this->assertEquals(Container::class, $this->output($router));
    }
}
