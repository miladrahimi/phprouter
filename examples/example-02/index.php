<?php

require('../../vendor/autoload.php');

use MiladRahimi\PhpRouter\Router;

$router = new Router();

$router
    ->get('/', function () {
        return '<b>GET method</b>';
    })
    ->post('/', function () {
        return '<b>POST method</b>';
    })
    ->patch('/', function () {
        return '<b>PATCH method</b>';
    })
    ->put('/', function () {
        return '<b>PUT method</b>';
    })
    ->delete('/', function () {
        return '<b>DELETE method</b>';
    })
    ->dispatch();
