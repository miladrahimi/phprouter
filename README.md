# PhpRouter
Standalone URL router for PHP projects

## Overview
PhpRouter is a powerful and stand-alone URL router for PHP projects.

The new version is inspired by [Laravel routing system](http://laravel.com/docs/master/routing) and
it is appropriate for pure PHP projects, and brand new frameworks.

## Installation

Install [Composer](https://getcomposer.org) and run following command in your project's root directory:

```
composer require miladrahimi/phprouter
```

## Configuration
Certainly, all of your application requests must be handled by only one PHP file like `index.php`.
To do so, you can follow the instructions below based on your web server software.

### Apache
If you are using Apache HTTP server,
you should create `.htaccess` in your project's root directory with the content below.

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
If you are using NGINX web server, you should consider following directive in your project's site configuration file.

```
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

## Getting Started
After configuration, you can use PhpRouter in your `index.php` (entry point) file this way:

```
use MiladRahimi\PHPRouter\Router;

$router = new Router();

$router->get('/', function () {
    return 'This is home page!';
});

$router->dispatch();
```

To buy more convenience, most of the controllers in the examples are defined as Closure,
of course, PhpRouter supports plenty of controller types which will be discussed further.

## Basic Routing
Following example demonstrates how to define simple routes.

```
use MiladRahimi\PHPRouter\Router;
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

You can use the following methods defined in PhpRouter to map different HTTP methods to controllers.

```
use MiladRahimi\PHPRouter\Router;

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

You may need to use other HTTP methods or your custom ones, no worry, there is `map()` method for you.

```
use MiladRahimi\PHPRouter\Router;

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
it is going to respond regardless to what HTTP method is, the method "any" is for you.

```
use MiladRahimi\PHPRouter\Router;

$router = new Router();

$router->any('/', function () {
    return 'This is Home! No matter what the HTTP method is!';
});

$router->dispatch();
```

## Controllers
PhpRouter supports plenty of controller types, look at following examples.

```
use MiladRahimi\PHPRouter\Router;

$router = new Router();

$router->get('/1', function () {
    return 'Closure as controller';
});

$closure = function() {
    return 'Stored closure as controller';
};

$router->get('/2', $closure);

function func() {
    return 'Function as controller';
}

$router->get('/3', 'func');

$router->dispatch();
```

```
use MiladRahimi\PHPRouter\Router;

$router = new Router();

class Controller
{
    function method()
    {
        return new HtmlResponse('Method as controller');
    }
}

$router->get('/4', 'Controller@method');

$router->dispatch();
```

```
namespace App\Http\Controllers;

use MiladRahimi\PHPRouter\Router;

$router = new Router();

class Controller
{
    function method()
    {
        return new HtmlResponse('Method as controller');
    }
}

$router->get('/5', 'App\Http\Controllers\Controller@method');

$router->dispatch();
```

## Route Parameters
Some endpoints have variable parts like IDs in URLs.
We call them the route parameters, and you can catch them with route parameters.

```
use MiladRahimi\PHPRouter\Router;

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

In default, route parameters can match any value, but you can define regular expression if you want to.

```
use MiladRahimi\PHPRouter\Router;

$router = new Router();

// IDs must be numeric
$router->defineParameter('id', '[0-9]+');

$router->get('/blog/post/{id}', function ($id) {
    return 'Content of the post: ' . $id;
});

$router->dispatch();
```

## HTTP Request
PhpRouter passes a PSR-7 complaint request object to the controllers and middleware.
It uses "ServerRequestFactory" from the Zend implementation of PSR-7 to create this instance.
You can catch the request object like the example.

```
use MiladRahimi\PHPRouter\Router;
use Zend\Diactoros\ServerRequest;

$router = new Router();

$router->get('/', function (ServerRequest $request) {
    return new JsonResponse([
        'method' => $request->getMethod(),
        'uri' => $request->getUri(),
        'body' => $request->getBody(),
        'headers' => $request->getHeaders(),
    ]);
});

$router->dispatch();
```

### Response object

Response object manipulates the application HTTP response to the client. Here is an example:

```
use MiladRahimi\PHPRouter\Response;
use MiladRahimi\PHPRouter\Router;

$router = new Router();

$router->get("/fa", function (Response $response) {
    $response->render("test.php");
    $response->publish("Here is the published content!");
});

$router->dispatch();
```

The output:

```
This is test.php file content!
Here is the published content!
```

Methods:

```
$response->publish($content);       // Publish string, array or object content
$response->cookie($name,$value);    // Set cookie value (like PHP native setcookie() function)
$response->redirect($to);           // Redirect to the new URL ($to)
$response->render($file);           // Render PHP file (like PHP include syntax)
$response->contents();              // Return current output 
```

## Access $_GET, $_POST and $_COOKIE
Of course you can use the same `$_GET`, `$_POST` and `$_COOKIE` arrays.
But it might neater and more beautiful to use the `$request` methods.

Run following example for `http://example.com/user?id=93`:

```
use MiladRahimi\PHPRouter\Router;

$router = new Router();

$router->get("/user", function ($request) {
    return $request->get("id"); // will publish "93"
});

$router->dispatch();
```

### GET
The `get()` method in `$request` object is used to access GET parameters.
Following example returns $_GET array when the `$key` parameter is absent.

```
$request->get();
```

But this time, it would return value of `id` parameter if it had existed:

```
$request->get("id");
```

### POST
The `post()` method in `$request` object is used to access POST parameters.
Following example returns $_POST array when the `$key` parameter is absent.

```
$request->post();
```

But this time, it would return value of `id` parameter if it had existed:

```
$request->post("id");
```

### COOKIE
The `cookie()` method in `$request` object is used to access cookies.
Following example returns all cookies (as an array) when the `$name` parameter is absent.

```
$request->cookie();
```

But this time, it would return value of `id` in cookies if it had existed:

```
$request->cookie("id");
```

The `cookie()` method in `$response` object is used to manipulate or write cookies.
Following example shows the way it works:

```
$response->cookie("language","persian");
```

*   `$response->cookie()` arguments: `$name`, `$value`, `$expire`, `$path`, `$domain`, `$secure`, `$httponly`

## Redirection
You can redirect the request with the php `header()` function.
But if you are interested in using PHPRouter methods, you may try this:

```
use MiladRahimi\PHPRouter\Router;

$router = new Router();

$router->get("/old-page", function ($response) {
    $response->redirect("/new-page");
});

$router->dispatch();
```

*   The `redirect()` method will consider the **Base URI**.
*   If you omit `$to` parameters, it will be `"/"` in default and it will redirect client to home.

## Rendering PHP files
Sometimes the result you want to return is not plain texts or compiled PHP codes but it's a PHP file.
No problem! you can use `render()` method in `$response` object as it's shown in the following example:

```
use MiladRahimi\PHPRouter\Router;

$router = new Router();

$router->get("/", function ($response) {
    $response->render("test.php");
});

$router->dispatch();
```

*   It's recommended to use a **template engine** which returns HTML content instead.

## Middleware
Middleware is a function or any callable which runs before your controller.
It can control access like authentication or anything you want.
You can consider one or more middleware for any route.
Actually the `$middleware` parameter can be a callable, a method name or an array of them.

```
use MiladRahimi\PHPRouter\Router;

$router = new Router();

// Middleware
function authenticate($id, $response)
{
    if(/* User with this id is not allowed to access */)
        $response->redirect("/login");
}

// Controller
function profile($id)
{
    return "The info of user with this ID:" . $id;
}

$router->get("/user/{id}", "profile", "authenticate");

$router->dispatch();
```

*   All the parameters which Router passes to the controller will be passed to the middleware too.
*   In this level middleware seems not very useful.
    Read the rest of documentation, you will find it very useful!

## Groups
It's the most exciting part! You can group your routes and set common options for them.
In this version PHPRouter supports common
**middleware**, **domain**, **subdomain**, **prefix** and **postfix**.

For example all of user control panel pages need authentication.
So you can group all of them.

```
use MiladRahimi\PHPRouter\Router;

$router = new Router();

// Middleware
function authenticate($id, $response)
{
    if(/* User with this id is not allowed to access */)
        $response->redirect("/login");
}

$router->group("authenticate", function ($router) {

    $router->get("/user/{id}/profile", function ($id) {
        return "User profile page!";
    });

    $router->get("/user/{id}/setting", function ($id) {
        return "User setting page!";
    });

    $router->get("/user/{id}/messages", function ($id) {
        return "User messages page!";
    });

});

$router->dispatch();
```

*   First argument of `group()` method is common options. You can pass all commons in an array.
*   If you pass a callable function or a name of function or method, it will be considered as middleware.
*   Second argument of `group()` method is a function or method which declares group body (routes).
*   The body must access the `$router` object somehow, It will if you consider `$router` parameter.
*   You may use PHP `use` statement instead of putting `$router` parameter for group body.

You may use **prefix** option beside of middleware.

```
use MiladRahimi\PHPRouter\Router;
$router = new Router();

// Middleware
function authenticate($id, $response)
{
    if(/* User with this id is not allowed to access */)
        $response->redirect("/login");
}

$options = ["middleware" => "authenticate", "prefix" => "/user"]; // This line!

$router->group($options, function (Router $router) { // And this line too!

    $router->get("/{id}/profile", function ($id) {
        return "User $id profile page!";
    });

    $router->get("/{id}/setting", function ($id) {
        return "User $id setting page!";
    });

    $router->get("/{id}/messages", function ($id) {
        return "User $id messages page!";
    });

});

$router->dispatch();
```

*   The `middleware` element can be callable function, name of a function or method or array of them.
*   The `prefix` and `postfix` elements are string, array of them is not permitted.
*   You may put `$router` data type (`Router`) in the group body parameters to help your IDE.
*   If you use this way of injecting Router object to the group body,
    the object's name in the body will be `$router` always.

You may use PHP `use` syntax to access the router object.
It's useful specially when the router object name is not `$router`!

```
use MiladRahimi\PHPRouter\Router;

$my_router = new Router();

// Middleware
function authenticate($id, $response)
{
    if (is_permitted_user($id) == false) {
        $response->redirect("/forbidden");
    }
}

$options = ["middleware" => "authenticate", "prefix" => "/user"];

$my_router->group($options, function () use ($my_router) { // This line!

    $my_router->get("/{id}/profile", function ($id) {
        return "User $id profile page!";
    });

    // ...

});

$router->dispatch();
```

*   When you use `use` syntax, you can keep your Router object's name in the group body.

## Domains
You can have some different websites on one hosting!
The `domain` element in the first argument of `group()` method will help you.
Check out following example:

```
use MiladRahimi\PHPRouter\Router;

$router = new Router();

$router->group(["domain" => "domain1.com"], function () use ($router) {
    $router->get("/", function () {
        return "Homepage of domain1.com";
    });
});

$router->group(["domain" => "domain1.com"], function () use ($router) {
    $router->get("/", function () {
        return "Homepage of domain2.com";
    });
});

$router->dispatch();
```

*   You can use other common options like middleware beside of domain option.

## Subdomains
It's really easy to manage subdomains with PHPRouter.
The `domain` element in the first argument of `group()` method will help you.
As mentioned above this element is used to manage domains too,
but this time we work with subdomains.
### Static subdomains
Following Example shows how to work with `blog` and `forum` subdomains:

```
use MiladRahimi\PHPRouter\Router;

$router = new Router();

$router->group(["domain" => "blog.example.com"], function () use ($router) {
    $router->get("/", function () {
        return "Blog home page!";
    });
});

$router->group(["domain" => "forum.example.com"], function () use ($router) {
    $router->get("/", function () {
        return "Forum home page!";
    });
});

$router->dispatch();
```

### Dynamic subdomains
You can catch subdomain as a parameter just like route parameter.
See the following example.
it's really nice feature.

```
use MiladRahimi\PHPRouter\Router;

$router = new Router();

$router->group(["domain" => "{subdomain}.example.com"], function () use ($router) {
    $router->get("/", function ($subdomain) {
        return "This is " . $subdomain;
    });
});

$router->dispatch();
```

*   You can make parameter in domain wherever you need and catch them in controller parameters.
*   Route parameters would overwrite subdomains if their name was the same.
*   You can use other common options like middleware next to domain option.
*   Just like route parameters, middleware will can access the subdomains.

## Base URI and projects in subdirectory
You can handle routes for the projects in subdirectories with the tools explained above.
But there is a tool to make this job even easier.
For example you work on a blog and all of its files are in directory like `blog`.
You can set it's base URI to route you blog easier.
It's useful in some cases like redirection too, the `redirect()` in `$response` object adds base URI to the target.

```
use MiladRahimi\PHPRouter\Router;

// You can determine the base URI with Router constructor
$router = new Router("/blog");

$router->get("/post/{id}", function ($id) {
    return "Show post " . $id;
});

$router->dispatch();
```

*   You can set base URI only in constructor to keep it unique in whole the application.
*   There is a method named `getBaseURI()` but there is no `setBaseURI` method!

## Error Handling
To be neater, all of the errors (exceptions) will be thrown when you call `dispatch()` method.
So you must wrap this method with `try-catch`.

```
use MiladRahimi\PHPRouter\Router;
use MiladRahimi\PHPRouter\HttpError;
use MiladRahimi\PHPRouter\PHPRouterError;

$router = new Router();

$router->get("/", function() {
    return "Homepage!";
});

try {
    $router->dispatch();
} catch(HttpError $e) {
    if($e->getMessage() == "404")
        $router->publish("Error 404! Not found!");
    //...

} catch(Exception $e) {
    // Log details...
    $router->publish("Sorry, there is an internal error, we will fix it asap!");
}
```

*   You may use `publish()` method or PHP `echo()` or anything you like to publish details for users.

### HttpError Exception
This exception will be thrown when an HTTP error like **Error 404 Not Found** occurs.
The exception message contain it's code like `404`.

### Your application exceptions
PHPRouter doesn't manipulate your application exceptions,
so you can catch them like `HttpError` exception.

## Template Engine
When you use a router for your application, you will find it out soon which you need a **template engine** too.
Template engines usually return HTML outputs, so you can return it in your controller.
I have created a template engine package too and you may download it at:
[PHPTemplate](https://github.com/miladrahimi/phptemplate).

## License
PHPRouter is created by [Milad Rahimi](http://miladrahimi.com)
and released under the [MIT License](http://opensource.org/licenses/mit-license.php).
