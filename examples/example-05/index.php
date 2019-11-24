<?php

require('../../vendor/autoload.php');

use MiladRahimi\PhpRouter\Router;

$router = new Router();

$router->get('/closure', function () {
    return 'Closure as a controller';
});

function func() {
    return 'Function as a controller';
}
$router->get('/function', 'func');

$router->dispatch();