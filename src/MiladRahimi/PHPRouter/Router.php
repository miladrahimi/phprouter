<?php namespace MiladRahimi\PHPRouter;

use MiladRahimi\PHPRouter\Exceptions\BadController;
use MiladRahimi\PHPRouter\Exceptions\BadMiddleware;
use MiladRahimi\PHPRouter\Exceptions\HttpError;
use MiladRahimi\PHPRouter\Exceptions\InvalidArgumentException;

/**
 * Class Router
 * Router class is the main class which developers must interactive with to
 * dispatch all website routes.
 *
 * @package MiladRahimi\PHPRouter
 * @author  Milad Rahimi <info@miladrahimi.com>
 */
class Router {
    /**
     * Number regex pattern
     */
    const NUMERIC = "[0-9]+";

    /**
     * Alphanumeric regex pattern
     */
    const ALPHANUMERIC = "[A-Za-z0-9]+";

    /**
     * Alphabetic regex pattern
     */
    const ALPHABETIC = "[A-Za-z]+";

    /**
     * Declared routes
     *
     * @var array
     */
    private $routes = array();

    /**
     * Declared parameters patterns
     *
     * @var array
     */
    private $parameters = array();

    /**
     * The base URI (for projects in sub-folder)
     *
     * @var string
     */
    private $base_uri;

    /**
     * The list of middleware for the current group
     *
     * @var array|null
     */
    private $group_middleware = array();

    /**
     * The prefix for the current group
     *
     * @var string
     */
    private $group_prefix = "";

    /**
     * The postfix for the current group
     *
     * @var string
     */
    private $group_postfix = "";

    /**
     * The domain pattern for current group
     *
     * @var string
     */
    private $group_domain = "";

    /**
     * End-user HTTP request
     *
     * @var Request
     */
    private $request;

    /**
     * HTTP response to end-user
     *
     * @var Response
     */
    private $response;

    /**
     * Constructor
     *
     * @param string $base_uri
     *
     * @throw InvalidArgumentException
     */
    public function __construct($base_uri = "") {
        // Set Base URI
        if (!is_string($base_uri)) {
            throw new InvalidArgumentException("Base URI must be a string value");
        }
        $this->base_uri = $base_uri;
        // New Request
        $this->request = Request::getInstance($this);
        $this->response = Response::getInstance($this);
    }

    /**
     * @return string
     */
    public function __toString() {
        return $this->request->getMethod() . " " . $this->request->getUri();
    }

    /**
     * Group routes to set common options
     *
     * @param array|callable|string $options
     * @param callable              $body
     */
    public function group($options, $body) {
        if (!isset($options)) {
            throw new InvalidArgumentException("Options must be set");
        }
        if (!isset($body)) {
            throw new InvalidArgumentException("Body must be set");
        }
        if (is_array($options)) {
            if (isset($options["middleware"])) {
                if (!is_array($options["middleware"])) {
                    $options["middleware"] = array($options["middleware"]);
                }
            }
        } else {
            if (is_callable($options) || is_string($options)) {
                $middleware = $options;
                $options = array();
                $options["middleware"] = array($middleware);
            }
        }
        $this->group_middleware = (!isset($options["middleware"]) || !is_array($t = $options["middleware"]))
            ? array() : $t;
        $this->group_prefix = (!isset($options["prefix"]) || !is_string($t = $options["prefix"])) ? "" : $t;
        $this->group_postfix = (!isset($options["postfix"]) || !is_string($t = $options["postfix"])) ? "" : $t;
        $this->group_domain = (!isset($options["domain"]) || !is_string($t = $options["domain"])) ? "" : $t;
        if (is_callable($body)) {
            $body($this);
        }
        $this->group_middleware = array();
        $this->group_prefix = "";
        $this->group_postfix = "";
    }

