<?php
/**
 * Created by PhpStorm.
 * User: Milad Rahimi <info@miladrahimi.com>
 * Date: 6/23/2018 AD
 * Time: 01:58
 */

namespace MiladRahimi\Router\Tests;

use MiladRahimi\Router\Enums\HttpMethods;
use MiladRahimi\Router\Exceptions\InvalidControllerException;
use MiladRahimi\Router\Exceptions\InvalidMiddlewareException;
use MiladRahimi\Router\Exceptions\RouteNotFoundException;
use MiladRahimi\Router\Tests\Classes\SampleController;
use MiladRahimi\Router\Tests\Classes\SampleMiddleware;
use MiladRahimi\Router\Tests\Classes\StopperMiddleware;
use Throwable;
use Zend\Diactoros\ServerRequestFactory;

class SimpleMappingTest extends TestCase
{
    /**
     * @throws Throwable
     */
    public function test_simple_routing()
    {
        $router = $this->createRouterWithMockedProperties();
        $router->map('GET', '/', $this->simpleController());
        $router->dispatch();

        $this->assertEquals('Here I am!', ob_get_contents());
    }

    /**
     * @throws Throwable
     */
    public function test_multiple_routing()
    {
        $router = $this->createRouterWithMockedProperties();
        $router->map('GET', '/', SampleController::class . '@getNoParameter');
        $router->map('POST', '/{id}', SampleController::class . '@postOneParameter');
        $router->dispatch();

        $this->assertEquals('Here I am!', ob_get_contents());

        ob_clean();

        $this->mockRequest(HttpMethods::POST, 'http://example.com/666');

        $router->setServerRequest(ServerRequestFactory::fromGlobals());
        $router->dispatch();

        $this->assertEquals('The id is 666', ob_get_contents());
    }

    /**
     * @throws Throwable
     */
    public function test_simple_routing_and_a_single_middleware()
    {
        $middleware = new SampleMiddleware(13);

        $router = $this->createRouterWithMockedProperties();
        $router->map('GET', '/', $this->simpleController(), $middleware);
        $router->dispatch();

        $this->assertEquals('Here I am!', ob_get_contents());
        $this->assertContains(13, SampleMiddleware::$output);
    }

    /**
     * @throws Throwable
     */
    public function test_simple_routing_and_a_single_stopper_middleware()
    {
        $middleware = new StopperMiddleware(11);

        $router = $this->createRouterWithMockedProperties();
        $router->map('GET', '/', $this->simpleController(), $middleware);
        $router->dispatch();

        $this->assertEquals('Stopped in middleware.', ob_get_contents());
        $this->assertContains(11, StopperMiddleware::$output);
    }

    /**
     * @throws Throwable
     */
    public function test_simple_routing_and_some_middleware()
    {
        $middleware = [new SampleMiddleware(32), new SampleMiddleware(64)];

        $router = $this->createRouterWithMockedProperties();
        $router->map('GET', '/', $this->simpleController(), $middleware);
        $router->dispatch();

        $this->assertEquals('Here I am!', ob_get_contents());
        $this->assertContains(32, SampleMiddleware::$output);
        $this->assertContains(64, SampleMiddleware::$output);
    }

    /**
     * @throws Throwable
     */
    public function test_simple_routing_with_static_domain()
    {
        $this->mockRequest(HttpMethods::GET, 'http://server.domain.ext/');

        $router = $this->createRouterWithMockedProperties();
        $router->map('GET', '/', $this->simpleController(), [], 'server.domain.ext');
        $router->dispatch();

        $this->assertEquals('Here I am!', ob_get_contents());
    }

    /**
     * @throws Throwable
     */
    public function test_simple_routing_with_regex_domain()
    {
        $this->mockRequest(HttpMethods::GET, 'http://something.domain.ext/');

        $router = $this->createRouterWithMockedProperties();
        $router->map('GET', '/', $this->simpleController(), [], '(.*).domain.ext');
        $router->dispatch();

        $this->assertEquals('Here I am!', ob_get_contents());
    }

    /**
     * @throws Throwable
     */
    public function test_simple_routing_with_name_parameter()
    {
        $router = $this->createRouterWithMockedProperties();
        $router->map('GET', '/', $this->simpleController(), [], null, 'TheName');
        $router->dispatch();

        $this->assertEquals('Here I am!', ob_get_contents());
        $this->assertTrue($router->isRoute('TheName'));
    }

    /**
     * @throws Throwable
     */
    public function test_simple_routing_with_use_name_method_and_check_being_disposal()
    {
        $router = $this->createRouterWithMockedProperties();
        $router->useName('TheName')->map('GET', '/', $this->simpleController());
        $router->dispatch();

        $this->assertEquals('Here I am!', ob_get_contents());
        $this->assertTrue($router->isRoute('TheName'));

        ob_clean();

        $controller = SampleController::class . '@postOneParameter';

        $this->mockRequest(HttpMethods::POST, 'http://example.com/666');

        $router = $this->createRouterWithMockedProperties();
        $router->map('POST', '/{id}', $controller);
        $router->dispatch();

        $this->assertEquals('The id is 666', ob_get_contents());
        $this->assertFalse($router->isRoute('TheName'));
    }

    /**
     * @throws Throwable
     */
    public function test_simple_routing_it_should_raise_an_error_when_route_is_not_found()
    {
        $this->mockRequest(HttpMethods::GET, 'http://example.com/unknowon-page');

        $this->expectException(RouteNotFoundException::class);

        $router = $this->createRouterWithMockedProperties();
        $router->map('GET', '/', $this->simpleController());
        $router->dispatch();
    }

    /**
     * @throws Throwable
     */
    public function test_simple_routing_it_should_raise_an_error_when_controller_class_is_invalid()
    {
        $this->expectException(InvalidControllerException::class);
        $this->expectExceptionMessage('Controller class `UnknownController@method` not found.');

        $router = $this->createRouterWithMockedProperties();
        $router->map('GET', '/', 'UnknownController@method');
        $router->dispatch();
    }

    /**
     * @throws Throwable
     */
    public function test_simple_routing_it_should_raise_an_error_when_middleware_is_invalid()
    {
        $this->expectException(InvalidMiddlewareException::class);

        $router = $this->createRouterWithMockedProperties();
        $router->map('GET', '/', $this->simpleController(), 'UnknownMiddleware');
        $router->dispatch();
    }
}
