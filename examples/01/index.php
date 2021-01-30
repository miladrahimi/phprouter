<?php

require('../../vendor/autoload.php');

use MiladRahimi\PhpRouter\Router;

$router = Router::create();

$router->get('/', function () {
    return 'This is homepage!';
});

$router->dispatch();
