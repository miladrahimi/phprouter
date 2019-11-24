<?php

require('../../vendor/autoload.php');

use MiladRahimi\PhpRouter\Router;

$router = new Router('', 'MiladRahimi\PhpRouter\Examples\Samples');

$router->get('/', 'SimpleController@show');
// PhpRouter looks for MiladRahimi\PhpRouter\Examples\Samples\SimpleController@show

$router->dispatch();
