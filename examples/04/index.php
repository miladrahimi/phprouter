<?php

require('../../vendor/autoload.php');

use MiladRahimi\PhpRouter\Router;

$router = Router::create();

$router->match(['GET', 'POST'], '/', function () {
    return 'GET or POST!';
});

$router->dispatch();