    /**
     * Dispatch all routes and perform the appropriate controller (in order to run!)
     */
    public function dispatch() {
        $this->checkBaseURI();
        // Prepare all routes of the requested method
        if (!isset($this->routes[null]) || !is_array($this->routes[null])) {
            $this->routes[null] = array();
        }
        if (!isset($this->routes[$this->request->getMethod()]) ||
            !is_array($this->routes[$this->request->getMethod()])
        ) {
            $this->routes[$this->request->getMethod()] = array();
        }
        $routes = array_merge($this->routes[null], $this->routes[$this->request->getMethod()]);
        // Flag down! Not found is the default
        $flag = false;
        // Check all routes
        foreach ($routes as $route => $route_options) {
            // Extract all the current route parameter
            $route_pattern = $this->convertToRegex($route);
            // Check if the request page is match with current route
            if (preg_match($route_pattern, $this->request->getLocalUri(), $arguments)) {
                // Check the domain
                $domain_args = $this->checkDomain($route_options["domain"]);
                // Flag up! found, matches...
                $flag = true;
                $arguments["request"] = (isset($parameters["request"])) ? $parameters["request"] : $this->request;
                $arguments["response"] = (isset($parameters["response"])) ? $parameters["response"] : $this->response;
                $parameters = array_merge($domain_args, $arguments);
                // Controller...
                if (is_callable($controller = $route_options["controller"])) {
                    $parameters = $this->arrangeFuncArgs($controller, $parameters);
                } else {
                    $list = explode('@', $controller = $route_options["controller"]);
                    if (count($list) != 2) {
                        throw new BadController('Cannot detect the controller class');
                    }
                    if (method_exists($class = $list[0], $method = $list[1])) {
                        $c = new $class();
                        $parameters = $this->arrangeMethodArgs($c, $method, $parameters);
                        $controller = array($c, $method);
                    } else {
                        throw new BadController('Cannot detect the controller method');
                    }
                }
                // Run middleware if exists
                if (!empty($route_options["middleware"])) {
                    foreach ($route_options["middleware"] as $middleware) {
                        if (is_callable($middleware) || function_exists($middleware)) {
                            $mid_args = array_merge($domain_args, $arguments);
                            $mid_args = $this->arrangeFuncArgs($middleware, $mid_args);
                            $this->publish(call_user_func_array($middleware, $mid_args));
                        } else {
                            if (is_string($middleware)) {
                                $list = explode('@', $middleware);
                                if (count($list) != 2) {
                                    throw new BadMiddleware('Cannot detect the middleware class');
                                }
                                if (method_exists($list[0], $list[1])) {
                                    $mid_args = $arguments;
                                    $mid_args = $this->arrangeMethodArgs($list[0], $list[1], $mid_args);

                                    $mid_instance = new $list[0];
                                    $this->publish(call_user_func_array(array($mid_instance, $list[1]), $mid_args));
                                } else {
                                    throw new BadMiddleware('Cannot detect the middleware method');
                                }
                            }
                        }
                    }
                }
                // Run controller function or method
                if (is_callable($controller)) {
                    $this->publish(call_user_func_array($controller, $parameters));
                } else {
                    if (is_array($controller)) {
                        $this->publish(call_user_func_array($controller[0]->$controller[1], $parameters));
                    }
                }
            }
        }
        if (!$flag) {
            throw new HttpError(404);
        }
    }

    /**
     * Check whether the current base page is correct or not
     *
     * @throws HttpError
     */
    private function checkBaseURI() {
        if (substr($this->request->getPage(), 0, strlen($this->base_uri)) != $this->base_uri) {
            throw new HttpError(404);
        }
    }

    /**
     * Convert route to regex pattern and extract the parameters
     *
     * @param string $route Route ro compile
     *
     * @return string Pattern
     */
    private function convertToRegex($route) {
        return '@^' . preg_replace_callback("@{([^}]+)}@", function ($match) {
            return $this->regexParameter($match[1]);
        }, $route) . '$@';
    }

    /**
     * Check whether the set domain is correct or not
     *
     * @param string $domain Set Domain
     *
     * @return array arguments
     * @throws HttpError
     */
    private function checkDomain($domain) {
        if (!empty($domain)) {
            if (preg_match($this->convertToRegex($domain), $this->request->getWebsite(), $arguments)) {
                return $arguments;
            }
            throw new HttpError(404);
        }
        return array();
    }

    /**
     * Arrange arguments for the given function
     *
     * @param callable $function
     * @param array    $arguments
     *
     * @return array
     */
    private function arrangeFuncArgs($function, $arguments) {
        $ref = new \ReflectionFunction($function);
        return array_map(
            function (\ReflectionParameter $param) use ($arguments) {
                if (isset($arguments[$param->getName()])) {
                    return $arguments[$param->getName()];
                }
                if ($param->isOptional()) {
                    return $param->getDefaultValue();
                }
                return null;
            },
            $ref->getParameters()
        );
    }

    /**
     * Arrange arguments for the given method
     *
     * @param object   $class
     * @param callable $method
     * @param array    $arguments
     *
     * @return array
     */
    private function arrangeMethodArgs($class, $method, $arguments) {
        $ref = new \ReflectionMethod($class, $method);
        return array_map(
            function (\ReflectionParameter $param) use ($arguments) {
                if (isset($arguments[$param->getName()])) {
                    return $arguments[$param->getName()];
                }
                if ($param->isOptional()) {
                    return $param->getDefaultValue();
                }
                return null;
            },
            $ref->getParameters()
        );
    }

