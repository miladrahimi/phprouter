<?php

namespace MiladRahimi\PhpRouter\Tests;

use MiladRahimi\PhpRouter\Enums\GroupAttributes;
use MiladRahimi\PhpRouter\Enums\HttpMethods;
use MiladRahimi\PhpRouter\Router;
use MiladRahimi\PhpRouter\Tests\Classes\SampleMiddleware;
use Throwable;

/**
 * Class GroupingTest
 *
 * @package MiladRahimi\PhpRouter\Tests
 */
class GroupingTest extends TestCase
{
    /**
     * @throws Throwable
     */
    public function test_with_no_attribute()
    {
        $router = $this->router();

        $router->group([], function (Router $router) {
            $router->get('/', $this->controller());
        });

        $router->dispatch();

        $this->assertEquals('OK', $this->extract($router));
    }

    /**
     * @throws Throwable
     */
    public function test_with_a_middleware()
    {
        $middleware = new SampleMiddleware(mt_rand(1, 9999999));

        $groupAttributes = [GroupAttributes::MIDDLEWARE => $middleware];

        $router = $this->router();

        $router->group($groupAttributes, function (Router $router) {
            $router->get('/', $this->controller());
        });

        $router->dispatch();

        $this->assertEquals('OK', $this->extract($router));
        $this->assertContains($middleware->content, SampleMiddleware::$output);
    }

    /**
     * @throws Throwable
     */
    public function test_with_route_and_group_middleware()
    {
        $groupMiddleware = new SampleMiddleware(mt_rand(1, 9999999));
        $routeMiddleware = new SampleMiddleware(mt_rand(1, 9999999));

        $groupAttributes = [
            GroupAttributes::MIDDLEWARE => $groupMiddleware,
        ];

        $router = $this->router();

        $router->group($groupAttributes, function (Router $router) use ($routeMiddleware) {
            $router->get('/', $this->controller(), $routeMiddleware);
        });

        $router->dispatch();

        $this->assertEquals('OK', $this->extract($router));
        $this->assertContains($groupMiddleware->content, SampleMiddleware::$output);
        $this->assertContains($routeMiddleware->content, SampleMiddleware::$output);
    }

    /**
     * @throws Throwable
     */
    public function test_nested_groups_with_middleware()
    {
        $group1Middleware = new SampleMiddleware(mt_rand(1, 9999999));
        $group2Middleware = new SampleMiddleware(mt_rand(1, 9999999));

        $group1Attributes = [
            GroupAttributes::MIDDLEWARE => $group1Middleware,
        ];

        $group2Attributes = [
            GroupAttributes::MIDDLEWARE => $group2Middleware,
        ];

        $router = $this->router();

        $router->group($group1Attributes, function (Router $router) use ($group2Attributes) {
            $router->group($group2Attributes, function (Router $router) {
                $router->get('/', $this->controller());
            });
        });

        $router->dispatch();

        $this->assertEquals('OK', $this->extract($router));
        $this->assertContains($group1Middleware->content, SampleMiddleware::$output);
        $this->assertContains($group2Middleware->content, SampleMiddleware::$output);
    }

    /**
     * @throws Throwable
     */
    public function test_with_a_prefix()
    {
        $this->mockRequest(HttpMethods::GET, 'http://example.com/group/page');

        $groupAttributes = [
            GroupAttributes::PREFIX => '/group',
        ];

        $router = $this->router();

        $router->group($groupAttributes, function (Router $router) {
            $router->get('/page', $this->controller());
        });

        $router->dispatch();

        $this->assertEquals('OK', $this->extract($router));
    }

    /**
     * @throws Throwable
     */
    public function test_nested_groups_with_prefix()
    {
        $this->mockRequest(HttpMethods::GET, 'http://example.com/group1/group2/page');

        $group1Attributes = [
            GroupAttributes::PREFIX => '/group1',
        ];

        $group2Attributes = [
            GroupAttributes::PREFIX => '/group2',
        ];

        $router = $this->router();

        $router->group($group1Attributes, function (Router $router) use ($group2Attributes) {
            $router->group($group2Attributes, function (Router $router) {
                $router->get('/page', $this->controller());
            });
        });

        $router->dispatch();

        $this->assertEquals('OK', $this->extract($router));
    }

    /**
     * @throws Throwable
     */
    public function test_with_namespace()
    {
        $namespace = 'MiladRahimi\PhpRouter\Tests\Classes';

        $router = $this->router();

        $router->group(['namespace' => $namespace], function (Router $router) {
            $router->get('/', 'SampleController@home');
        });

        $router->dispatch();

        $this->assertEquals('Home', $this->extract($router));
    }

    /**
     * @throws Throwable
     */
    public function test_with_domain()
    {
        $this->mockRequest(HttpMethods::GET, 'http://sub.domain.tld/');

        $groupAttributes = [
            GroupAttributes::DOMAIN => 'sub.domain.tld',
        ];

        $router = $this->router();

        $router->group($groupAttributes, function (Router $router) {
            $router->get('/', $this->controller());
        });

        $router->dispatch();

        $this->assertEquals('OK', $this->extract($router));
    }

    /**
     * @throws Throwable
     */
    public function test_with_group_and_route_domain()
    {
        $this->mockRequest(HttpMethods::GET, 'http://sub2.domain.com/');

        $groupAttributes = [
            GroupAttributes::DOMAIN => 'sub1.domain.com',
        ];

        $router = $this->router();

        $router->group($groupAttributes, function (Router $router) {
            $router->get('/', $this->controller(), [], 'sub2.domain.com');
        });

        $router->dispatch();

        $this->assertEquals('OK', $this->extract($router));
    }

    /**
     * @throws Throwable
     */
    public function test_nested_groups_with_domain()
    {
        $this->mockRequest(HttpMethods::GET, 'http://sub2.domain.com/');

        $group1Attributes = [
            GroupAttributes::DOMAIN => 'sub1.domain.com',
        ];

        $group2Attributes = [
            GroupAttributes::DOMAIN => 'sub2.domain.com',
        ];

        $router = $this->router();

        $router->group($group1Attributes, function (Router $router) use ($group2Attributes) {
            $router->group($group2Attributes, function (Router $router) {
                $router->get('/', $this->controller());

            });
        });

        $router->dispatch();

        $this->assertEquals('OK', $this->extract($router));
    }

    /**
     * @throws Throwable
     */
    public function test_naming_it_should_remove_existing_name_before_the_group()
    {
        $router = $this->router();

        $router->name('NameForNothing');

        $router->group([], function (Router $router) {
            $router->get('/', $this->controller());
        });

        $router->dispatch();

        $this->assertEquals('OK', $this->extract($router));
        $this->assertFalse($router->currentRoute()->getName() == 'NameForNothing');
    }
}
