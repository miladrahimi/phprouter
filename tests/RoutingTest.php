<?php

namespace MiladRahimi\PhpRouter\Tests;

use MiladRahimi\PhpRouter\Enums\HttpMethods;
use MiladRahimi\PhpRouter\Exceptions\InvalidControllerException;
use MiladRahimi\PhpRouter\Exceptions\RouteNotFoundException;
use MiladRahimi\PhpRouter\Router;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;
use Zend\Diactoros\ServerRequest;

/**
 * Class RoutingTest
 *
 * @package MiladRahimi\PhpRouter\Tests
 */
class RoutingTest extends TestCase
{
    /**
     * @throws Throwable
     */
    public function test_a_simple_get_route()
    {
        $this->mockRequest(HttpMethods::GET, 'http://example.com/');

        $router = $this->router();
        $router->map('GET', '/', $this->controller());
        $router->dispatch();

        $this->assertEquals('OK', $this->outputOf($router));
    }

    /**
     * @throws Throwable
     */
    public function test_a_simple_post_route()
    {
        $this->mockRequest(HttpMethods::POST, 'http://example.com/');

        $router = $this->router()->post('/', $this->controller())->dispatch();

        $this->assertEquals('OK', $this->outputOf($router));
    }

    /**
     * @throws Throwable
     */
    public function test_a_simple_put_route()
    {
        $this->mockRequest(HttpMethods::PUT, 'http://example.com/');

        $router = $this->router()->put('/', $this->controller())->dispatch();

        $this->assertEquals('OK', $this->outputOf($router));
    }

    /**
     * @throws Throwable
     */
    public function test_a_simple_patch_route()
    {
        $this->mockRequest(HttpMethods::PATCH, 'http://example.com/');

        $router = $this->router()->patch('/', $this->controller())->dispatch();

        $this->assertEquals('OK', $this->outputOf($router));
    }

    /**
     * @throws Throwable
     */
    public function test_a_simple_delete_route()
    {
        $this->mockRequest(HttpMethods::DELETE, 'http://example.com/');

        $router = $this->router()->delete('/', $this->controller())->dispatch();

        $this->assertEquals('OK', $this->outputOf($router));
    }

    /**
     * @throws Throwable
     */
    public function test_a_route_with_custom_method()
    {
        $method = "SCREW";

        $this->mockRequest($method, 'http://example.com/');

        $router = $this->router()->map($method, '/', $this->controller())->dispatch();

        $this->assertEquals('OK', $this->outputOf($router));
    }

    /**
     * @throws Throwable
     */
    public function test_the_any_method()
    {
        $this->mockRequest(HttpMethods::GET, 'http://example.com/');

        $router = $this->router()
            ->any('/', function () {
                return 'Test any-get method';
            })
            ->dispatch();

        $this->assertEquals('Test any-get method', $this->outputOf($router));

        $this->mockRequest(HttpMethods::POST, 'http://example.com/');

        $router = $this->router()
            ->any('/', function () {
                return 'Test any-post method';
            })
            ->dispatch();

        $this->assertEquals('Test any-post method', $this->outputOf($router));
    }

    /**
     * @throws Throwable
     */
    public function test_multiple_routes()
    {
        $this->mockRequest(HttpMethods::POST, 'http://example.com/666');

        $router = $this->router()
            ->get('/', function () {
                return 'Home';
            })
            ->post('/{id}', function ($id) {
                return $id;
            })
            ->dispatch();

        $this->assertEquals('666', $this->outputOf($router));
    }

    /**
     * @throws Throwable
     */
    public function test_duplicate_routes_with_different_controllers()
    {
        $router = $this->router()
            ->get('/', function () {
                return 'Home';
            })
            ->get('/', function () {
                return 'Home again!';
            })
            ->dispatch();

        $this->assertEquals('Home again!', $this->outputOf($router));
    }

    /**
     * @throws Throwable
     */
    public function test_multiple_http_methods()
    {
        $this->mockRequest(HttpMethods::POST, 'http://example.com/');

        $router = $this->router()
            ->get('/', function () {
                return 'Get';
            })
            ->post('/', function () {
                return 'Post';
            })
            ->delete('/', function () {
                return 'Delete';
            })
            ->dispatch();

        $this->assertEquals('Post', $this->outputOf($router));
    }

    /**
     * @throws Throwable
     */
    public function test_initial_prefix()
    {
        $this->mockRequest(HttpMethods::POST, 'http://example.com/app/page');

        $router = $this->router('/app')
            ->post('/page', $this->controller())
            ->dispatch();

        $this->assertEquals('OK', $this->outputOf($router));
    }

    /**
     * @throws Throwable
     */
    public function test_with_a_required_parameter()
    {
        $this->mockRequest(HttpMethods::GET, 'http://web.com/666');

        $router = $this->router()
            ->get('/{id}', function ($id) {
                return $id;
            })
            ->dispatch();

        $this->assertEquals('666', $this->outputOf($router));
    }

    /**
     * @throws Throwable
     */
    public function test_with_a_optional_parameter_when_it_is_present()
    {
        $this->mockRequest(HttpMethods::GET, 'http://web.com/666');

        $router = $this->router()
            ->get('/{id?}', function ($id) {
                return $id;
            })
            ->dispatch();

        $this->assertEquals('666', $this->outputOf($router));
    }

    /**
     * @throws Throwable
     */
    public function test_with_a_optional_parameter_when_it_is_not_present()
    {
        $this->mockRequest(HttpMethods::GET, 'http://web.com/');

        $router = $this->router()
            ->get('/{id?}', function ($id) {
                return $id ?: 'Default';
            })
            ->dispatch();

        $this->assertEquals('Default', $this->outputOf($router));
    }