    /**
     * Publish output content
     *
     * @param mixed $content
     */
    public function publish($content) {
        if (!isset($content)) {
            $content = null;
        }
        $this->response->publish($content);
    }

    /**
     * Match GET request
     *
     * @param string|array         $routes
     * @param string|callable      $controller
     * @param callable|string|null $middleware
     */
    public function get($routes = null, $controller = null, $middleware = null) {
        $this->map("GET", $routes, $controller, $middleware);
    }

    /**
     * Match the the desired route
     *
     * @param string|array         $methods
     * @param string|array         $routes
     * @param string|callable      $controller
     * @param callable|string|null $middleware
     */
    public function map($methods, $routes, $controller, $middleware = null) {
        if (!isset($methods)) {
            throw new InvalidArgumentException("Methods must be set");
        }
        if (!isset($routes)) {
            throw new InvalidArgumentException("Routes must be set");
        }
        if (!isset($controller)) {
            throw new InvalidArgumentException("Controllers must be set");
        }
        if (!is_array($methods)) {
            $methods = array($methods);
        }
        if (!is_array($routes)) {
            $routes = array($routes);
        }
        if (!empty($this->group_middleware) && is_null($middleware)) {
            $middleware = $this->group_middleware;
        } else {
            if (!is_array($middleware) && !is_null($middleware)) {
                $middleware = array($middleware);
            } else {
                if (!is_array($middleware) && is_null($middleware)) {
                    $middleware = array();
                }
            }
        }
        foreach ($methods as $method) {
            if (!isset($this->routes[$method])) {
                $this->routes[$method] = array();
            }
            foreach ($routes as $route) {
                if (substr($this->base_uri, -1) == "/" && substr($route, 0, 1) == "/") {
                    $route = substr($route, 1);
                }
                $route = $this->safeRegex($this->group_prefix . $route . $this->group_postfix);
                $this->routes[$method][$route] = array();
                $this->routes[$method][$route]["controller"] = $controller;
                $this->routes[$method][$route]["middleware"] = $middleware;
                $this->routes[$method][$route]["domain"] = $this->group_domain;
            }
        }
    }

    /**
     * Escape undesired regex from the given content
     *
     * @param string $content
     *
     * @return string
     */
    private function safeRegex($content) {
        $f = array('(', ')');
        $r = array('\(', '\)');
        return str_replace($f, $r, $content);
    }

    /**
     * Match any (GET, POST, etc.) request
     *
     * @param string|array         $routes
     * @param string|callable      $controller
     * @param callable|string|null $middleware
     */
    public function any($routes = null, $controller = null, $middleware = null) {
        $this->map(null, $routes, $controller, $middleware);
    }

    /**
     * Match POST request
     *
     * @param string|array         $routes
     * @param string|callable      $controller
     * @param callable|string|null $middleware
     */
    public function post($routes = null, $controller = null, $middleware = null) {
        $this->map("POST", $routes, $controller, $middleware);
    }

    /**
     * Define parameter regex pattern
     *
     * @param string $parameter_name Desired parameter for changing it's regex pattern
     * @param string $regex          Desired regex pattern for related parameter
     *
     * @throw \InvalidArgumentException
     */
    public function define($parameter_name, $regex) {
        if (!isset($parameter_name) || !is_string($parameter_name)) {
            throw new \InvalidArgumentException("Parameter name must be a string value");
        }
        if (!isset($regex) || !is_string($regex)) {
            throw new \InvalidArgumentException("Regex must be a string value");
        }
        $this->parameters[$parameter_name] = $this->safeRegex($regex);
    }

    /**
     * @return string
     */
    public function getBaseURI() {
        return $this->base_uri;
    }

    /** @noinspection PhpUnusedPrivateMethodInspection
     *
     * Return the regex pattern of given parameter
     *
     * @param $name
     *
     * @return string
     */
    private function regexParameter($name) {
        if ($name[strlen($name) - 1] == '?') {
            $name = substr($name, 0, strlen($name) - 1);
            $end = '?';
        } else {
            $end = '';
        }
        $pattern = isset($this->parameters[$name]) ? $this->parameters[$name] : "[^/]+";
        return '(?<' . $name . '>' . $pattern . ')' . $end;
    }

}
