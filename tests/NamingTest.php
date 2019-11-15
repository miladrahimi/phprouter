<?php

namespace MiladRahimi\PhpRouter\Tests;

use MiladRahimi\PhpRouter\Enums\HttpMethods;
use Throwable;

class NamingTest extends TestCase
{
    /**
     * @throws Throwable
     */
    public function test_a_named_route()
    {
        $router = $this->router()
            ->get('/', $this->OkController(), [], null, 'Home')
            ->dispatch();

        $this->assertEquals('OK', $this->output($router));
        $this->assertTrue($router->currentRoute()->getName() == 'Home');
    }

    /**
     * @throws Throwable
     */
    public function test_the_name_method()
    {
        $router = $this->router()
            ->name('Home')
            ->get('/', $this->OkController())
            ->dispatch();

        $this->assertEquals('OK', $this->output($router));
        $this->assertTrue($router->currentRoute()->getName() == 'Home');

        $this->mockRequest(HttpMethods::POST, 'http://example.com/666');

        $router = $this->router()
            ->post('/{id}', function ($id) {
                return $id;
            })
            ->dispatch();

        $this->assertEquals('666', $this->output($router));
        $this->assertFalse($router->currentRoute()->getName() == 'Home');
    }

    /**
     * @throws Throwable
     */
    public function test_duplicate_naming_it_should_set_the_name_for_all_routes()
    {
        $router = $this->router()
            ->get('/', $this->OkController(), [], null, 'Home')
            ->get('/home', $this->OkController(), [], null, 'Home')
            ->dispatch();

        $this->assertTrue($router->currentRoute()->getName() == 'Home');
    }
}
