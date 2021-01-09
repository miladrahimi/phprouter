<?php

namespace MiladRahimi\PhpRouter\Tests;

use MiladRahimi\PhpRouter\Exceptions\InvalidCallableException;
use MiladRahimi\PhpRouter\Tests\Common\SampleController;
use Throwable;

class ControllerTest extends TestCase
{
    /**
     * @throws Throwable
     */
    public function test_with_a_closure_controller()
    {
        $router = $this->router();
        $router->get('/', function () {
            return 'Closure';
        });
        $router->dispatch();

        $this->assertEquals('Closure', $this->output($router));
    }

    /**
     * @throws Throwable
     */
    public function test_with_a_method_controller()
    {
        $router = $this->router();
        $router->get('/', [SampleController::class, 'home']);
        $router->dispatch();

        $this->assertEquals('Home', $this->output($router));
    }

    /**
     * @throws Throwable
     */
    public function test_with_an_invalid_array_as_controller_it_should_fail()
    {
        $router = $this->router();
        $router->get('/', ['invalid', 'array', 'controller']);

        $this->expectException(InvalidCallableException::class);
        $router->dispatch();
    }

    /**
     * @throws Throwable
     */
    public function test_with_multiple_controller_for_the_same_route_it_should_call_the_last_one()
    {
        $router = $this->router();
        $router->get('/', [SampleController::class, 'home']);
        $router->get('/', [SampleController::class, 'page']);
        $router->dispatch();

        $this->assertEquals('Page', $this->output($router));
    }
}
