<?php

namespace MiladRahimi\PhpRouter\Tests\Features;

use MiladRahimi\PhpRouter\Exceptions\InvalidCallableException;
use MiladRahimi\PhpRouter\Router;
use MiladRahimi\PhpRouter\Tests\Common\SampleController;
use MiladRahimi\PhpRouter\Tests\Common\SampleMiddleware;
use MiladRahimi\PhpRouter\Tests\Common\StopperMiddleware;
use MiladRahimi\PhpRouter\Tests\TestCase;
use Throwable;

class MiddlewareTest extends TestCase
{
    /**
     * @throws Throwable
     */
    public function test_with_a_single_middleware_as_an_object()
    {
        $middleware = new SampleMiddleware(666);

        $router = $this->router();
        $router->group(['middleware' => [$middleware]], function (Router $r) {
            $r->get('/', [SampleController::class, 'ok']);
        });
        $router->dispatch();

        $this->assertEquals('OK', $this->output($router));
        $this->assertContains($middleware->content, SampleMiddleware::$output);
    }

    /**
     * @throws Throwable
     */
    public function test_with_a_single_middleware_as_a_string()
    {
        $middleware = SampleMiddleware::class;

        $router = $this->router();
        $router->group(['middleware' => [$middleware]], function (Router $r) {
            $r->get('/', [SampleController::class, 'ok']);
        });
        $router->dispatch();

        $this->assertEquals('OK', $this->output($router));
        $this->assertEquals('empty', SampleMiddleware::$output[0]);
    }

    /**
     * @throws Throwable
     */
    public function test_with_a_stopper_middleware()
    {
        $middleware = new StopperMiddleware(666);

        $router = $this->router();
        $router->group(['middleware' => [$middleware]], function (Router $r) {
            $r->get('/', [SampleController::class, 'ok']);
        });
        $router->dispatch();

        $this->assertEquals('Stopped in middleware.', $this->output($router));
        $this->assertContains($middleware->content, StopperMiddleware::$output);
    }

    /**
     * @throws Throwable
     */
    public function test_with_invalid_middleware()
    {
        $this->expectException(InvalidCallableException::class);

        $router = $this->router();
        $router->group(['middleware' => ['UnknownMiddleware']], function (Router $r) {
            $r->get('/', [SampleController::class, 'ok']);
        });
        $router->dispatch();
    }
}
