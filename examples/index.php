<?php

use MiladRahimi\PhpRouter\Enums\GroupAttributes;
use MiladRahimi\PhpRouter\Exceptions\RouteNotFoundException;
use MiladRahimi\PhpRouter\Router;
use Zend\Diactoros\Response\EmptyResponse;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Diactoros\Response\TextResponse;
use Zend\Diactoros\Response\XmlResponse;
use Zend\Diactoros\ServerRequest;

require('../vendor/autoload.php');
require('Controller.php');
require('Middleware.php');

$router = new Router();

// Simple home page
$router->get('/', function () {
    return 'Homepage!';
});

// Simple post endpoint
$router->post('/', function () {
    return 'Homepage for POST method!';
});

// Simple html page
$router->get('/page', function () {
    return new HtmlResponse('<p>This is a HTML page!</p>');
});

// Simple text response
$router->get('/text', function () {
    return new TextResponse('This is a text response!');
});

// Simple JSON response
$router->get('/json', function () {
    return new JsonResponse([
        'song' => 'Hey You!',
        'album' => 'The Wall',
        'singer' => 'PinkFloyd',
    ]);
});

// Simple XML response
$router->get('/xml', function () {
    return new XmlResponse('<year>1993</year>');
});

// Simple empty response
$router->get('/empty', function () {
    return new EmptyResponse();
});

// Simple 400 error response
$router->get('/e400', function () {
    return new HtmlResponse('<p>Invalid Username</p>', 400);
});

// Simple 500 error response
$router->get('/e500', function () {
    return new HtmlResponse('<p>Internal Error.</p>', 500);
});

// Search page (Try /search?q=Something)
$router->get('/search', function (ServerRequest $request) {
    return new JsonResponse($request->getQueryParams());
});

// Redirection
$router->get('/google', function () {
    return new RedirectResponse('https://www.google.com');
});

// Dynamic Blog Post
$router->get('/blog/posts/{id}', function ($id) {
    return $id;
});

// Dynamic Blog Post & Comment
$router->get('/blog/posts/{pid}/comments/{cid}', function ($pid, $cid) {
    return "The comment $cid for the post $pid!";
});

// Using controller class
$router->get('/about', 'Controller@getAbout');

// Using middleware class
$router->get('/middleware', function (ServerRequest $request) {
    return $request->getAttribute('color');
}, Middleware::class);

// Naming routes
$router
    ->name('product')
    ->get('/shop/product/{id}', function ($id) {
        return 'This is the product '.$id;
    })
    ->get('/shop', function (Router $router) {
        return 'A product link: '.$router->url('product', ['id' => 666]);
    });

// Grouping routes with common prefix
$router->group(['prefix' => '/group'], function (Router $router) {
    $router->get('/page', function () {
        return 'URL of this page is /group/page';
    });
});

// Grouping routes with common prefix and middleware
$router->group(['prefix' => '/g', 'middleware' => Middleware::class], function (Router $router) {
    $router->get('/color', function (ServerRequest $request) {
        return 'The page /g/page says the color is ' . $request->getAttribute('color');
    });
});

// Grouping with common domain or sub-domain
$router->group(['domain' => 'server2.com'], function (Router $router) {
    $router->get('/page', function () {
        return 'This page will be displayed when domain is server2.com!';
    });
});

// Dispatch routes and run the app
try {
    $router->dispatch();
} catch (RouteNotFoundException $e) {
    $router->getPublisher()->publish(new TextResponse("Not found.", 404));
} catch (Throwable $e) {
    $router->getPublisher()->publish(new TextResponse($e->getMessage(), 500));
}
