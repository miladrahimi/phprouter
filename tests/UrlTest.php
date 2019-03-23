<?php

namespace MiladRahimi\PhpRouter\Tests;

use MiladRahimi\PhpRouter\Enums\HttpMethods;
use MiladRahimi\PhpRouter\Router;
use Throwable;

/**
 * Class UrlTest
 *
 * @package MiladRahimi\PhpRouter\Tests
 */
class UrlTest extends TestCase
{
    /**
     * @throws Throwable
     */
    public function test_generating_url_for_home()
    {
        $router = $this->router()
            ->name('home')
            ->get('/', function (Router $r) {
                return $r->url('home');
            })
            ->dispatch();

        $this->assertEquals('/', $this->extract($router));
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

        $this->assertEquals('/page', $this->extract($router));
    }

    /**
     * @throws Throwable
     */
    public function test_generating_url_for_a_page_with_required_parameter()
    {
        $this->mockRequest(HttpMethods::GET, 'http://web.com/666');

        $router = $this->router()
            ->name('page')
            ->get('/{name}', function (Router $r) {
                return $r->url('page', ['name' => '13']);
            })
            ->dispatch();

        $this->assertEquals('/13', $this->extract($router));
    }

    /**
     * @throws Throwable
     */
    public function test_generating_url_for_a_page_with_optional_parameter()
    {
        $this->mockRequest(HttpMethods::GET, 'http://web.com/666');

        $router = $this->router()
            ->name('page')
            ->get('/{name?}', function (Router $r) {
                return $r->url('page', ['name' => '13']);
            })
            ->dispatch();

        $this->assertEquals('/13', $this->extract($router));
    }

    /**
     * @throws Throwable
     */
    public function test_generating_url_for_a_page_with_optional_parameter_2()
    {
        $this->mockRequest(HttpMethods::GET, 'http://web.com/666');

        $router = $this->router()
            ->name('page')
            ->get('/{name?}', function (Router $r) {
                return $r->url('page');
            })
            ->dispatch();

        $this->assertEquals('/', $this->extract($router));
    }

    /**
     * @throws Throwable
     */
    public function test_generating_url_for_a_page_with_optional_parameter_3()
    {
        $this->mockRequest(HttpMethods::GET, 'http://web.com/page/666');

        $router = $this->router()
            ->name('page')
            ->get('/page/?{name?}', function (Router $r) {
                return $r->url('page');
            })
            ->dispatch();

        $this->assertEquals('/page', $this->extract($router));
    }

    /**
     * @throws Throwable
     */
    public function test_generating_url_for_a_page_with_optional_parameter_4()
    {
        $this->mockRequest(HttpMethods::GET, 'http://web.com/page/666');

        $router = $this->router()
            ->name('page')
            ->get('/page/?{name?}', function (Router $r) {
                return $r->url('page');
            })
            ->dispatch();

        $this->assertEquals('/page', $this->extract($router));
    }

    /**
     * @throws Throwable
     */
    public function test_generating_url_for_undefined_route()
    {
        $router = $this->router()
            ->get('/', function (Router $r) {
                // There is no route with this name
                // It must return NULL (empty response)
                return $r->url('home');
            })
            ->dispatch();

        $this->assertEquals('', $this->extract($router));
    }
}
