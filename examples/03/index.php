<?php

require('../../vendor/autoload.php');

use MiladRahimi\PhpRouter\Router;

$router = Router::create();

$router->map('GET', '/', function () {
    return 'GET';
});
$router->map('OPTIONS', '/', function () {
    return 'OPTIONS';
});
$router->map('CUSTOM', '/', function () {
    return 'CUSTOM';
});

$router->dispatch();
