<?php

namespace MiladRahimi\PhpRouter\Tests;

use MiladRahimi\PhpRouter\Enums\HttpMethods;
use MiladRahimi\PhpRouter\Router;
use MiladRahimi\PhpRouter\Tests\Testing\SampleMiddleware;
use Throwable;

class GroupingTest extends TestCase
{
    /**
     * @throws Throwable
     */
    public function test_with_no_attribute()
    {
        $router = $this->router()
            ->group([], function (Router $router) {
                $router->get('/', $this->OkController());
            })->dispatch();

        $this->assertEquals('OK', $this->output($router));
    }

    /**
     * @throws Throwable
     */
    public function test_with_a_middleware()
    {
        $middleware = new SampleMiddleware(666);

        $router = $this->router()
            ->group(['middleware' => $middleware], function (Router $router) {
                $router->get('/', $this->OkController());
            })->dispatch();

        $this->assertEquals('OK', $this->output($router));
        $this->assertContains($middleware->content, SampleMiddleware::$output);
    }

    /**
     * @throws Throwable
     */
    public function test_with_route_and_group_middleware()
    {
        $groupMiddleware = new SampleMiddleware(13);
        $routeMiddleware = new SampleMiddleware(666);

        $router = $this->router()
            ->group(['middleware' => $groupMiddleware], function (Router $router) use ($routeMiddleware) {
                $router->get('/', $this->OkController(), $routeMiddleware);
            })->dispatch();

        $this->assertEquals('OK', $this->output($router));
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

        $router = $this->router()
            ->group(['middleware' => $group1Middleware], function (Router $router) use ($group2Middleware) {
                $router->group(['middleware' => $group2Middleware], function (Router $router) {
                    $router->get('/', $this->OkController());
                });
            })->dispatch();

        $this->assertEquals('OK', $this->output($router));
        $this->assertContains($group1Middleware->content, SampleMiddleware::$output);
        $this->assertContains($group2Middleware->content, SampleMiddleware::$output);
    }

    /**
     * @throws Throwable
     */
    public function test_with_a_prefix()
    {
        $this->mockRequest(HttpMethods::GET, 'http://example.com/group/page');

        $router = $this->router()
            ->group(['prefix' => '/group'], function (Router $router) {
                $router->get('/page', $this->OkController());
            })->dispatch();

        $this->assertEquals('OK', $this->output($router));
    }

    /**
     * @throws Throwable
     */
    public function test_nested_groups_with_prefix()
    {
        $this->mockRequest(HttpMethods::GET, 'http://example.com/group1/group2/page');

        $router = $this->router()
            ->group(['prefix' => '/group1'], function (Router $router) {
                $router->group(['prefix' => '/group2'], function (Router $router) {
                    $router->get('/page', $this->OkController());
                });
            })->dispatch();

        $this->assertEquals('OK', $this->output($router));
    }

    /**
     * @throws Throwable
     */
    public function test_with_namespace()
    {
        $namespace = 'MiladRahimi\PhpRouter\Tests\Testing';

        $router = $this->router()
            ->group(['namespace' => $namespace], function (Router $router) {
                $router->get('/', 'SampleController@home');
            })->dispatch();

        $this->assertEquals('Home', $this->output($router));
    }

    /**
     * @throws Throwable
     */
    public function test_with_domain()
    {
        $this->mockRequest(HttpMethods::GET, 'http://sub.domain.tld/');

        $router = $this->router()
            ->group(['domain' => 'sub.domain.tld'], function (Router $router) {
                $router->get('/', $this->OkController());
            })->dispatch();

        $this->assertEquals('OK', $this->output($router));
    }

    /**
     * @throws Throwable
     */
    public function test_with_group_and_route_domain_it_should_only_consider_route_domain()
    {
        $this->mockRequest(HttpMethods::GET, 'http://sub2.domain.com/');

        $router = $this->router()
            ->group(['domain' => 'sub1.domain.com'], function (Router $router) {
                $router->get('/', $this->OkController(), [], 'sub2.domain.com');
            })->dispatch();

        $this->assertEquals('OK', $this->output($router));
    }

    /**
     * @throws Throwable
     */
    public function test_nested_groups_with_domain_it_should_consider_the_inner_group_domain()
    {
        $this->mockRequest(HttpMethods::GET, 'http://sub2.domain.com/');

        $router = $this->router()
            ->group(['domain' => 'sub1.domain.com'], function (Router $router) {
                $router->group(['domain' => 'sub2.domain.com'], function (Router $router) {
                    $router->get('/', $this->OkController());

                });
            })->dispatch();

        $this->assertEquals('OK', $this->output($router));
    }

    /**
     * @throws Throwable
     */
    public function test_naming_it_should_remove_existing_name_before_the_group()
    {
        $router = $this->router()
            ->name('NameForNothing')
            ->group([], function (Router $router) {
                $router->get('/', $this->OkController());
            })->dispatch();

        $this->assertEquals('OK', $this->output($router));
        $this->assertFalse($router->currentRoute()->getName() == 'NameForNothing');
    }
}
