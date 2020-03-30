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
            ->get('/', $this->OkController(), 'Home')
            ->dispatch();

        $this->assertEquals('OK', $this->output($router));
        $this->assertTrue($router->currentRoute()->getName() == 'Home');
    }

    /**
     * @throws Throwable
     */
    public function test_duplicate_naming_it_should_set_the_name_for_all_routes()
    {
        $router = $this->router()
            ->get('/', $this->OkController(), 'Home')
            ->get('/home', $this->OkController(), 'Home')
            ->dispatch();

        $this->assertTrue($router->currentRoute()->getName() == 'Home');
    }
}
