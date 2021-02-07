<?php

namespace MiladRahimi\PhpRouter\Tests\Features;

use MiladRahimi\PhpRouter\Router;
use MiladRahimi\PhpRouter\Routing\Attributes;
use MiladRahimi\PhpRouter\Routing\Route;
use MiladRahimi\PhpRouter\Tests\Common\SampleMiddleware;
use MiladRahimi\PhpRouter\Tests\TestCase;
use Throwable;

class RouteTest extends TestCase
{
    /**
     * @throws Throwable
     */
    public function test_current_route_for_a_route_with_all_attributes()
    {
        $this->mockRequest('POST', 'http://shop.com/admin/profile/666');

        $router = $this->router();

        $attributes = [
            Attributes::DOMAIN => 'shop.com',
            Attributes::MIDDLEWARE => [SampleMiddleware::class],
            Attributes::PREFIX => '/admin',
        ];

        $router->group($attributes, function (Router $router) {
            $router->post('/profile/{id}', function (Route $route) {
                return $route->__toString();
            }, 'admin.profile');
        });

        $router->dispatch();

        $expected = [
            'method' => 'POST',
            'path' => '/admin/profile/{id}',
            'controller' => function () {
                return 'Closure';
            },
            'name' => 'admin.profile',
            'middleware' => [SampleMiddleware::class],
            'domain' => 'shop.com',
            'uri' => '/admin/profile/666',
            'parameters' => ['id' => '666'],
        ];

        $this->assertEquals(json_encode($expected), $this->output($router));
    }

    /**
     * @throws Throwable
     */
    public function test_lately_added_attributes_of_route()
    {
        $this->mockRequest('POST', 'http://shop.com/admin/profile/666');

        $router = $this->router();

        $router->post('/admin/profile/{id}', function (Route $route) {
            return [
                $route->getParameters(),
                $route->getUri(),
            ];
        }, 'admin.profile');

        $router->dispatch();

        $expected = [['id' => '666'], '/admin/profile/666'];

        $this->assertEquals(json_encode($expected), $this->output($router));
    }
}
