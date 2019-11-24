<?php

require('../../vendor/autoload.php');

use MiladRahimi\PhpRouter\Router;

$router = new Router();

$router->any('/', function () {
    return 'This is Home! No matter what the HTTP method is!';
});

$router->dispatch();
