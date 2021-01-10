<?php

require('../../vendor/autoload.php');

use MiladRahimi\PhpRouter\Router;

$router = Router::create();

// Domain
$router->group(['domain' => 'shop.com'], function(Router $router) {
    $router->get('/', function () {
        return 'This is shop.com';
    });
});

// Subdomain
$router->group(['domain' => 'admin.shop.com'], function(Router $router) {
    $router->get('/', function () {
        return 'This is admin.shop.com';
    });
});

// Subdomain with regex pattern
$router->group(['domain' => '(.*).example.com'], function(Router $router) {
    $router->get('/', function () {
        return 'This is a subdomain';
    });
});

$router->dispatch();
