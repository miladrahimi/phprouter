<?php

namespace MiladRahimi\PhpRouter\Tests;

use Closure;
use MiladRahimi\PhpRouter\Exceptions\InvalidMiddlewareException;
use MiladRahimi\PhpRouter\Tests\Testing\SampleMiddleware;
use MiladRahimi\PhpRouter\Tests\Testing\StopperMiddleware;
use Throwable;
use Zend\Diactoros\ServerRequest;

class MiddlewareTest extends TestCase
{
    /**
     * @throws Throwable
     */
    public function test_with_a_single_middleware_as_an_object()
    {
        $middleware = new SampleMiddleware(666);

        $router = $this->router()
            ->get('/', $this->OkController(), $middleware)
            ->dispatch();

        $this->assertEquals('OK', $this->output($router));
        $this->assertContains($middleware->content, SampleMiddleware::$output);
    }

    /**
     * @throws Throwable
     */
    public function test_with_a_single_middleware_as_a_string()
    {
        $middleware = SampleMiddleware::class;

        $router = $this->router()
            ->get('/', $this->OkController(), $middleware)
            ->dispatch();

        $this->assertEquals('OK', $this->output($router));
        $this->assertEquals('empty', SampleMiddleware::$output[0]);
    }

    /**
     * @throws Throwable
     */
    public function test_with_a_single_middleware_as_a_closure()
    {
        $middleware = function (ServerRequest $request, Closure $next) {
            return $next($request->withAttribute('Middleware', 666));
        };

        $router = $this->router()
            ->get('/', function (ServerRequest $request) {
                return $request->getAttribute('Middleware');
            }, $middleware)
            ->dispatch();

        $this->assertEquals('666', $this->output($router));
    }

    /**
     * @throws Throwable
     */
    public function test_with_a_stopper_middleware()
    {
        $middleware = new StopperMiddleware(666);

        $router = $this->router()
            ->get('/', $this->OkController(), $middleware)
            ->dispatch();

        $this->assertEquals('Stopped in middleware.', $this->output($router));
        $this->assertContains($middleware->content, StopperMiddleware::$output);
    }

    /**
     * @throws Throwable
     */
    public function test_with_multiple_middleware()
    {
        $middleware = [
            function (ServerRequest $request, $next) {
                $request = $request->withAttribute('a', 'It');
                return $next($request);
            },
            function (ServerRequest $request, $next) {
                $request = $request->withAttribute('b', 'works!');
                return $next($request);
            },
        ];

        $router = $this->router()
            ->get('/', function (ServerRequest $request) {
                return $request->getAttribute('a') . ' ' . $request->getAttribute('b');
            }, $middleware)
            ->dispatch();

        $this->assertEquals('It works!', $this->output($router));
    }

    /**
     * @throws Throwable
     */
    public function test_with_invalid_middleware()
    {
        $this->expectException(InvalidMiddlewareException::class);

        $this->router()
            ->get('/', $this->OkController(), 'UnknownMiddleware')
            ->dispatch();
    }
}
