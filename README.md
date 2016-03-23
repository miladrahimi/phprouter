# PHPRouter
Free PHP URL router for neat and powerful projects!

## Overview
PHPRouter is a free, neat, powerful and stand-alone URL router for PHP projects.

It's inspired by [Laravel](http://laravel.com/docs/master/routing) routing system and
appropriate for no-framework projects and brand new frameworks.

### URL Routing
URL routing means mapping URLs to controllers.

A URL router is a required part for every project/framework with MVC-based architecture.

PHPRouter as a URL router provides pretty URLs.

See following URL which is provided by a weak router:

```
http://example.com/index.php?section=blog&page_type=post&id=93
```

And compare to this one which is provided by PHPRouter:

```
http://example.com/blog/post/93
```

In addition, PHPRouter provides pretty API and easy-to-use methods for you.

## Installation
### Using Composer (Recommended)
Read
[How to use composer in php projects](http://miladrahimi.com/blog/2015/04/12/how-to-use-composer-in-php-projects)
article if you are not familiar with [Composer](http://getcomposer.org).

Run following command in your project root directory:

```
composer require miladrahimi/phprouter
```

### Manually
You may use your own autoloader as long as it follows [PSR-0](http://www.php-fig.org/psr/psr-0) or
[PSR-4](http://www.php-fig.org/psr/psr-4) standards.
Just put `src` directory contents in your vendor directory.

## Getting Started
All of your application requests must be handled by one PHP file like `index.php`.
Put following directives in your `.htaccess` file to achieve this goal:

```
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^.*$ index.php [PT,L]
```

Now you can use `Router` class in the PHP file (here `index.php`) to route your application:

```
// Use this namespace
use MiladRahimi\PHPRouter\Router;

// Create brand new Router instance
$router = new Router();

// Map this function to home page
$router->get("/", function () {
    return "This is home page!";
});

// Dispatch all matched routes and run!
$router->dispatch();
```

* Try the example in your server root directory (do not put it in a sub directory).
* You will read how to use PHPRouter in projects in subdirectories in the rest of documentation.

## Basic Routing
To buy more convenience, most of controllers in the examples in this documentation are defined as closure.
Of course, PHPRouter supports plenty of controller types which you will read in the further sections.

There are some simple routes below.

```
use MiladRahimi\PHPRouter\Router;

$router = new Router();

// Map this function to home page (GET method)
$router->get("/", function () {
    return "This is home page!";
});

// Map this function to /blog URI (GET method)
$router->get("/blog", function () {
    return "This is blog!";
});

// Map this function to /submit URI (POST method)
$router->post("/submit", function () {
    return "I'm supposed to catch posted data!";
});

// Dispatch routes and run!
$router->dispatch();
```

*   All of controllers above are closure functions.
*   The `get()` method map controllers to `GET` request methods.
*   The `post()` method map controllers to `POST` request methods.

## Request methods
Both `get()` and `post()` methods in `Router` class are shortcuts for mapping controllers to `GET` and `POST` requests.
The super method is `map()` which catches request method as its first argument.

```
use MiladRahimi\PHPRouter\Router;

$router = new Router();

// Alias: $router->get();
$router->map("GET", "/", function () {
    return "This is Home!";
});

// Alias: $router->post();
$router->map("POST", "/post_data", function () {
    return "Data updated!";
});

// PUT Request method
$router->map("PUT", "/put_data", function () {
    return "Data uploaded!";
});

// DELETE Request method
$router->map("DELETE", "/delete_data", function () {
    return "Data deleted!";
});

// Dispatch routes and run!
$router->dispatch();
```

## Multiple request methods
The controller can be mapped to multiple request methods as following example demonstrates:

```
use MiladRahimi\PHPRouter\Router;

$router = new Router();

// Map to GET, POST and DELETE request methods
$router->map(["GET", "POST", "DELETE"], "/", function () {
    return "Homepage for GET, POST and DELETE request methods!";
});

$router->dispatch();
```

*   PHP >= 5.4 supports `[]` [syntax for array](http://php.net/manual/en/language.types.array.php).
    you may use old `array()` syntax.

## Any request method
If the controller can respond to the route with any request method, you may try this method:

```
use MiladRahimi\PHPRouter\Router;

$router = new Router();

// Respond to all request methods (GET, POST, PUT, etc)
$router->any("/", function () {
    return "This is Home! No matter what the request method is!";
});

$router->dispatch();
```

## Multiple routes
Array of routes is supported too. If the controller can respond to multiple routes,
the method below may be useful to you.

```
use MiladRahimi\PHPRouter\Router;

$router = new Router();

$router->map(["GET", "POST"], ["/", "/home"], function () {
    return "Homepage for GET, POST and DELETE requests!";
});

$router->dispatch();
```

*   The controller above will respond to these request methods and routes:
    *   Method: `GET`   Route: `/`
    *   Method: `GET`   Route: `/home`
    *   Method: `POST`  Route: `/`
    *   Method: `POST`  Route: `/home`

## Controllers
Personally, I hate using closure as a controller.
I believe a controller absolutely must be a method.
However, following codes shows how to use all kind of controllers.

```
use MiladRahimi\PHPRouter\Router;

$router = new Router();

// Closure
$router->get("/1", function () {
    return "Closure as controller";
});

// Stored closure
$closure = function() {
    return "Stored closure as controller";
};
$router->get("/2", $closure);

// Function
function func() {
    return "Function as controller";
}
$router->get("/3", "func");

// Method (Recommended)
class Controller
{
    function method()
    {
        return "Method as controller";
    }
}
$router->get("/4", "Controller@method");

$router->dispatch();
```

## Controller class namespaces
Because of MVC pattern, developers usually declare controllers with namespaces.

PHPRouter doesn't recognize "used namespaces",
so you have to pass them with the class names.

See following example, `Post` class is declared with `Blog` namespace.

```
use MiladRahimi\PHPRouter\Router;

$router = new Router();

$router->get("/blog/posts", 'Controllers\Blog\Post@getAll');

$router->dispatch();
```

An the `Post` class:

```
<?php namespace Controllers\Blog;

class Post {
    function getAll() {
        return "All posts";
    }
}
```

## Route parameters
PHPRouter is created to help developers to handle dynamic routes easier than ever.
Let's go back to the first example, I mean this one:

```
http://example.com/blog/post/93
```

The post ID (`93` in the example above) is variable.
Actually we need to handle all the routes with following pattern with only one controller:

```
http://example.com/blog/post/{id}
```

Don't worry!
Because PHPRouter handles it in the easiest way.
Look at following example:

```
use MiladRahimi\PHPRouter\Router;

$router = new Router();

$router->get("/blog/post/{id}", function ($id) {
    return "Show content of post " . $id;
});

$router->dispatch();
```

Or you may use a method as the controller:

```
class Blog
{
    function getPost($id)
    {
        return "Show content of post " . $id;
    }
}

use MiladRahimi\PHPRouter\Router;

$router = new Router();

$router->get("/blog/post/{id}", "Blog@getPost");

$router->dispatch();
```

*   You can determine route parameters with `{` and `}` characters.
*   The Router will extract them and pass their values as arguments to the controller.
*   Controller parameters must has the same route parameter names.
*   PHPRouter passes parameters by name not the sequence.

## Optional Parameters
You may consider one or some of parameters optional.

In this example `id` is optional.
If the request URI had ID, it would return page with the caught ID.
If the request URI was without any ID (`/page/`), it would return `All pages!`.

```
use MiladRahimi\PHPRouter\Router;

$router = new Router();

$router->get("/page/{id?}", function ($id) {
    if($id == null)
        return "All pages!";
    return "Page $id";
});

$router->dispatch();
```

*   When optional parameter was not given it would be `null`.

In the example above to see all pages, user must enter `/page/` URI.
You may consider `/page` for this purpose too.
To achieve this goal you can use an array of routes or just use following trick:

```
use MiladRahimi\PHPRouter\Router;

$router = new Router();

$router->get("/page/?{id?}", function ($id) {
    if($id == null)
        return "All pages!";
    return "Page $id";
});

$router->dispatch();
```

*   `?` makes preceding character optional.

## More customized parameters
In default, route parameters could be anything (`[^/]+` Regular Expression pattern).
Sometimes the route parameter must be only a numeric value.
Sometimes you need them to follow a complex pattern.
No problem! You can define
[Regular Expression](http://www.regular-expressions.info/)
pattern for some or all of the route parameters.
There are also some predefined pattern which you may use.

```
use MiladRahimi\PHPRouter\Router;

$router = new Router();

// 'id' must be a number (number pattern id predefined)
$router->define("id", Router::NUMERIC);

// 'username' must be an alphanumeric, - is allowed too
$router->define("username", "[a-z0-9\-]+");

$router->get("/blog/post/{id}", function ($id) {
    return "Show content of post " . $id;
});

$router->get("/user/{username}", function ($username) {
    return "Show info of user with username: " . $username;
});

$router->dispatch();
```

*   No need to check if ID is a number of not, It will be a number, I promise!
*   To avoid unwanted results, pattern group characters (`(` and `)`) are disabled.
*   There are three predefined patterns as `NUMERIC`, `ALPHABETIC` and `ALPHANUMERIC`.

## Request and Response objects
The Router passes two objects named `$request` and `$response` to the route controller.
Of course the controller must be considered to catch them too.
These objects help you in some cases.
To get these object you must name them (one or both) in the controller parameters.
Don't worry, PHPRouter is enough flexible to don't care about the order of controller parameters!
### Request object
The URL:

```
http://example.com/blog/post/93?section=comments
```

The application routing section:

```
use MiladRahimi\PHPRouter\Request;
use MiladRahimi\PHPRouter\Router;

$router = new Router();

$router->get("/blog/post/{id}", function ($id, Request $request) {
    echo $id . "<br>";
    echo $request->getUrl() . "<br>";
    echo $request->getUri() . "<br>";
    echo $request->getWebsite() . "<br>";
    echo $request->getPage() . "<br>";
    echo $request->getQueryString() . "<br>";
    echo $request->getBaseUri() . "<br>";
    echo $request->getLocalUri() . "<br>";
    echo $request->getMethod() . "<br>";
    echo $request->getProtocol() . "<br>";
    echo $request->getIP() . "<br>";
    echo $request->getPort() . "<br>";
});

$router->dispatch();
```

The output:

```
93
example.com/blog/post/93?section=comments
/blog/post/93?section=comments
example.com
/blog/post/93
section=comments

/blog/post/93
GET
HTTP/1.1
127.0.0.1
14889
```

Other methods:

```
// Return Previous URL (HTTP_REFERRER)
$request->referer();
// Following methods will be discussed more!
$request->get();
$request->get("key");
$request->post();
$request->post("key");
$request->cookie();
$request->cookie("name");
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
