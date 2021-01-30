<?php

require('../../vendor/autoload.php');

use MiladRahimi\PhpRouter\Router;

$router = Router::create();

$router->get('/', function () {
    return 'GET';
});
$router->post('/', function () {
    return 'POST';
});
$router->put('/', function () {
    return 'PUT';
});
$router->patch('/', function () {
    return 'PATCH';
});
$router->delete('/', function () {
    return 'DELETE';
});

$router->dispatch();
