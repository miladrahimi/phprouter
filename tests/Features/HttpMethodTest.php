<?php

namespace MiladRahimi\PhpRouter\Tests\Features;

use Laminas\Diactoros\ServerRequest;
use MiladRahimi\PhpRouter\Tests\Common\SampleController;
use MiladRahimi\PhpRouter\Tests\TestCase;
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
    public function test_map_a_post_route()
    {
        $this->mockRequest('POST', 'http://example.com/');

        $router = $this->router();
        $router->define('POST', '/', [SampleController::class, 'home']);
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
        $router->define('CUSTOM', '/', [SampleController::class, 'home']);
        $router->dispatch();

        $this->assertEquals('Home', $this->output($router));
    }

    /**
     * @throws Throwable
     */
    public function test_any_with_some_methods()
    {
        $router = $this->router();
        $router->any('/', function (ServerRequest $request) {
            return $request->getMethod();
        });

        $this->mockRequest('GET', 'http://example.com/');
        $router->dispatch();
        $this->assertEquals('GET', $this->output($router));

        $this->mockRequest('POST', 'http://example.com/');
        $router->dispatch();
        $this->assertEquals('POST', $this->output($router));
    }
}
