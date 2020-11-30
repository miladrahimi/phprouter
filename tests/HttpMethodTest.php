<?php

namespace MiladRahimi\PhpRouter\Tests;

use MiladRahimi\PhpRouter\Exceptions\RouteNotFoundException;
use MiladRahimi\PhpRouter\Tests\Testing\SampleController;
use Throwable;

class HttpMethodTest extends TestCase
{
    /**
     * @throws Throwable
     */
    public function test_a_get_route()
    {
        $this->mockRequest('GET', 'http://example.com/');

        $router = $this->router();
        $router->get('/', [SampleController::class, 'home']);
        $router->dispatch();

        $this->assertEquals('Home', $this->output($router));
    }

    /**
     * @throws Throwable
     */
    public function test_a_post_route()
    {
        $this->mockRequest('POST', 'http://example.com/');

        $router = $this->router();
        $router->post('/', [SampleController::class, 'home']);
        $router->dispatch();

        $this->assertEquals('Home', $this->output($router));
    }

    /**
     * @throws Throwable
     */
    public function test_a_put_route()
    {
        $this->mockRequest('PUT', 'http://example.com/');

        $router = $this->router();
        $router->put('/', [SampleController::class, 'home']);
        $router->dispatch();

        $this->assertEquals('Home', $this->output($router));
    }

    /**
     * @throws Throwable
     */
    public function test_a_patch_route()
    {
        $this->mockRequest('PATCH', 'http://example.com/');

        $router = $this->router();
        $router->patch('/', [SampleController::class, 'home']);
        $router->dispatch();

        $this->assertEquals('Home', $this->output($router));
    }

    /**
     * @throws Throwable
     */
    public function test_a_delete_route()
    {
        $this->mockRequest('DELETE', 'http://example.com/');

        $router = $this->router();
        $router->delete('/', [SampleController::class, 'home']);
        $router->dispatch();

        $this->assertEquals('Home', $this->output($router));
    }

    /**
     * @throws Throwable
     */
    public function test_an_options_route()
    {
        $this->mockRequest('OPTIONS', 'http://example.com/');

        $router = $this->router();
        $router->options('/', [SampleController::class, 'home']);
        $router->dispatch();

        $this->assertEquals('Home', $this->output($router));
    }

    /**
     * @throws Throwable
     */
    public function test_map_a_post_route()
    {
        $this->mockRequest('POST', 'http://example.com/');

        $router = $this->router();
        $router->map('POST', '/', [SampleController::class, 'home']);
        $router->dispatch();

        $this->assertEquals('Home', $this->output($router));
    }

    /**
     * @throws Throwable
     */
    public function test_map_a_custom_method()
    {
        $this->mockRequest('CUSTOM', 'http://example.com/');

        $router = $this->router();
        $router->map('CUSTOM', '/', [SampleController::class, 'home']);
        $router->dispatch();

        $this->assertEquals('Home', $this->output($router));
    }

    /**
     * @throws Throwable
     */
    public function test_match_multiple_routes()
    {
        $router = $this->router();
        $router->match(['PUT', 'PATCH'], '/', [SampleController::class, 'home']);

        $this->mockRequest('PUT', 'http://example.com/');
        $router->dispatch();
        $this->assertEquals('Home', $this->output($router));

        $this->mockRequest('PATCH', 'http://example.com/');
        $router->dispatch();
        $this->assertEquals('Home', $this->output($router));
    }

    /**
     * @throws Throwable
     */
    public function test_match_no_route()
    {
        $this->mockRequest('GET', 'http://example.com/');

        $router = $this->router();
        $router->match([], '/', [SampleController::class, 'home']);

        $this->expectException(RouteNotFoundException::class);
        $router->dispatch();
    }

    /**
     * @throws Throwable
     */
    public function test_any_with_some_methods()
    {
        $router = $this->router();
        $router->any('/', [SampleController::class, 'home']);

        $this->mockRequest('GET', 'http://example.com/');
        $router->dispatch();
        $this->assertEquals('Home', $this->output($router));

        $this->mockRequest('POST', 'http://example.com/');
        $router->dispatch();
        $this->assertEquals('Home', $this->output($router));
    }
}
