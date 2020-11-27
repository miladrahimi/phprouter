<?php

namespace MiladRahimi\PhpRouter\Tests;

use MiladRahimi\PhpRouter\Exceptions\UndefinedRouteException;
use MiladRahimi\PhpRouter\Router;
use MiladRahimi\PhpRouter\Url;
use Throwable;

class UrlTest extends TestCase
{
    /**
     * @throws Throwable
     */
    public function test_generating_url_for_the_homepage()
    {
        $router = $this->router();
        $router->get('/', function (Url $r) {
            return $r->make('home');
        }, 'home');
        $router->dispatch();

        $this->assertEquals('/', $this->output($router));
    }

    /**
     * @throws Throwable
     */
    public function test_generating_url_for_a_page()
    {
        $this->mockRequest('GET', 'http://web.com/page');

        $router = $this->router();
        $router->get('/', function (Url $r) {
            return $r->make('home');
        }, 'home');
        $router->get('/page', function (Url $r) {
            return $r->make('page');
        }, 'page');
        $router->dispatch();

        $this->assertEquals('/page', $this->output($router));
    }

    /**
     * @throws Throwable
     */
    public function test_generating_url_for_a_page_with_required_parameter()
    {
        $this->mockRequest('GET', 'http://web.com/contact');

        $router = $this->router();
        $router->get('/{name}', function (Url $r) {
            return $r->make('page', ['name' => 'about']);
        }, 'page');
        $router->dispatch();

        $this->assertEquals('/about', $this->output($router));
    }

    /**
     * @throws Throwable
     */
    public function test_generating_url_for_a_page_with_optional_parameter()
    {
        $this->mockRequest('GET', 'http://web.com/contact');

        $router = $this->router();
        $router->get('/{name?}', function (Url $r) {
            return $r->make('page', ['name' => 'about']);
        }, 'page');
        $router->dispatch();

        $this->assertEquals('/about', $this->output($router));
    }

    /**
     * @throws Throwable
     */
    public function test_generating_url_for_a_page_with_optional_parameter_2()
    {
        $this->mockRequest('GET', 'http://web.com/contact');

        $router = $this->router();
        $router->get('/{name?}', function (Url $r) {
            return $r->make('page');
        }, 'page');
        $router->dispatch();

        $this->assertEquals('/', $this->output($router));
    }

    /**
     * @throws Throwable
     */
    public function test_generating_url_for_a_page_with_optional_parameter_3()
    {
        $this->mockRequest('GET', 'http://web.com/page/contact');

        $router = $this->router();
        $router->get('/page/?{name?}', function (Url $r) {
            return $r->make('page');
        }, 'page');
        $router->dispatch();

        $this->assertEquals('/page', $this->output($router));
    }

    /**
     * @throws Throwable
     */
    public function test_generating_url_for_undefined_route()
    {
        $this->expectException(UndefinedRouteException::class);
        $this->expectExceptionMessage("There is no route named `home`.");

        $router = $this->router();
        $router->get('/', function (Url $r) {
            return $r->make('home');
        });
        $router->dispatch();
    }
}
