<?php

require('../../vendor/autoload.php');

use MiladRahimi\PhpRouter\Router;

$router = new Router();

// Required parameter
$router->get('/post/{id}', function ($id) {
    return "The content of post $id";
});

// Optional parameter
$router->get('/welcome/{name?}', function ($name = null) {
    return 'Welcome ' . ($name ?: 'Dear User');
});

// Optional parameter, Optional Slash!
$router->get('/profile/?{user?}', function ($user = null) {
    return ($user ?: 'Your') . ' profile';
});

// Optional parameter with default value
$router->get('/role/{role?}', function ($role = 'admin') {
    return "Role is $role";
});

// Multiple parameters
$router->get('/post/{pid}/comment/{cid}', function ($pid, $cid) {
    return "The comment $cid of the post $pid";
});

$router->dispatch();
