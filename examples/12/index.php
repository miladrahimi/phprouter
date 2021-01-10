<?php

require('../../vendor/autoload.php');

use MiladRahimi\PhpRouter\Examples\Shared\SimpleController;
use MiladRahimi\PhpRouter\Examples\Shared\SimpleMiddleware;
use MiladRahimi\PhpRouter\Router;

$router = Router::create();

// A group with uri prefix
$router->group(['prefix' => '/admin'], function (Router $router) {
    // URI: /admin/setting
    $router->get('/setting', function () {
        return 'Setting Panel';
    });
});

// All of group attributes together!
$attributes = [
    'prefix' => '/products',
    'domain' => 'shop.example.com',
    'middleware' => [SimpleMiddleware::class],
];

$router->group($attributes, function (Router $router) {
    // URL: http://shop.example.com/products/{id}
    // Domain: shop.example.com
    // Middleware: SampleMiddleware
    $router->get('/{id}', [SimpleController::class, 'show']);
});

$router->dispatch();
