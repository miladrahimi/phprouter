[![Latest Stable Version](https://poser.pugx.org/miladrahimi/phprouter/v)](//packagist.org/packages/miladrahimi/phprouter)
[![Total Downloads](https://poser.pugx.org/miladrahimi/phprouter/downloads)](//packagist.org/packages/miladrahimi/phprouter)
[![Build Status](https://travis-ci.org/miladrahimi/phprouter.svg?branch=master)](https://travis-ci.org/miladrahimi/phprouter)
[![Coverage Status](https://coveralls.io/repos/github/miladrahimi/phprouter/badge.svg?branch=master)](https://coveralls.io/github/miladrahimi/phprouter?branch=master)
[![License](https://poser.pugx.org/miladrahimi/phprouter/license)](//packagist.org/packages/miladrahimi/phprouter)

# PhpRouter

PhpRouter is a powerful, lightweight, and very fast HTTP URL router for PHP projects.

Some of the provided features:
* Route parameters
* Predefined route parameter patterns
* Middleware
* Closure and class controllers/middleware
* Route groups (by prefix, middleware, and domain)
* Route naming (and generating route by name)
* PSR-7 requests and responses
* Views
* Multiple (sub)domains (using regex patterns)
* Custom HTTP methods
* Integrated with an Ioc Container([PhpContainer](https://github.com/miladrahimi/phpcontainer))
* Method and constructor auto-injection of request, route, url, etc

The current version requires PHP `v7.1` or newer versions including (`v8.0`).

## Contents
- [Versions](#versions)
- [Documentation](#documentation)
    - [Installation](#installation)
    - [Configuration](#configuration)
    - [Getting Started](#getting-started)
    - [HTTP Methods](#http-methods)
    - [Controllers](#controllers)
    - [Route Parameters](#route-parameters)
    - [Requests and Responses](#requests-and-responses)
    - [Route Groups](#route-groups)
    - [Middleware](#middleware)
    - [Domains and Subdomains](#domains-and-subdomains)
    - [Views](#views)
    - [Route Names](#route-names)
    - [Current Route](#current-route)
    - [Error Handling](#error-handling)
- [License](#license)

## Versions

Supported versions:

* v5.x.x
* v4.x.x

Unsupported versions:

* v3.x.x

Unavailable versions:

* v2.x.x
* v1.x.x

## Documentation

### Installation

Install [Composer](https://getcomposer.org) and run following command in your project's root directory:

```bash
composer require miladrahimi/phprouter "5.*"
```

### Configuration

First of all,
you need to configure your webserver to handle all the HTTP requests with a single PHP file like the `index.php` file.
Here you can see sample configurations for NGINX and Apache HTTP Server.

* NGINX configuration sample:
    ```nginx
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    ```

* Apache `.htaccess` sample:
    ```apacheconfig
    <IfModule mod_rewrite.c>
        <IfModule mod_negotiation.c>
            Options -MultiViews
        </IfModule>

        RewriteEngine On

        RewriteCond %{REQUEST_FILENAME} !-d
        RewriteRule ^(.*)/$ /$1 [L,R=301]

        RewriteCond %{REQUEST_FILENAME} !-d
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteRule ^ index.php [L]
    </IfModule>
    ```

### Getting Started

It's so easy to work with PhpRouter! Just take a look at the following example.

*  API Example:

    ```php
    use MiladRahimi\PhpRouter\Router;
    use Laminas\Diactoros\Response\JsonResponse;

    $router = Router::create();

    $router->get('/', function () {
        return new JsonResponse(['message' => 'ok']);
    });

    $router->dispatch();
    ```

* View Example:

    ```php
    use MiladRahimi\PhpRouter\Router;
    use MiladRahimi\PhpRouter\View\View

    $router = Router::create();
    $router->setupView('/../views');

    $router->get('/', function (View $view) {
        return $view->make('profile', ['user' => 'Jack']);
    });

    $router->dispatch();
    ```

### HTTP Methods

The following example illustrates how to declare different routes for different HTTP methods.

```php
use MiladRahimi\PhpRouter\Router;

$router = Router::create();

$router->get('/', function () {
    return 'GET';
});
$router->post('/', function () {
    return 'POST';
});
$router->put('/', function () {
    return 'PUT';
});
$router->patch('/', function () {
    return 'PATCH';
});
$router->delete('/', function () {
    return 'DELETE';
});

$router->dispatch();
```

You can use the `define()` method for other HTTP methods like this example:

```php
use MiladRahimi\PhpRouter\Router;

$router = Router::create();

$router->define('GET', '/', function () {
    return 'GET';
});
$router->define('OPTIONS', '/', function () {
    return 'OPTIONS';
});
$router->define('CUSTOM', '/', function () {
    return 'CUSTOM';
});

$router->dispatch();
```

If you don't care about HTTP verbs, you can use the `any()` method.

```php
use MiladRahimi\PhpRouter\Router;

$router = Router::create();

$router->any('/', function () {
    return 'This is Home! No matter what the HTTP method is!';
});

$router->dispatch();
```

### Controllers

#### Closure Controllers

```php
use MiladRahimi\PhpRouter\Router;

$router = Router::create();

$router->get('/', function () {
    return 'This is a closure controller!';
});

$router->dispatch();
```

#### Class Method Controllers

```php
use MiladRahimi\PhpRouter\Router;

class UsersController
{
    function index()
    {
        return 'Class: UsersController, Method: index';
    }

    function handle()
    {
        return 'Class UsersController.';
    }
}

$router = Router::create();

// Controller: Class=UsersController Method=index()
$router->get('/method', [UsersController::class, 'index']);

// Controller: Class=UsersController Method=handle()
$router->get('/class', UsersController::class);

$router->dispatch();
```

### Route Parameters

A URL might have one or more variable parts like product IDs on a shopping website.
We call it a route parameter.
You can catch them by controller method arguments like the example below.

```php
use MiladRahimi\PhpRouter\Router;

$router = Router::create();

// Required parameter
$router->get('/post/{id}', function ($id) {
    return "The content of post $id";
});
// Optional parameter
$router->get('/welcome/{name?}', function ($name = null) {
    return 'Welcome ' . ($name ?: 'Dear User');
});
// Optional parameter, Optional / (Slash)!
$router->get('/profile/?{user?}', function ($user = null) {
    return ($user ?: 'Your') . ' profile';
});
// Optional parameter with default value
$router->get('/roles/{role?}', function ($role = 'guest') {
    return "Your role is $role";
});
// Multiple parameters
$router->get('/post/{pid}/comment/{cid}', function ($pid, $cid) {
    return "The comment $cid of the post $pid";
});

$router->dispatch();
```

#### Route Parameter Patterns

In default, route parameters can have any value, but you can define regex patterns to limit them.

```php
use MiladRahimi\PhpRouter\Router;

$router = Router::create();

// "id" must be numeric
$router->pattern('id', '[0-9]+');

$router->get('/post/{id}', function (int $id) {
    return 'Content of the post: ' . $id;
});

$router->dispatch();
```

### Requests and Responses

PhpRouter uses [laminas-diactoros](https://github.com/laminas/laminas-diactoros/)
(formerly known as [zend-diactoros](https://github.com/zendframework/zend-diactoros))
package (v2) to provide [PSR-7](https://www.php-fig.org/psr/psr-7)
request and response objects to your controllers and middleware.

#### Requests

You can catch the request object in your controllers like this example:

```php
use MiladRahimi\PhpRouter\Router;
use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\Response\JsonResponse;

$router = Router::create();

$router->get('/', function (ServerRequest $request) {
    $info = [
        'method' => $request->getMethod(),
        'uri' => $request->getUri()->getPath(),
        'body' => $request->getBody()->getContents(),
        'parsedBody' => $request->getParsedBody(),
        'headers' => $request->getHeaders(),
        'queryParameters' => $request->getQueryParams(),
        'attributes' => $request->getAttributes(),
    ];
    // ...
});

$router->dispatch();
```

#### Responses

The example below illustrates the built-in responses.

```php
use Laminas\Diactoros\Response\RedirectResponse;
use MiladRahimi\PhpRouter\Router;
use Laminas\Diactoros\Response\EmptyResponse;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\Response\TextResponse;

$router = Router::create();

$router->get('/html/1', function () {
    return '<html>This is an HTML response</html>';
});
$router->get('/html/2', function () {
    return new HtmlResponse('<html>This is also an HTML response</html>', 200);
});
$router->get('/json', function () {
    return new JsonResponse(['error' => 'Unauthorized!'], 401);
});
$router->get('/text', function () {
    return new TextResponse('This is a plain text...');
});
$router->get('/empty', function () {
    return new EmptyResponse(204);
});
$router->get('/redirect', function () {
    return new RedirectResponse('https://miladrahimi.com');
});

$router->dispatch();
```

### Route Groups

You can categorize routes into groups.
The groups can have common attributes like middleware, domain, or prefix.
The following example shows how to group routes:

```php
use MiladRahimi\PhpRouter\Router;

$router = Router::create();

// A group with uri prefix
$router->group(['prefix' => '/admin'], function (Router $router) {
    // URI: /admin/setting
    $router->get('/setting', function () {
        return 'Setting Panel';
    });
});

// All of group attributes together!
$attributes = [
    'prefix' => '/admin',
    'domain' => 'shop.example.com',
    'middleware' => [AuthMiddleware::class],
];

$router->group($attributes, function (Router $router) {
    // URL: http://shop.example.com/admin/users
    // Domain: shop.example.com
    // Middleware: AuthMiddleware
    $router->get('/users', [UsersController::class, 'index']);
});

$router->dispatch();
```

The group attributes will be explained later in this documentation.

You can use [Attributes](src/Routing/Attributes.php) enum, as well.

### Middleware

PhpRouter supports middleware.
You can use it for different purposes, such as authentication, authorization, throttles, and so forth.
Middleware runs before and after controllers, and it can check and manipulate requests and responses.

Here you can see the request lifecycle considering some middleware:

```
[Request]  ↦ Router ↦ Middleware 1 ↦ ... ↦ Middleware N ↦ Controller
                                                              ↧
[Response] ↤ Router ↤ Middleware 1 ↤ ... ↤ Middleware N ↤ [Response]
```

To declare a middleware, you can use closures and classes just like controllers.
To use the middleware, you must group the routes and mention the middleware in the group attributes.
Caution! The middleware attribute in groups takes an array of middleware, not a single one.

```php
use MiladRahimi\PhpRouter\Router;
use Psr\Http\Message\ServerRequestInterface;
use Laminas\Diactoros\Response\JsonResponse;

class AuthMiddleware
{
    public function handle(ServerRequestInterface $request, Closure $next)
    {
        if ($request->getHeader('Authorization')) {            
            // Call the next middleware/controller
            return $next($request);
        }

        return new JsonResponse(['error' => 'Unauthorized!'], 401);
    }
}

$router = Router::create();

// The middleware attribute takes an array of middleware, not a single one!
$router->group(['middleware' => [AuthMiddleware::class]], function(Router $router) {
    $router->get('/admin', function () {
        return 'Admin API';
    });
});

$router->dispatch();
```

As you can see, the middleware catches the request and the `$next` closure.
The closure calls the next middleware or the controller if no middleware is left.
The middleware must return a response, as well.
A middleware can break the lifecycle and return a response itself,
or it can call the `$next` closure to continue the lifecycle.

### Domains and Subdomains

Your application may serve different services on different domains or subdomains.
In this case, you can specify the domain or subdomain for your routes.
See this example:

```php
use MiladRahimi\PhpRouter\Router;

$router = Router::create();

// Domain
$router->group(['domain' => 'shop.com'], function(Router $router) {
    $router->get('/', function () {
        return 'This is shop.com';
    });
});

// Subdomain
$router->group(['domain' => 'admin.shop.com'], function(Router $router) {
    $router->get('/', function () {
        return 'This is admin.shop.com';
    });
});

// Subdomain with regex pattern
$router->group(['domain' => '(.*).example.com'], function(Router $router) {
    $router->get('/', function () {
        return 'This is a subdomain';
    });
});

$router->dispatch();
```

### Views

You might need to create a classic-style website that uses views.
PhpRouter has a simple feature for working with PHP/HTML views.
Look at the following example.

```php
use MiladRahimi\PhpRouter\Router;
use MiladRahimi\PhpRouter\View\View

$router = Router::create();

// Setup view feature and set the directory of view files
$router->setupView(__DIR__ . '/../views');

$router->get('/profile', function (View $view) {
    // It looks for a view with path: __DIR__/../views/profile.phtml
    return $view->make('profile', ['user' => 'Jack']);
});

$router->get('/blog/post', function (View $view) {
    // It looks for a view with path: __DIR__/../views/blog/post.phtml
    return $view->make('blog.post', ['post' => $post]);
});

$router->dispatch();
```

There is also some points:
* View files must have the ".phtml" extension (e.g. `profile.phtml`).
* You must separate sub-directories with `.` (e.g. `blog.post` for `blog/post.phtml`).

View files are pure PHP or mixed with HTML.
You should use PHP language with template style in the view files.
This is a sample view file:

```php
<h1><?php echo $title ?></h1>
<ul>
    <?php foreach ($posts as $post): ?>
        <li><?php echo $post['content'] ?></li>
    <?php endforeach ?>
</ul>
```

### Route Names

You can assign names to your routes and use them in your codes instead of the hard-coded URLs.
See this example:

```php
use MiladRahimi\PhpRouter\Router;
use Laminas\Diactoros\Response\JsonResponse;
use MiladRahimi\PhpRouter\Url;

$router = Router::create();

$router->get('/about', [AboutController::class, 'show'], 'about');
$router->get('/post/{id}', [PostController::class, 'show'], 'post');
$router->get('/links', function (Url $url) {
    return new JsonResponse([
        'about' => $url->make('about'),             /* Result: /about  */
        'post1' => $url->make('post', ['id' => 1]), /* Result: /post/1 */
        'post2' => $url->make('post', ['id' => 2])  /* Result: /post/2 */
    ]);
});

$router->dispatch();
```

### Current Route

You might need to get information about the current route in your controller or middleware.
This example shows how to get this information.

```php
use MiladRahimi\PhpRouter\Router;
use Laminas\Diactoros\Response\JsonResponse;
use MiladRahimi\PhpRouter\Routing\Route;

$router = Router::create();

$router->get('/{id}', function (Route $route) {
    return new JsonResponse([
        'uri'    => $route->getUri(),            /* Result: "/1" */
        'name'   => $route->getName(),           /* Result: "sample" */
        'path'   => $route->getPath(),           /* Result: "/{id}" */
        'method' => $route->getMethod(),         /* Result: "GET" */
        'domain' => $route->getDomain(),         /* Result: null */
        'parameters' => $route->getParameters(), /* Result: {"id": "1"} */
        'middleware' => $route->getMiddleware(), /* Result: []  */
        'controller' => $route->getController(), /* Result: {}  */
    ]);
}, 'sample');

$router->dispatch();
```

### IoC Container

PhpRouter uses [PhpContainer](https://github.com/miladrahimi/phpcontainer) to provide an IoC container for the package itself and your application's dependencies.

#### How does PhpRouter use the container?

PhpRouter binds route parameters, HTTP Request, Route (Current route), Url (URL generator), Container itself.
The controller method or constructor can resolve these dependencies and catch them.

#### How can your app use the container?

Just look at the following example.

```php
use MiladRahimi\PhpContainer\Container;
use MiladRahimi\PhpRouter\Router;

$router = Router::create();

$router->getContainer()->singleton(Database::class, MySQL::class);
$router->getContainer()->singleton(Config::class, JsonConfig::class);

// Resolve directly
$router->get('/', function (Database $database, Config $config) {
    // Use MySQL and JsonConfig...
});

// Resolve container
$router->get('/', function (Container $container) {
    $database = $container->get(Database::class);
    $config = $container->get(Config::class);
});

$router->dispatch();
```

Check [PhpContainer](https://github.com/miladrahimi/phpcontainer) for more information about this powerful IoC container.

### Error Handling

Your application runs through the `Router::dispatch()` method.
You should put it in a `try` block and catch exceptions.
It throws your application and PhpRouter exceptions.

```php
use MiladRahimi\PhpRouter\Router;
use MiladRahimi\PhpRouter\Exceptions\RouteNotFoundException;
use Laminas\Diactoros\Response\HtmlResponse;

$router = Router::create();

$router->get('/', function () {
    return 'Home.';
});

try {
    $router->dispatch();
} catch (RouteNotFoundException $e) {
    // It's 404!
    $router->getPublisher()->publish(new HtmlResponse('Not found.', 404));
} catch (Throwable $e) {
    // Log and report...
    $router->getPublisher()->publish(new HtmlResponse('Internal error.', 500));
}
```

PhpRouter throws the following exceptions:

* `RouteNotFoundException` if PhpRouter cannot find any route that matches the user request.
* `InvalidCallableException` if PhpRouter cannot invoke the controller or middleware.

The `RouteNotFoundException` should be considered `404 Not found` error.

## License

PhpRouter is initially created by [Milad Rahimi](https://miladrahimi.com) and released under the [MIT License](http://opensource.org/licenses/mit-license.php).

