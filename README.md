# PhpRouter
PhpRouter is a powerful and standalone URL router for PHP projects.

## Installation

Install [Composer](https://getcomposer.org) and run following command in your project's root directory:

```
composer require miladrahimi/phprouter "3.*"
```

## Configuration
First of all, you need to config your web server software to
handle all the HTTP requests with a single PHP file like `index.php`.
Here you can see related config for Apache HTTP Server and NGINX.

### Apache
If you are using Apache HTTP server,
you should create `.htaccess` in your project's root directory with this content:

```
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

### NGINX
If you are using NGINX web server, you should consider following directive in the site configuration file.

```
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

## Getting Started
After configuration, you can use PhpRouter in your entry point (`index.php`) like this example.

```
use MiladRahimi\PhpRouter\Router;

$router = new Router();

$router->get('/', function () {
    return 'This is home page!';
});

$router->dispatch();
```

To buy more convenience, most of the controllers in the examples are defined using Closures,
of course, PhpRouter supports plenty of controller types which will be discussed further.

## Basic Routing
Following example demonstrates how to define simple routes.

```
use MiladRahimi\PhpRouter\Router;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\JsonResponse;

$router = new Router();

$router->get('/html/1', function () {
    return '<b>Hello from HTML!</b>';
});

$router->post('/html/2', function () {
    return HtmlResponse('<b>Hello from HTML!</b>');
});

$router->patch('/json', function () {
    return JsonResponse(['message' => 'Hello from JSON!']);
});

$router->dispatch();
```

## HTTP Methods

You can use the following PhpRouter methods to map different HTTP methods to the controllers.

```
use MiladRahimi\PhpRouter\Router;

$router = new Router();

$router->get('/', function () {
    return '<b>GET method</b>';
});

$router->post('/', function () {
    return '<b>POST method</b>';
});

$router->patch('/', function () {
    return '<b>PATCH method</b>';
});

$router->put('/', function () {
    return '<b>PUT method</b>';
});

$router->delete('/', function () {
    return '<b>DELETE method</b>';
});

$router->dispatch();
```

You may need to use other HTTP methods or your custom ones, no worry, there is `Router::map()` method for you.

```
use MiladRahimi\PhpRouter\Router;

$router = new Router();

$router->map('GET', '/', function () {
    return '<b>GET method</b>';
});

$router->map('POST', '/', function () {
    return '<b>POST method</b>';
});

$router->map('OPTIONS', '/', function () {
    return '<b>OPTIONS method</b>';
});

$router->map('CUSTOM', '/', function () {
    return '<b>CUSTOM method</b>';
});

$router->dispatch();
```

If your controller is not sensitive about HTTP methods and
it is going to respond regardless to what HTTP method is, the method `Router::any()` is for you.

```
use MiladRahimi\PhpRouter\Router;

$router = new Router();

$router->any('/', function () {
    return 'This is Home! No matter what the HTTP method is!';
});

$router->dispatch();
```

## Controllers
PhpRouter supports plenty of controller types, just look at following examples.

```
use MiladRahimi\PhpRouter\Router;

$router = new Router();

$router->get('/1', function () {
    return 'Closure as a controller';
});

$closure = function() {
    return 'Stored closure as a controller';
};

$router->get('/2', $closure);

function func() {
    return 'Function as a controller';
}

$router->get('/3', 'func');

$router->dispatch();
```

Using a class for controller could be nice:

```
use MiladRahimi\PhpRouter\Router;

$router = new Router();

class Controller
{
    function method()
    {
        return new HtmlResponse('Method as a controller');
    }
}

$router->get('/4', 'Controller@method');

$router->dispatch();
```

The controller class can also have a namespace:

```
namespace App\Http\Controllers;

use MiladRahimi\PhpRouter\Router;

$router = new Router();

class Controller
{
    function method()
    {
        return new HtmlResponse('Method as a controller');
    }
}

$router->get('/5', 'App\Http\Controllers\Controller@method');
// OR
$router->get('/5', Controller::class . '@method');

$router->dispatch();
```

## Route Parameters
Some endpoints have variable parts like IDs in URLs.
We call them the route parameters, and you can catch them with controller parameters with the same names.

```
use MiladRahimi\PhpRouter\Router;

$router = new Router();

// Required parameter
$router->get('/blog/post/{id}', function ($id) {
    return 'Content of the post: ' . $id;
});

// Optional parameter
$router->get('/path/to/{info?}', function ($info = null) {
    return 'Info may be present an may be null.';
});

// Optional parameter, Optional Slash!
$router->get('/path/to/?{info?}', function ($info = null) {
    return 'Info may be present an may be null.';
});

// Optional parameter with default value
$router->get('/path/to/{info?}', function ($info = 'DEFAULT') {
    return 'Info may be present an may be DEFAULT.';
});

$router->dispatch();
```

In default, route parameters can match any value, but you can define a regular expression for it if you want to.

```
use MiladRahimi\PhpRouter\Router;

class BlogController
{
    function getPost(int $id)
    {
        return 'Content of the post: ' . $id;
    }
}

$router = new Router();

// IDs must be numeric
$router->defineParameter('id', '[0-9]+');

$router->get('/blog/post/{id}', 'BlogController@getPost');

$router->dispatch();
```

## HTTP Request and Request

### HTTP Request
PhpRouter passes a [PSR-7](https://www.php-fig.org/psr/psr-7) complaint request object to
the controllers and middleware.
It uses [Zend implementation](https://github.com/zendframework/zend-diactoros) of PSR-7 to create this instance.
You can catch the request object like the example.

```
use MiladRahimi\PhpRouter\Router;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\Response\EmptyResponse;

$router = new Router();

$router->get('/', function (ServerRequest $request) {
    return new JsonResponse([
        'method' => $request->getMethod(),
        'uri' => $request->getUri(),
        'body' => $request->getBody(),
        'parsedBody' => $request->getParsedBody(),
        'headers' => $request->getHeaders(),
        'attributes' => $request->getAttributes(),
    ]);
});

// Catch the POST and GET (Query String) parameters
$router->post('/blog/post', function (ServerRequest $request) {
    $post = new \App\Models\Post();
    $post->title = $request->getAttribute('title');
    $post->content = $request->getAttribute('content');
    $post->save();
    
    return new EmptyResponse($httpCode = 201);
});

$router->dispatch();
```

### HTTP Response
Your controllers can return a string value as the response, it will be considered HTML.
You can also return JSON, plain text and empty responses by provided zend-diactoros package.

```
use MiladRahimi\PhpRouter\Router;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\Response\EmptyResponse;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Diactoros\Response\TextResponse;

$router = new Router();

$router->get('/html/1', function (ServerRequest $request) {
    return '<html>This is an HTML response</html>';
});

$router->get('/html/2', function (ServerRequest $request) {
    return new HtmlResponse('<html>This is also an HTML response</html>');
});

$router->get('/json', function (ServerRequest $request) {
    return new JsonResponse(['message' => 'I am JSON!']);
});

$router->get('/text', function (ServerRequest $request) {
    return new TextResponse('This is a plain text...');
});

$router->get('/empty', function (ServerRequest $request) {
    return new EmptyResponse();
});

$router->dispatch();

```

### Redirection
Your controllers can redirect user to another url as well.

```
use MiladRahimi\PhpRouter\Router;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\Response\RedirectResponse;

$router = new Router();

$router->get('/redirect', function (ServerRequest $request) {
    return new RedirectResponse('https://miladrahimi.com');
});

$router->dispatch();

```

### Read More
Since PhpRouter uses [zendframework/zend-diactoros](https://github.com/zendframework/zend-diactoros) for http request and response,
you should read its documentation to see its functionality.

## Middleware
PhpRouter supports middleware, you can use it for purposes like authentication, authorization and so forth.
Middleware will be run before controller and it can check and manipulate http request.

Here you can see the request lifecycle considering some middleware.

```
Input -[Request]→ Router → Middleware 1 → ... → Middleware N → Controller
                                                                   ↓
                  Output ← Middleware 1 ← ... ← Middleware N ← [Response]
```

To declare a middleware you must implements Middleware interface (which you can see below).

```
use Closure;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface Middleware
{
    /**
     * Handle user request
     *
     * @param ServerRequestInterface $request
     * @param Closure $next
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request, Closure $next);
}
```

As you can see, middleware must have `handle()` method that catches http request and
a closure (which runs the next middleware or the controller) and it returns a response.
Middleware can break the lifecycle and return a response (so the controller never be run) or
it can run the `$next` closure to continue lifecycle.

For example check the following snippet. In the example, if there was a `Authorization` header in the request,
it passes the request to next middleware or controller (if there is no more middleware) and
if the header is absent it returns an empty response with `401 Authorization Failed ` HTTP status code.

```
use MiladRahimi\PhpRouter\Router;
use MiladRahimi\PhpRouter\Middleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

$router = new Router();

class AuthMiddleware implements Middleware {
    /**
     * Handle user request
     *
     * @param ServerRequestInterface $request
     * @param Closure $next
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request, Closure $next)
    {
        if ($request->getHeader('Authorization')) {
            return $next($request);
        }

        return new EmptyResponse(401);
    }
}

$router->get('/auth', function () { return 'OK' }, AuthMiddleware::class);

$router->dispatch();
```

## Domain and Subdomain
Your application may serve different services on different domains or
it may assign subdomain dynamically to users like blog hosting services.
In this case, you need to specify domain or subdomain in addition to the URIs in your routes.

```
use MiladRahimi\PhpRouter\Router;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\Response\RedirectResponse;

$router = new Router();

// Domain
$router->get('/', 'Controller@method', [], 'domain.com');

// Subdomain
$router->get('/', 'Controller@method', [], 'server.domain.com');

// Subdomain pattern
$router->get('/', 'Controller@method', [], '(.*).domain.com');

// Strict subdomain pattern
$router->get('/', 'Controller@method', [], 'server\.domain\.com');

$router->dispatch();
```

Notice that domain parameter receives a regex pattern not a simple string.


## Route Groups
Usually routes can fit in a groups that have common attributes like middleware, domain (or subdomain) and prefix.
To group the routes you can follow the example below.

```
use MiladRahimi\PhpRouter\Router;
use MiladRahimi\PhpRouter\Enums\GroupAttributes;

$router = new Router();

$router->group(['prefix' => '/admin'], function (Router $router) {
    $router->get('/setting', 'AdminController@getSetting');
});

$attributes = [
    GroupAttributes::MIDDLEWARE => SampleMiddleware::class,
    GroupAttributes::PREFIX => '/shop',
    GroupAttributes::DOMAIN => 'shop.example.com',
];

$router->group($attributes, function (Router $router) {
    $router->get('/product/{id}', 'ShopController@getProduct');
});

$router->dispatch();
```

As you can see in the examples, you can `GroupAttributes` enum instead of memorizing route attribute names!

## Base URI
Your project may be in a subdirectory, so all of your route URIs will starts with the subdirectory name.
In the previous version, there was a `setBaseUri()` method but it's removed in this version.
You still can have the same functionality with grouping your routes and using prefix attribute.

```
use MiladRahimi\PhpRouter\Router;

$router = new Router();

$router->group(['prefix' => '/project'], function (Router $router) {
    // Your routes go here...
});

$router->dispatch();
```

## Route Name
You can name your routes and use their names to get their information or check current http request.
You can set the route name via `name` parameter or using `useName()` method before defining the route.

```
use MiladRahimi\PhpRouter\Router;

$router = new Router();

$router->useName('about')->get('/about', function (Router $router) {
    if($router->isRoute('about')) {
        return 'Current route is about';
    } else {
        return 'Current route is ' . $router->currentRouteName();
    }
});

$router->dispatch();
```

## Error Handling
Since your application runs through the `Router::disptach()` method,
you should put it in a `try` block and catch exceptions that will be thrown by your application and the router.

```
use MiladRahimi\PhpRouter\Router;

$router = new Router();

$router->get('/', function () {
    return 'This is home page!';
});

try {
    $router->dispatch();
} catch (RouteNotFoundException $e) {
    $router->publish(new EmptyResponse(404));
} catch (Throwable $e) {
    // other exceptions...
}
```

The router throws following exceptions:

* `InvalidControllerException` if the controller is neither callable nor class method.
* `InvalidMiddlewareException` if the middleware is not a instance of `Middleware` interface.
* `RouteNotFoundException` if cannot find a route for the current request.

The `InvalidControllerException` and `InvalidMiddlewareException` exceptions should never be thrown,
it should be considered `500 Internal Error` if these exceptions be thrown.

The `RouteNotFoundException` should be considered `404 Not found` error.
Of course, do not forget that it would be thrown by `isRoute()` as well.

## License
PhpRouter is initially created by [Milad Rahimi](http://miladrahimi.com)
and released under the [MIT License](http://opensource.org/licenses/mit-license.php).