    /**
     * @throws Throwable
     */
    public function test_with_a_static_domain()
    {
        $this->mockRequest(HttpMethods::GET, 'http://server.domain.ext/');

        $router = $this->router()
            ->get('/', $this->controller(), [], 'server.domain.ext')
            ->dispatch();

        $this->assertEquals('OK', $this->outputOf($router));
    }

    /**
     * @throws Throwable
     */
    public function test_with_a_domain_pattern()
    {
        $this->mockRequest(HttpMethods::GET, 'http://something.domain.ext/');

        $router = $this->router()
            ->get('/', $this->controller(), [], '(.*).domain.ext')
            ->dispatch();

        $this->assertEquals('OK', $this->outputOf($router));
    }

    /**
     * @throws Throwable
     */
    public function test_with_defined_parameters()
    {
        $this->mockRequest(HttpMethods::GET, 'http://example.com/666');

        $router = $this->router()
            ->define('id', '[0-9]+')
            ->get('/{id}', $this->controller())
            ->dispatch();

        $this->assertEquals('OK', $this->outputOf($router));

        $this->mockRequest(HttpMethods::GET, 'http://example.com/abc');

        $this->expectException(RouteNotFoundException::class);

        $this->router()
            ->define('id', '[0-9]+')
            ->get('/{id}', $this->controller())
            ->dispatch();
    }

    /**
     * @throws Throwable
     */
    public function test_injection_of_request_by_name()
    {
        $router = $this->router()
            ->get('/', function ($request) {
                /** @var ServerRequest $request */
                return $request->getMethod();
            })
            ->dispatch();

        $this->assertEquals('GET', $this->outputOf($router));
    }

    /**
     * @throws Throwable
     */
    public function test_injection_of_request_by_interface()
    {
        $router = $this->router()
            ->get('/', function (ServerRequestInterface $r) {
                return $r->getMethod();
            })
            ->dispatch();

        $this->assertEquals('GET', $this->outputOf($router));
    }

    /**
     * @throws Throwable
     */
    public function test_injection_of_request_by_type()
    {
        $router = $this->router()
            ->get('/', function (ServerRequest $r) {
                return $r->getMethod();
            })
            ->dispatch();

        $this->assertEquals('GET', $this->outputOf($router));
    }

    /**
     * @throws Throwable
     */
    public function test_injection_of_router_by_name()
    {
        $router = $this->router()
            ->name('home')
            ->get('/', function ($router) {
                /** @var Router $router */
                return $router->currentRoute()->getName();
            })
            ->dispatch();

        $this->assertEquals('home', $this->outputOf($router));
    }

    /**
     * @throws Throwable
     */
    public function test_injection_of_router_by_type()
    {
        $router = $this->router()
            ->name('home')
            ->get('/', function (Router $r) {
                return $r->currentRoute()->getName();
            })
            ->dispatch();

        $this->assertEquals('home', $this->outputOf($router));
    }

    /**
     * @throws Throwable
     */
    public function test_injection_of_default_value()
    {
        $router = $this->router()
            ->get('/', function ($default = "Default") {
                return $default;
            })
            ->dispatch();

        $this->assertEquals('Default', $this->outputOf($router));
    }

    /**
     * @throws Throwable
     */
    public function test_set_and_get_request()
    {
        $router = $this->router();

        $router->get('/', function () use ($router) {
            $newRequest = $router->getRequest()->withMethod('CUSTOM');
            $router->setRequest($newRequest);

            return $router->getRequest()->getMethod();
        })->dispatch();

        $this->assertEquals('CUSTOM', $this->outputOf($router));
    }

    /**
     * @throws Throwable
     */
    public function test_default_publisher()
    {
        ob_start();

        $router = new Router();

        $router->get('/', function () {
            return 'home';
        })->dispatch();

        $this->assertEquals('home', ob_get_contents());

        ob_end_clean();
    }

    /**
     * @throws Throwable
     */
    public function test_with_fully_namespaced_controller()
    {
        $c = 'MiladRahimi\PhpRouter\Tests\Classes\SampleController@home';

        $router = $this->router()
            ->get('/', $c)
            ->dispatch();

        $this->assertEquals('Home', $this->outputOf($router));
    }

    /**
     * @throws Throwable
     */
    public function test_with_preserved_namespaced_controller()
    {
        $namespace = 'MiladRahimi\PhpRouter\Tests\Classes';

        $router = $this->router('', $namespace)
            ->get('/', 'SampleController@home')
            ->dispatch();

        $this->assertEquals('Home', $this->outputOf($router));
    }

    /**
     * @throws Throwable
     */
    public function test_not_found_error()
    {
        $this->mockRequest(HttpMethods::GET, 'http://example.com/unknowon');

        $this->expectException(RouteNotFoundException::class);

        $this->router()->get('/', $this->controller())->dispatch();
    }

    /**
     * @throws Throwable
     */
    public function test_with_class_method_but_invalid_controller_class()
    {
        $this->expectException(InvalidControllerException::class);

        $this->router()->get('/', 'UnknownController@method')->dispatch();
    }

    /**
     * @throws Throwable
     */
    public function test_with_invalid_controller_class()
    {
        $this->expectException(InvalidControllerException::class);

        $this->router()->get('/', 666)->dispatch();
    }

    /**
     * @throws Throwable
     */
    public function test_with_invalid_controller_method()
    {
        $this->expectException(InvalidControllerException::class);

        $namespace = 'MiladRahimi\PhpRouter\Tests\Classes';
        $this->router('', $namespace)
            ->get('/', 'SampleController@invalidMethod')
            ->dispatch();
    }
}
