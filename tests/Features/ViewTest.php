<?php

namespace MiladRahimi\PhpRouter\Tests\Features;

use MiladRahimi\PhpRouter\Router;
use MiladRahimi\PhpRouter\Tests\TestCase;
use MiladRahimi\PhpRouter\View\View;
use Throwable;

class ViewTest extends TestCase
{
    /**
     * @throws Throwable
     */
    public function test_with_the_sample_view()
    {
        ob_start();

        $router = Router::create();
        $router->setupView(__DIR__ . '/../resources/views');

        $router->get('/', function(View $view) {
            return $view->make('sample', ['user' => 'Milad']);
        });
        $router->dispatch();

        $this->assertEquals('<h1>Hello Milad</h1>', ob_get_clean());
    }

    /**
     * @throws Throwable
     */
    public function test_with_the_sample_view_and_status_201_and_headers()
    {
        ob_start();

        $router = Router::create();
        $router->setupView(__DIR__ . '/../resources/views');

        $router->get('/', function(View $view) {
            return $view->make('sample', ['user' => 'Milad'], 201, [
                'X-Powered-By' => 'PhpRouter Test',
            ]);
        });
        $router->dispatch();

        $this->assertEquals('<h1>Hello Milad</h1>', ob_get_clean());
        $this->assertEquals(201, http_response_code());
    }
}
