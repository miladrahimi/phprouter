<?php

require('../../vendor/autoload.php');

use MiladRahimi\PhpRouter\Examples\Samples\SimpleMiddleware;
use MiladRahimi\PhpRouter\Router;

$router = new Router();

// A group with uri prefix
$router->group(['prefix' => '/admin'], function (Router $router) {
    // URI: /admin/setting
    $router->get('/setting', function () {
        return 'Setting.';
    });
});

// All of group properties together!
$attributes = [
    'prefix' => '/products',
    'namespace' => 'App\Controllers',
    'domain' => 'shop.example.com',
    'middleware' => SimpleMiddleware::class,
];

// A group with many common properties!
$router->group($attributes, function (Router $router) {
    // URI: http://shop.example.com/products/{id}
    // Controller: App\Controllers\ShopController@getProduct
    // Domain: shop.example.com
    // Middleware: SampleMiddleware
    $router->get('/{id}', function ($id) {
        return 'Wow.';
    });
});

$router->dispatch();
