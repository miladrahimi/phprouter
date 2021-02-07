<?php

namespace MiladRahimi\PhpRouter\Tests\Features;

use MiladRahimi\PhpRouter\Exceptions\UndefinedRouteException;
use MiladRahimi\PhpRouter\Tests\TestCase;
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
        $router->get('/', function (Url $url) {
            return $url->make('home');
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
        $router->get('/', function (Url $url) {
            return $url->make('home');
        }, 'home');
        $router->get('/page', function (Url $url) {
            return $url->make('page');
        }, 'page');
        $router->dispatch();

        $this->assertEquals('/page', $this->output($router));
    }

    /**
     * @throws Throwable
     */
    public function test_generating_url_for_a_page_with_required_parameter()
    {
        $this->mockRequest('GET', 'http://web.com/');

        $router = $this->router();
        $router->get('/', function (Url $url) {
            return $url->make('page', ['name' => 'about']);
        });
        $router->get('/{name}', function () {
            return 'empty';
        }, 'page');
        $router->dispatch();

        $this->assertEquals('/about', $this->output($router));
    }

    /**
     * @throws Throwable
     */
    public function test_generating_url_for_a_page_with_optional_parameter()
    {
        $this->mockRequest('GET', 'http://web.com/');

        $router = $this->router();
        $router->get('/', function (Url $url) {
            return $url->make('post', ['post' => 666]);
        });
        $router->get('/blog/{post?}', function () {
            return 'empty';
        }, 'post');
        $router->dispatch();

        $this->assertEquals('/blog/666', $this->output($router));
    }

    /**
     * @throws Throwable
     */
    public function test_generating_url_for_a_page_with_optional_parameter_ignored()
    {
        $this->mockRequest('GET', 'http://web.com/');

        $router = $this->router();
        $router->get('/', function (Url $url) {
            return $url->make('page');
        });
        $router->get('/profile/{name?}', function () {
            return 'empty';
        }, 'page');
        $router->dispatch();

        $this->assertEquals('/profile/', $this->output($router));
    }

    /**
     * @throws Throwable
     */
    public function test_generating_url_for_a_page_with_optional_parameter_and_slash_ignored()
    {
        $this->mockRequest('GET', 'http://web.com/');

        $router = $this->router();
        $router->get('/', function (Url $url) {
            return $url->make('page');
        });
        $router->get('/profile/?{name?}', function () {
            return 'empty';
        }, 'page');
        $router->dispatch();

        $this->assertEquals('/profile', $this->output($router));
    }

    /**
     * @throws Throwable
     */
    public function test_generating_url_for_undefined_route()
    {
        $this->expectException(UndefinedRouteException::class);
        $this->expectExceptionMessage("There is no route named `page`.");

        $router = $this->router();
        $router->get('/', function (Url $r) {
            return $r->make('page');
        });
        $router->dispatch();
    }
}
