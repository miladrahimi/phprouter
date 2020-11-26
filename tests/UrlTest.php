<?php

namespace MiladRahimi\PhpRouter\Tests;

use MiladRahimi\PhpRouter\Exceptions\UndefinedRouteException;
use MiladRahimi\PhpRouter\Router;
use Throwable;

class UrlTest extends TestCase
{
    /**
     * @throws Throwable
     */
    public function test_generating_url_for_the_homepage()
    {
        $router = $this->router();
        $router->get('/', function (Router $r) {
            return $r->url('home');
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
        $router->get('/', function (Router $r) {
            return $r->url('home');
        }, 'home');
        $router->get('/page', function (Router $r) {
            return $r->url('page');
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
        $router->get('/{name}', function (Router $r) {
            return $r->url('page', ['name' => 'about']);
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
        $router->get('/{name?}', function (Router $r) {
            return $r->url('page', ['name' => 'about']);
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
        $router->get('/{name?}', function (Router $r) {
            return $r->url('page');
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
        $router->get('/page/?{name?}', function (Router $r) {
            return $r->url('page');
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
        $this->expectExceptionMessage("There is no route with name `home`.");

        $router = $this->router();
        $router->get('/', function (Router $r) {
            return $r->url('home');
        });
        $router->dispatch();
    }
}
