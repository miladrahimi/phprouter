<?php

namespace MiladRahimi\PhpRouter\Tests;

use Closure;
use MiladRahimi\PhpRouter\Exceptions\InvalidMiddlewareException;
use MiladRahimi\PhpRouter\Tests\Classes\SampleMiddleware;
use MiladRahimi\PhpRouter\Tests\Classes\StopperMiddleware;
use Throwable;
use Zend\Diactoros\ServerRequest;

/**
 * Class MiddlewareTest
 *
 * @package MiladRahimi\PhpRouter\Tests
 */
class MiddlewareTest extends TestCase
{
    /**
     * @throws Throwable
     */
    public function test_with_a_single_middleware_in_an_object()
    {
        $middleware = new SampleMiddleware(mt_rand(1, 9999999));

        $router = $this->router()
            ->get('/', $this->controller(), $middleware)
            ->dispatch();

        $this->assertEquals('OK', $this->extract($router));
        $this->assertContains($middleware->content, SampleMiddleware::$output);
    }

    /**
     * @throws Throwable
     */
    public function test_with_a_single_middleware_in_a_string()
    {
        $middleware = SampleMiddleware::class;

        $router = $this->router()
            ->get('/', $this->controller(), $middleware)
            ->dispatch();

        $this->assertEquals('OK', $this->extract($router));
        $this->assertCount(1, SampleMiddleware::$output);
    }

    /**
     * @throws Throwable
     */
    public function test_with_a_single_middleware_in_a_closure()
    {
        $middleware = function (ServerRequest $request, Closure $next) {
            $request = $request->withAttribute('Middleware', 666);

            return $next($request);
        };

        $router = $this->router()
            ->get('/', function (ServerRequest $request) {
                return $request->getAttribute('Middleware');
            }, $middleware)
            ->dispatch();

        $this->assertEquals('666', $this->extract($router));
    }

    /**
     * @throws Throwable
     */
    public function test_with_a_stopper_middleware()
    {
        $middleware = new StopperMiddleware(mt_rand(1, 9999999));

        $router = $this->router()
            ->get('/', $this->controller(), $middleware)
            ->dispatch();

        $this->assertEquals('Stopped in middleware.', $this->extract($router));
        $this->assertContains($middleware->content, StopperMiddleware::$output);
    }

    /**
     * @throws Throwable
     */
    public function test_with_multiple_middleware()
    {
        $middleware = [
            function (ServerRequest $request, $next) {
                $request = $request->withAttribute('t1', microtime(true));
                return $next($request);
            },
            function (ServerRequest $request, $next) {
                $request = $request->withAttribute('t2', microtime(true));
                return $next($request);
            },
        ];

        $router = $this->router()
            ->get('/', function (ServerRequest $request) {
                return $request->getAttribute('t2') - $request->getAttribute('t1');
            }, $middleware)
            ->dispatch();

        $this->assertGreaterThan(0, $this->extract($router));
    }

    /**
     * @throws Throwable
     */
    public function test_with_invalid_middleware()
    {
        $this->expectException(InvalidMiddlewareException::class);

        $this->router()
            ->get('/', $this->controller(), 'UnknownMiddleware')
            ->dispatch();
    }
}
