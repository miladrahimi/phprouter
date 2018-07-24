<?php
/**
 * Created by PhpStorm.
 * User: Milad Rahimi <info@miladrahimi.com>
 * Date: 6/23/2018 AD
 * Time: 01:58
 */

namespace MiladRahimi\PhpRouter\Tests;

use MiladRahimi\PhpRouter\Enums\HttpMethods;
use MiladRahimi\PhpRouter\Exceptions\InvalidControllerException;
use MiladRahimi\PhpRouter\Exceptions\InvalidMiddlewareException;
use MiladRahimi\PhpRouter\Exceptions\RouteNotFoundException;
use MiladRahimi\PhpRouter\Tests\Classes\SampleController;
use MiladRahimi\PhpRouter\Tests\Classes\SampleMiddleware;
use MiladRahimi\PhpRouter\Tests\Classes\StopperMiddleware;
use Throwable;
use Zend\Diactoros\ServerRequestFactory;

class RoutingTest extends TestCase
{
    /**
     * @throws Throwable
     */
    public function test_simple_routing()
    {
        $router = $this->createRouter();
        $router->map('GET', '/', $this->simpleController());
        $router->dispatch();

        $this->assertEquals('Here I am!', $this->getOutput($router));
    }

    /**
     * @throws Throwable
     */
    public function test_specific_methods()
    {
        $this->mockRequest(HttpMethods::GET, 'http://example.com/');

        $router = $this->createRouter();
        $router->get('/', $this->simpleController());
        $router->dispatch();

        $this->assertEquals('Here I am!', $this->getOutput($router));

        $this->mockRequest(HttpMethods::POST, 'http://example.com/');

        $router = $this->createRouter();
        $router->post('/', $this->simpleController());
        $router->dispatch();

        $this->assertEquals('Here I am!', $this->getOutput($router));

        $this->mockRequest(HttpMethods::PATCH, 'http://example.com/');

        $router = $this->createRouter();
        $router->patch('/', $this->simpleController());
        $router->dispatch();

        $this->assertEquals('Here I am!', $this->getOutput($router));

        $this->mockRequest(HttpMethods::PUT, 'http://example.com/');

        $router = $this->createRouter();
        $router->put('/', $this->simpleController());
        $router->dispatch();

        $this->assertEquals('Here I am!', $this->getOutput($router));

        $this->mockRequest(HttpMethods::DELETE, 'http://example.com/');

        $router = $this->createRouter();
        $router->delete('/', $this->simpleController());
        $router->dispatch();

        $this->assertEquals('Here I am!', $this->getOutput($router));
    }

    /**
     * @throws Throwable
     */
    public function test_any_method()
    {
        $this->mockRequest(HttpMethods::GET, 'http://example.com/');

        $router = $this->createRouter();
        $router->any('/', $this->simpleController());
        $router->dispatch();

        $this->assertEquals('Here I am!', $this->getOutput($router));

        $this->mockRequest(HttpMethods::POST, 'http://example.com/');

        $router = $this->createRouter();
        $router->any('/', $this->simpleController());
        $router->dispatch();

        $this->assertEquals('Here I am!', $this->getOutput($router));
    }

    /**
     * @throws Throwable
     */
    public function test_multiple_routes()
    {
        $router = $this->createRouter();
        $router->map('GET', '/', SampleController::class . '@getNoParameter');
        $router->map('POST', '/{id}', SampleController::class . '@postOneParameter');
        $router->dispatch();

        $this->assertEquals('Here I am!', $this->getOutput($router));

        $this->mockRequest(HttpMethods::POST, 'http://example.com/666');

        // Reset the server request to consider the new request
        $router->setServerRequest(ServerRequestFactory::fromGlobals());

        $router->dispatch();

        $this->assertEquals('The id is 666', $this->getOutput($router));
    }

    /**
     * @throws Throwable
     */
    public function test_single_middleware()
    {
        $middleware = new SampleMiddleware(13);

        $router = $this->createRouter();
        $router->map('GET', '/', $this->simpleController(), $middleware);
        $router->dispatch();

        $this->assertEquals('Here I am!', $this->getOutput($router));
        $this->assertContains(13, SampleMiddleware::$output);
    }

