<?php
/**
 * Created by PhpStorm.
 * User: Milad Rahimi <info@miladrahimi.com>
 * Date: 6/23/2018 AD
 * Time: 13:00
 */

namespace MiladRahimi\Router\Tests;

use MiladRahimi\Router\Enums\HttpMethods;
use MiladRahimi\Router\Enums\RouteAttributes;
use MiladRahimi\Router\Router;
use MiladRahimi\Router\Tests\Classes\SampleMiddleware;
use Throwable;

class GroupedMappingTest extends TestCase
{
    /**
     * @throws Throwable
     */
    public function test_simple_group_routing()
    {
        $router = $this->createRouterWithMockedProperties();

        $router->group([], function (Router $router) {
            $router->map('GET', '/', $this->simpleController());
        });

        $router->dispatch();

        $this->assertEquals('Here I am!', $this->getOutput($router));
    }

    /**
     * @throws Throwable
     */
    public function test_group_routing_with_middleware()
    {
        $groupMiddleware = new SampleMiddleware(777);

        $groupAttributes = [
            RouteAttributes::MIDDLEWARE => $groupMiddleware,
        ];

        $router = $this->createRouterWithMockedProperties();

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
    public function test_group_routing_with_a_group_and_a_route_middleware()
    {
        $groupMiddleware = new SampleMiddleware(1001);
        $routeMiddleware = new SampleMiddleware(1002);

        $groupAttributes = [
            RouteAttributes::MIDDLEWARE => $groupMiddleware,
        ];

        $router = $this->createRouterWithMockedProperties();

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
    public function test_group_routing_with_prefix()
    {
        $this->mockRequest(HttpMethods::GET, 'http://example.com/group/page');

        $groupAttributes = [
            RouteAttributes::PREFIX => '/group',
        ];

        $router = $this->createRouterWithMockedProperties();

        $router->group($groupAttributes, function (Router $router) {
            $router->map('GET', '/page', $this->simpleController());
        });

        $router->dispatch();

        $this->assertEquals('Here I am!', $this->getOutput($router));
    }

    /**
     * @throws Throwable
     */
    public function test_group_routing_with_domain()
    {
        $this->mockRequest(HttpMethods::GET, 'http://sub.domain.tld/');

        $groupAttributes = [
            RouteAttributes::DOMAIN => 'sub.domain.tld',
        ];

        $router = $this->createRouterWithMockedProperties();

        $router->group($groupAttributes, function (Router $router) {
            $router->map('GET', '/', $this->simpleController());
        });

        $router->dispatch();

        $this->assertEquals('Here I am!', $this->getOutput($router));
    }

    /**
     * @throws Throwable
     */
    public function test_group_routing_with_domains_it_should_ignore_group_domain_when_the_route_has_one()
    {
        $this->mockRequest(HttpMethods::GET, 'http://sub2.domain.tld/');

        $groupAttributes = [
            RouteAttributes::DOMAIN => 'sub1.domain.tld',
        ];

        $router = $this->createRouterWithMockedProperties();

        $router->group($groupAttributes, function (Router $router) {
            $router->map('GET', '/', $this->simpleController(), [], 'sub2.domain.tld');
        });

        $router->dispatch();

        $this->assertEquals('Here I am!', $this->getOutput($router));
    }

    /**
     * @throws Throwable
     */
    public function test_simple_group_routing_it_should_remove_existing_name_before_the_group()
    {
        $router = $this->createRouterWithMockedProperties();

        $router->useName('NameForNothing');

        $router->group([], function (Router $router) {
            $router->map('GET', '/', $this->simpleController());
        });

        $router->dispatch();

        $this->assertEquals('Here I am!', $this->getOutput($router));
        $this->assertFalse($router->isRoute('NameForNothing'));
    }
}
