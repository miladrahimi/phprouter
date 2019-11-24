<?php

require('../../vendor/autoload.php');

use MiladRahimi\PhpRouter\Examples\Samples\SimpleController;
use MiladRahimi\PhpRouter\Router;

$router = new Router();

$router->get('/ns', 'MiladRahimi\PhpRouter\Examples\Samples\SimpleController@show');
// OR
$router->get('/ns', SimpleController::class . '@show');

$router->dispatch();
