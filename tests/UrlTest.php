<?php

namespace MiladRahimi\PhpRouter\Tests;

use MiladRahimi\PhpRouter\Enums\HttpMethods;
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
        $router = $this->router()
            ->name('home')
            ->get('/', function (Router $r) {
                return $r->url('home');
            })
            ->dispatch();

        $this->assertEquals('/', $this->output($router));
    }

    /**
     * @throws Throwable
     */
    public function test_generating_url_for_a_page()
    {
        $this->mockRequest(HttpMethods::GET, 'http://web.com/page');

        $router = $this->router()
            ->name('home')->get('/', function (Router $r) {
                return $r->url('home');
            })
            ->name('page')->get('/page', function (Router $r) {
                return $r->url('page');
            })
            ->dispatch();

        $this->assertEquals('/page', $this->output($router));
    }

    /**
     * @throws Throwable
     */
    public function test_generating_url_for_a_page_with_required_parameter()
    {
        $this->mockRequest(HttpMethods::GET, 'http://web.com/contact');

        $router = $this->router()
            ->name('page')
            ->get('/{name}', function (Router $r) {
                return $r->url('page', ['name' => 'about']);
            })
            ->dispatch();

        $this->assertEquals('/about', $this->output($router));
    }

    /**
     * @throws Throwable
     */
    public function test_generating_url_for_a_page_with_optional_parameter()
    {
        $this->mockRequest(HttpMethods::GET, 'http://web.com/contact');

        $router = $this->router()
            ->name('page')
            ->get('/{name?}', function (Router $r) {
                return $r->url('page', ['name' => 'about']);
            })
            ->dispatch();

        $this->assertEquals('/about', $this->output($router));
    }

    /**
     * @throws Throwable
     */
    public function test_generating_url_for_a_page_with_optional_parameter_2()
    {
        $this->mockRequest(HttpMethods::GET, 'http://web.com/contact');

        $router = $this->router()
            ->name('page')
            ->get('/{name?}', function (Router $r) {
                return $r->url('page');
            })
            ->dispatch();

        $this->assertEquals('/', $this->output($router));
    }

    /**
     * @throws Throwable
     */
    public function test_generating_url_for_a_page_with_optional_parameter_3()
    {
        $this->mockRequest(HttpMethods::GET, 'http://web.com/page/contact');

        $router = $this->router()
            ->name('page')
            ->get('/page/?{name?}', function (Router $r) {
                return $r->url('page');
            })
            ->dispatch();

        $this->assertEquals('/page', $this->output($router));
    }

    /**
     * @throws Throwable
     */
    public function test_generating_url_for_undefined_route()
    {
        $this->expectException(UndefinedRouteException::class);
        $this->expectExceptionMessage("There is no route with name `home`.");

        $this->router()
            ->get('/', function (Router $r) {
                return $r->url('home');
            })
            ->dispatch();
    }
}
