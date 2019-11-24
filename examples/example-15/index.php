<?php

require('../../vendor/autoload.php');

use MiladRahimi\PhpRouter\Router;

$router = new Router();

// Domain
$router->get('/', 'Controller@method', [], 'domain2.com');

// Sub-domain
$router->get('/', 'Controller@method', [], 'server2.domain.com');

// Sub-domain with regex pattern
$router->get('/', 'Controller@method', [], '(.*).domain.com');

$router->dispatch();