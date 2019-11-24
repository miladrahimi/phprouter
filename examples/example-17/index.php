<?php

require('../../vendor/autoload.php');

use MiladRahimi\PhpRouter\Router;

$router = new Router('/shop');

// URI: /shop/about
$router->get('/about', function () {
    return 'About the shop.';
});

// URI: /shop/product/{id}
$router->get('/product/{id}', function ($id) {
    return 'A product.';
});

$router->dispatch();