    /**
     * @throws Throwable
     */
    public function test_stopper_middleware()
    {
        $middleware = new StopperMiddleware(11);

        $router = $this->createRouter();
        $router->map('GET', '/', $this->simpleController(), $middleware);
        $router->dispatch();

        $this->assertEquals('Stopped in middleware.', $this->getOutput($router));
        $this->assertContains(11, StopperMiddleware::$output);
    }

    /**
     * @throws Throwable
     */
    public function test_multiple_middleware()
    {
        $middleware = [new SampleMiddleware(32), new SampleMiddleware(64)];

        $router = $this->createRouter();
        $router->map('GET', '/', $this->simpleController(), $middleware);
        $router->dispatch();

        $this->assertEquals('Here I am!', $this->getOutput($router));
        $this->assertContains(32, SampleMiddleware::$output);
        $this->assertContains(64, SampleMiddleware::$output);
    }

    /**
     * @throws Throwable
     */
    public function test_static_domain()
    {
        $this->mockRequest(HttpMethods::GET, 'http://server.domain.ext/');

        $router = $this->createRouter();
        $router->map('GET', '/', $this->simpleController(), [], 'server.domain.ext');
        $router->dispatch();

        $this->assertEquals('Here I am!', $this->getOutput($router));
    }

    /**
     * @throws Throwable
     */
    public function test_regex_domain()
    {
        $this->mockRequest(HttpMethods::GET, 'http://something.domain.ext/');

        $router = $this->createRouter();
        $router->map('GET', '/', $this->simpleController(), [], '(.*).domain.ext');
        $router->dispatch();

        $this->assertEquals('Here I am!', $this->getOutput($router));
    }

    /**
     * @throws Throwable
     */
    public function test_named_route()
    {
        $router = $this->createRouter();
        $router->map('GET', '/', $this->simpleController(), [], null, 'TheName');
        $router->dispatch();

        $this->assertEquals('Here I am!', $this->getOutput($router));
        $this->assertTrue($router->isRoute('TheName'));
    }

    /**
     * @throws Throwable
     */
    public function test_use_name_method()
    {
        $router = $this->createRouter();
        $router->useName('TheName')->map('GET', '/', $this->simpleController());
        $router->dispatch();

        $this->assertEquals('Here I am!', $this->getOutput($router));
        $this->assertTrue($router->isRoute('TheName'));

        $controller = SampleController::class . '@postOneParameter';

        $this->mockRequest(HttpMethods::POST, 'http://example.com/666');

        $router = $this->createRouter();
        $router->map('POST', '/{id}', $controller);
        $router->dispatch();

        $this->assertEquals('The id is 666', $this->getOutput($router));
        $this->assertFalse($router->isRoute('TheName'));
    }

    /**
     * @throws Throwable
     */
    public function test_defined_parameters()
    {
        $this->mockRequest(HttpMethods::GET, 'http://example.com/666');

        $router = $this->createRouter();
        $router->defineParameter('id', '[0-9]+');
        $router->map('GET', '/{id}', $this->simpleController());
        $router->dispatch();

        $this->assertEquals('Here I am!', $this->getOutput($router));

        $this->mockRequest(HttpMethods::GET, 'http://example.com/abc');

        $this->expectException(RouteNotFoundException::class);

        $router = $this->createRouter();
        $router->defineParameter('id', '[0-9]+');
        $router->map('GET', '/{id}', $this->simpleController());
        $router->dispatch();
    }

    /**
     * @throws Throwable
     */
    public function test_routing_raise_an_error_when_route_is_not_found()
    {
        $this->mockRequest(HttpMethods::GET, 'http://example.com/unknowon-page');

        $this->expectException(RouteNotFoundException::class);

        $router = $this->createRouter();
        $router->map('GET', '/', $this->simpleController());
        $router->dispatch();
    }

    /**
     * @throws Throwable
     */
    public function test_routing_it_should_raise_an_error_when_controller_class_is_invalid()
    {
        $this->expectException(InvalidControllerException::class);
        $this->expectExceptionMessage('Controller class `UnknownController@method` not found.');

        $router = $this->createRouter();
        $router->map('GET', '/', 'UnknownController@method');
        $router->dispatch();
    }

    /**
     * @throws Throwable
     */
    public function test_routing_it_should_raise_an_error_when_middleware_is_invalid()
    {
        $this->expectException(InvalidMiddlewareException::class);

        $router = $this->createRouter();
        $router->map('GET', '/', $this->simpleController(), 'UnknownMiddleware');
        $router->dispatch();
    }
}
