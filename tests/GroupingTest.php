<?php
/**
 * Created by PhpStorm.
 * User: Milad Rahimi <info@miladrahimi.com>
 * Date: 6/23/2018 AD
 * Time: 13:00
 */

namespace MiladRahimi\PhpRouter\Tests;

use MiladRahimi\PhpRouter\Enums\GroupAttributes;
use MiladRahimi\PhpRouter\Enums\HttpMethods;
use MiladRahimi\PhpRouter\Router;
use MiladRahimi\PhpRouter\Tests\Classes\SampleMiddleware;
use Throwable;

class GroupingTest extends TestCase
{
    /**
     * @throws Throwable
     */
    public function test_simple_group()
    {
        $router = $this->createRouter();

        $router->group([], function (Router $router) {
            $router->map('GET', '/', $this->simpleController());
        });

        $router->dispatch();

        $this->assertEquals('Here I am!', $this->getOutput($router));
    }

    /**
     * @throws Throwable
     */
    public function test_middleware_object()
    {
        $groupMiddleware = new SampleMiddleware(777);

        $groupAttributes = [
            GroupAttributes::MIDDLEWARE => $groupMiddleware,
        ];

        $router = $this->createRouter();

        $router->group($groupAttributes, function (Router $router) {
            $router->map('GET', '/', $this->simpleController());
        });

        $router->dispatch();

        $this->assertEquals('Here I am!', $this->getOutput($router));
        $this->assertContains(777, SampleMiddleware::$output);
    }

    /**
     * @throws Throwable
     */
    public function test_string_middleware()
    {
        $groupAttributes = [
            GroupAttributes::MIDDLEWARE => SampleMiddleware::class,
        ];

        $router = $this->createRouter();

        $router->group($groupAttributes, function (Router $router) {
            $router->map('GET', '/', $this->simpleController());
        });

        $router->dispatch();

        $this->assertEquals('Here I am!', $this->getOutput($router));
    }

    /**
     * @throws Throwable
     */
    public function test_middleware_it_should_ignore_group_middleware_where_route_middleware_is_present()
    {
        $groupMiddleware = new SampleMiddleware(1001);
        $routeMiddleware = new SampleMiddleware(1002);

        $groupAttributes = [
            GroupAttributes::MIDDLEWARE => $groupMiddleware,
        ];

        $router = $this->createRouter();

        $router->group($groupAttributes, function (Router $router) use ($routeMiddleware) {
            $router->map('GET', '/', $this->simpleController(), $routeMiddleware);
        });

        $router->dispatch();

        $this->assertEquals('Here I am!', $this->getOutput($router));
        $this->assertContains(1001, SampleMiddleware::$output);
        $this->assertContains(1002, SampleMiddleware::$output);
    }

    /**
     * @throws Throwable
     */
    public function test_prefix()
    {
        $this->mockRequest(HttpMethods::GET, 'http://example.com/group/page');

        $groupAttributes = [
            GroupAttributes::PREFIX => '/group',
        ];

        $router = $this->createRouter();

        $router->group($groupAttributes, function (Router $router) {
            $router->map('GET', '/page', $this->simpleController());
        });

        $router->dispatch();

        $this->assertEquals('Here I am!', $this->getOutput($router));
    }

    /**
     * @throws Throwable
     */
    public function test_domain()
    {
        $this->mockRequest(HttpMethods::GET, 'http://sub.domain.tld/');

        $groupAttributes = [
            GroupAttributes::DOMAIN => 'sub.domain.tld',
        ];

        $router = $this->createRouter();

        $router->group($groupAttributes, function (Router $router) {
            $router->map('GET', '/', $this->simpleController());
        });

        $router->dispatch();

        $this->assertEquals('Here I am!', $this->getOutput($router));
    }

    /**
     * @throws Throwable
     */
    public function test_domain_it_should_ignore_group_domain_where_route_domain_is_present()
    {
        $this->mockRequest(HttpMethods::GET, 'http://sub2.domain.tld/');

        $groupAttributes = [
            GroupAttributes::DOMAIN => 'sub1.domain.tld',
        ];

        $router = $this->createRouter();

        $router->group($groupAttributes, function (Router $router) {
            $router->map('GET', '/', $this->simpleController(), [], 'sub2.domain.tld');
        });

        $router->dispatch();

        $this->assertEquals('Here I am!', $this->getOutput($router));
    }

    /**
     * @throws Throwable
     */
    public function test_naming_it_should_remove_existing_name_before_the_group()
    {
        $router = $this->createRouter();

        $router->useName('NameForNothing');

        $router->group([], function (Router $router) {
            $router->map('GET', '/', $this->simpleController());
        });

        $router->dispatch();

        $this->assertEquals('Here I am!', $this->getOutput($router));
        $this->assertFalse($router->isRoute('NameForNothing'));
    }
}
