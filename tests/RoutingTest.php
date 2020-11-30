<?php

namespace MiladRahimi\PhpRouter\Tests;

use MiladRahimi\PhpRouter\Exceptions\InvalidCallableException;
use MiladRahimi\PhpRouter\Exceptions\RouteNotFoundException;
use MiladRahimi\PhpRouter\Router;
use MiladRahimi\PhpRouter\Tests\Testing\SampleController;
use MiladRahimi\PhpRouter\Routes\Route;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;
use Laminas\Diactoros\ServerRequest;

class RoutingTest extends TestCase
{

    /**
     * @throws Throwable
     */
    public function test_duplicate_routes_with_different_controllers()
    {
        $router = $this->router();
        $router->get('/', function () {
            return 'Home';
        });
        $router->get('/', function () {
            return 'Home again!';
        });
        $router->dispatch();

        $this->assertEquals('Home again!', $this->output($router));
    }

    /**
     * @throws Throwable
     */
    public function test_multiple_http_methods()
    {
        $this->mockRequest('POST', 'http://example.com/');

        $router = $this->router();
        $router->get('/', function () {
            return 'Get';
        });
        $router->post('/', function () {
            return 'Post';
        });
        $router->delete('/', function () {
            return 'Delete';
        });
        $router->dispatch();

        $this->assertEquals('Post', $this->output($router));
    }

    /**
     * @throws Throwable
     */
    public function test_with_a_required_parameter()
    {
        $this->mockRequest('GET', 'http://web.com/666');

        $router = $this->router();
        $router->get('/{id}', function ($id) {
            return $id;
        });
        $router->dispatch();

        $this->assertEquals('666', $this->output($router));
    }

    /**
     * @throws Throwable
     */
    public function test_with_a_optional_parameter_when_it_is_present()
    {
        $this->mockRequest('GET', 'http://web.com/666');

        $router = $this->router();
        $router->get('/{id?}', function ($id) {
            return $id;
        });
        $router->dispatch();

        $this->assertEquals('666', $this->output($router));
    }

    /**
     * @throws Throwable
     */
    public function test_with_a_optional_parameter_when_it_is_not_present()
    {
        $this->mockRequest('GET', 'http://web.com/');

        $router = $this->router();
        $router->get('/{id?}', function ($id = 'Default') {
            return $id;
        });
        $router->dispatch();

        $this->assertEquals('Default', $this->output($router));
    }

    /**
     * @throws Throwable
     */
    public function test_with_defined_parameters()
    {
        $this->mockRequest('GET', 'http://example.com/666');

        $router = $this->router();
        $router->pattern('id', '[0-9]+');
        $router->get('/{id}', $this->OkController());
        $router->dispatch();

        $this->assertEquals('OK', $this->output($router));

        $this->mockRequest('GET', 'http://example.com/abc');

        $this->expectException(RouteNotFoundException::class);

        $router = $this->router();
        $router->pattern('id', '[0-9]+');
        $router->get('/{id}', $this->OkController());
        $router->dispatch();
    }

    /**
     * @throws Throwable
     */
    public function test_injection_of_request_by_interface()
    {
        $router = $this->router();
        $router->get('/', function (ServerRequestInterface $r) {
            return $r->getMethod();
        });
        $router->dispatch();

        $this->assertEquals('GET', $this->output($router));
    }

    /**
     * @throws Throwable
     */
    public function test_injection_of_request_by_type()
    {
        $router = $this->router();
        $router->get('/', function (ServerRequest $r) {
            return $r->getMethod();
        });
        $router->dispatch();

        $this->assertEquals('GET', $this->output($router));
    }

    /**
     * @throws Throwable
     */
    public function test_injection_of_default_value()
    {
        $router = $this->router();
        $router->get('/', function ($default = "Default") {
            return $default;
        });
        $router->dispatch();

        $this->assertEquals('Default', $this->output($router));
    }

    /**
     * @throws Throwable
     */
    public function test_default_publisher()
    {
        ob_start();

        $router = Router::create();

        $router->get('/', function () {
            return 'home';
        });
        $router->dispatch();

        $this->assertEquals('home', ob_get_contents());

        ob_end_clean();
    }

    /**
     * @throws Throwable
     */
    public function test_with_fully_namespaced_controller()
    {
        $router = $this->router();
        $router->get('/', [SampleController::class, 'home']);
        $router->dispatch();

        $this->assertEquals('Home', $this->output($router));
    }

    /**
     * @throws Throwable
     */
    public function test_not_found_error()
    {
        $this->mockRequest('GET', 'http://example.com/unknowon');

        $this->expectException(RouteNotFoundException::class);

        $router = $this->router();
        $router->get('/', $this->OkController());
        $router->dispatch();
    }

    /**
     * @throws Throwable
     */
    public function test_with_class_method_but_invalid_controller_class()
    {
        $this->expectException(InvalidCallableException::class);

        $router = $this->router();
        $router->get('/', 'UnknownController@method');
        $router->dispatch();
    }

    /**
     * @throws Throwable
     */
    public function test_with_class_but_invalid_method()
    {
        $this->expectException(InvalidCallableException::class);

        $router = $this->router();
        $router->get('/', SampleController::class . '@invalid');
        $router->dispatch();
    }

    /**
     * @throws Throwable
     */
    public function test_with_invalid_controller_class()
    {
        $this->expectException(InvalidCallableException::class);

        $router = $this->router();
        $router->get('/', 666);
        $router->dispatch();
    }

    /**
     * @throws Throwable
     */
    public function test_current_route()
    {
        $router = $this->router();
        $router->get('/', function (Route $r) {
            return join(',', [
                $r->getName(),
                $r->getPath(),
                $r->getUri(),
                $r->getParameters(),
                $r->getMethod(),
                count($r->getMiddleware()),
                $r->getDomain() ?? '-',
            ]);
        }, 'home');
        $router->dispatch();

        $value = join(',', ['home', '/', '/', [], 'GET', 0, '-']);
        $this->assertEquals($value, $this->output($router));
    }
}
