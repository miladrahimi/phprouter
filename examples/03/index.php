<?php

require('../../vendor/autoload.php');

use MiladRahimi\PhpRouter\Router;

$router = Router::create();

$router->define('GET', '/', function () {
    return 'GET';
});
$router->define('OPTIONS', '/', function () {
    return 'OPTIONS';
});
$router->define('CUSTOM', '/', function () {
    return 'CUSTOM';
});

$router->dispatch();
