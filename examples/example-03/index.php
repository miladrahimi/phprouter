<?php

require('../../vendor/autoload.php');

use MiladRahimi\PhpRouter\Router;

$router = new Router();

$router
    ->map('GET', '/', function () {
        return '<b>GET method</b>';
    })
    ->map('POST', '/', function () {
        return '<b>POST method</b>';
    })
    ->map('CUSTOM', '/', function () {
        return '<b>CUSTOM method</b>';
    })
    ->dispatch();
