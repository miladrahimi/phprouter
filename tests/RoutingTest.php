<?php

namespace MiladRahimi\PhpRouter\Tests;

use MiladRahimi\PhpRouter\Enums\HttpMethods;
use MiladRahimi\PhpRouter\Exceptions\InvalidControllerException;
use MiladRahimi\PhpRouter\Exceptions\RouteNotFoundException;
use MiladRahimi\PhpRouter\Router;
use MiladRahimi\PhpRouter\Tests\Testing\SampleController;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;
use Laminas\Diactoros\ServerRequest;

class RoutingTest extends TestCase
{
    /**
     * @throws Throwable
     */
    public function test_a_simple_get_route()
    {
        $this->mockRequest(HttpMethods::GET, 'http://example.com/');

        $router = $this->router()->map('GET', '/', $this->OkController())->dispatch();

        $this->assertEquals('OK', $this->output($router));
    }

    /**
     * @throws Throwable
     */
    public function test_a_simple_post_route()
    {
        $this->mockRequest(HttpMethods::POST, 'http://example.com/');

        $router = $this->router()->post('/', $this->OkController())->dispatch();

        $this->assertEquals('OK', $this->output($router));
    }

    /**
     * @throws Throwable
     */
    public function test_a_simple_put_route()
    {
        $this->mockRequest(HttpMethods::PUT, 'http://example.com/');

        $router = $this->router()->put('/', $this->OkController())->dispatch();

        $this->assertEquals('OK', $this->output($router));
    }

    /**
     * @throws Throwable
     */
    public function test_a_simple_patch_route()
    {
        $this->mockRequest(HttpMethods::PATCH, 'http://example.com/');

        $router = $this->router()->patch('/', $this->OkController())->dispatch();

        $this->assertEquals('OK', $this->output($router));
    }

    /**
     * @throws Throwable
     */
    public function test_a_simple_delete_route()
    {
        $this->mockRequest(HttpMethods::DELETE, 'http://example.com/');

        $router = $this->router()->delete('/', $this->OkController())->dispatch();

        $this->assertEquals('OK', $this->output($router));
    }

    /**
     * @throws Throwable
     */
    public function test_a_route_with_custom_method()
    {
        $method = "SCREW";

        $this->mockRequest($method, 'http://example.com/');

        $router = $this->router()->map($method, '/', $this->OkController())->dispatch();

        $this->assertEquals('OK', $this->output($router));
    }

    /**
     * @throws Throwable
     */
    public function test_the_any_method()
    {
        $this->mockRequest(HttpMethods::GET, 'http://example.com/');

        $router = $this->router()->any('/', function () {
            return 'Test any for get';
        })->dispatch();

        $this->assertEquals('Test any for get', $this->output($router));

        $this->mockRequest(HttpMethods::POST, 'http://example.com/');

        $router = $this->router()
            ->any('/', function () {
                return 'Test any for post';
            })
            ->dispatch();

        $this->assertEquals('Test any for post', $this->output($router));
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

        $this->assertEquals('666', $this->output($router));
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

        $this->assertEquals('Home again!', $this->output($router));
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

        $this->assertEquals('Post', $this->output($router));
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

        $this->assertEquals('666', $this->output($router));
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

        $this->assertEquals('666', $this->output($router));
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

        $this->assertEquals('Default', $this->output($router));
    }

    /**
     * @throws Throwable
     */
    public function test_with_defined_parameters()
    {
        $this->mockRequest(HttpMethods::GET, 'http://example.com/666');

        $router = $this->router()
            ->define('id', '[0-9]+')
            ->get('/{id}', $this->OkController())
            ->dispatch();

        $this->assertEquals('OK', $this->output($router));

        $this->mockRequest(HttpMethods::GET, 'http://example.com/abc');

        $this->expectException(RouteNotFoundException::class);

        $this->router()
            ->define('id', '[0-9]+')
            ->get('/{id}', $this->OkController())
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

        $this->assertEquals('GET', $this->output($router));
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

        $this->assertEquals('GET', $this->output($router));
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

        $this->assertEquals('GET', $this->output($router));
    }

    /**
     * @throws Throwable
     */
    public function test_injection_of_router_by_name()
    {
        $router = $this->router()
            ->get('/', function ($router) {
                /** @var Router $router */
                return $router->currentRoute()->getName();
            }, 'home')
            ->dispatch();

        $this->assertEquals('home', $this->output($router));
    }

    /**
     * @throws Throwable
     */
    public function test_injection_of_router_by_type()
    {
        $router = $this->router()
            ->get('/', function (Router $r) {
                return $r->currentRoute()->getName();
            }, 'home')
            ->dispatch();

        $this->assertEquals('home', $this->output($router));
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

        $this->assertEquals('Default', $this->output($router));
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

        $this->assertEquals('CUSTOM', $this->output($router));
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
        $c = 'MiladRahimi\PhpRouter\Tests\Testing\SampleController@home';

        $router = $this->router()
            ->get('/', $c)
            ->dispatch();

        $this->assertEquals('Home', $this->output($router));
    }

    /**
     * @throws Throwable
     */
    public function test_not_found_error()
    {
        $this->mockRequest(HttpMethods::GET, 'http://example.com/unknowon');

        $this->expectException(RouteNotFoundException::class);

        $this->router()->get('/', $this->OkController())->dispatch();
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
    public function test_with_class_but_invalid_method()
    {
        $this->expectException(InvalidControllerException::class);

        $this->router()->get('/', SampleController::class . '@invalid')->dispatch();
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
    public function test_current_route()
    {
        $router = $this->router()
            ->get('/', function (Router $r) {
                return join(',', [
                    $r->currentRoute()->getName(),
                    $r->currentRoute()->getPath(),
                    $r->currentRoute()->getMethod(),
                    count($r->currentRoute()->getMiddleware()),
                    $r->currentRoute()->getDomain() ?? '-',
                ]);
            }, 'home')
            ->dispatch();

        $value = join(',', ['home', '/', 'GET', 0, '-']);
        $this->assertEquals($value, $this->output($router));
    }
}
