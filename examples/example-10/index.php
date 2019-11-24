<?php

require('../../vendor/autoload.php');

use MiladRahimi\PhpRouter\Router;

$router = new Router();

$router->define('id', '[0-9]+');

$router->get('/post/{id}', function (int $id) {
    return 'Content of the post: ' . $id;
});

$router->dispatch();
