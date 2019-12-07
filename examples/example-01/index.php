<?php

require('../../vendor/autoload.php');

use MiladRahimi\PhpRouter\Router;

$router = new Router();

$router->get('/', function () {
    return '<p>This is homepage!</p>';
});

$router->dispatch();
