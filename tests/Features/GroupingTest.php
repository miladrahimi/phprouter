<?php

namespace MiladRahimi\PhpRouter\Tests\Features;

use MiladRahimi\PhpRouter\Router;
use MiladRahimi\PhpRouter\Tests\Common\SampleController;
use MiladRahimi\PhpRouter\Tests\Common\SampleMiddleware;
use MiladRahimi\PhpRouter\Tests\TestCase;
use Throwable;

class GroupingTest extends TestCase
{
    /**
     * @throws Throwable
     */
    public function test_with_no_attribute()
    {
        $router = $this->router();
        $router->group([], function (Router $router) {
            $router->get('/', [SampleController::class, 'ok']);
        });
        $router->dispatch();

        $this->assertEquals('OK', $this->output($router));
    }

    /**
     * @throws Throwable
     */
    public function test_with_a_middleware()
    {
        $middleware = new SampleMiddleware(666);

        $router = $this->router();
        $router->group(['middleware' => [$middleware]], function (Router $router) {
            $router->get('/', [SampleController::class, 'ok']);
        });
        $router->dispatch();

        $this->assertEquals('OK', $this->output($router));
        $this->assertContains($middleware->content, SampleMiddleware::$output);
    }

    /**
     * @throws Throwable
     */
    public function test_nested_groups_with_middleware()
    {
        $group1Middleware = new SampleMiddleware(mt_rand(1, 9999999));
        $group2Middleware = new SampleMiddleware(mt_rand(1, 9999999));

        $router = $this->router();
        $router->group(['middleware' => [$group1Middleware]], function (Router $router) use ($group2Middleware) {
            $router->group(['middleware' => [$group2Middleware]], function (Router $router) {
                $router->get('/', [SampleController::class, 'ok']);
            });
        });
        $router->dispatch();

        $this->assertEquals('OK', $this->output($router));
        $this->assertContains($group1Middleware->content, SampleMiddleware::$output);
        $this->assertContains($group2Middleware->content, SampleMiddleware::$output);
    }

    /**
     * @throws Throwable
     */
    public function test_with_a_prefix()
    {
        $this->mockRequest('GET', 'http://example.com/group/page');

        $router = $this->router();
        $router->group(['prefix' => '/group'], function (Router $router) {
            $router->get('/page', [SampleController::class, 'ok']);
        });
        $router->dispatch();

        $this->assertEquals('OK', $this->output($router));
    }

    /**
     * @throws Throwable
     */
    public function test_nested_groups_with_prefix()
    {
        $this->mockRequest('GET', 'http://example.com/group1/group2/page');

        $router = $this->router();
        $router->group(['prefix' => '/group1'], function (Router $router) {
            $router->group(['prefix' => '/group2'], function (Router $router) {
                $router->get('/page', [SampleController::class, 'ok']);
            });
        });
        $router->dispatch();

        $this->assertEquals('OK', $this->output($router));
    }

    /**
     * @throws Throwable
     */
    public function test_with_domain()
    {
        $this->mockRequest('GET', 'http://sub.domain.tld/');

        $router = $this->router();
        $router->group(['domain' => 'sub.domain.tld'], function (Router $router) {
            $router->get('/', [SampleController::class, 'ok']);
        });
        $router->dispatch();

        $this->assertEquals('OK', $this->output($router));
    }

    /**
     * @throws Throwable
     */
    public function test_nested_groups_with_domain_it_should_consider_the_inner_group_domain()
    {
        $this->mockRequest('GET', 'http://sub2.domain.com/');

        $router = $this->router();
        $router->group(['domain' => 'sub1.domain.com'], function (Router $router) {
            $router->group(['domain' => 'sub2.domain.com'], function (Router $router) {
                $router->get('/', [SampleController::class, 'ok']);
            });
        });
        $router->dispatch();

        $this->assertEquals('OK', $this->output($router));
    }
}
