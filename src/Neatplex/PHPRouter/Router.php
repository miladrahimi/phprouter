<?php namespace Neatplex\PHPRouter;

    /*
    --------------------------------------------------------------------------------
    Router Class
    --------------------------------------------------------------------------------
    Router class is the main class which developers must interactive with to
    dispatch all website routes.
    --------------------------------------------------------------------------------
    http://neatplex.com/package/phprouter/master/component#router
    --------------------------------------------------------------------------------
    */

/**
 * Class Router
 *
 * @package Neatplex\PHPRouter
 */
class Router
{
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
     * Current URI (removed query string)
     *
     * @var string
     */
    private $uri;

    /**
     * Current URI (removed base URI and query string)
     *
     * @var string
     */
    private $local_uri;

    /**
     * End-user request URI
     *
     * @var string
     */
    private $request_uri;

    /**
     * End-user request query string
     *
     * @var string
     */
    private $request_query_string;

    /**
     * End-user request method
     *
     * @var string
     */
    private $request_method;

    /**
     * End-user request protocol
     *
     * @var string
     */
    private $request_protocol;

    /**
     * End-user request IP
     *
     * @var string
     */
    private $request_ip;

    /**
     * End-user request port
     *
     * @var int
     */
    private $request_port;

    /**
     * End-user request POST array
     *
     * @var array
     */
    private $request_post;

    /**
     * End-user request GET array
     *
     * @var array
     */
    private $request_get;

    /**
     * @param string $base_uri
     * @throw \InvalidArgumentException
     */
    public function __construct($base_uri = "")
    {
        $u = $this->request_uri = urldecode($_SERVER["REQUEST_URI"]);
        $q = $this->request_query_string = $_SERVER["QUERY_STRING"];
        $this->uri = trim(substr($u, 0, strlen($u) - strlen($q)), '?');
        $this->request_method = $_SERVER["REQUEST_METHOD"];
        $this->request_protocol = $_SERVER["SERVER_PROTOCOL"];
        $this->request_ip = $_SERVER["REMOTE_ADDR"];
        $this->request_port = $_SERVER["REMOTE_PORT"];
        $this->request_get = $_GET;
        $this->request_post = $_POST;
        // Set Base URI
        if (!is_string($base_uri))
            throw new \InvalidArgumentException('Neatplex PHPRouter: $base_uri must be a string value');
        $this->base_uri = $base_uri;
        $lu = $this->uri;
        if (substr($lu, 0, strlen($this->base_uri)) == $this->base_uri)
            $lu = substr($lu, strlen($this->base_uri));
        $this->local_uri = $lu;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getRequestMethod() . " " . $this->getRequestUri();
    }

    /**
     * @return string
     */
    public function getRequestMethod()
    {
        return $this->request_method;
    }

    /**
     * @return string
     */
    public function getRequestUri()
    {
        return $this->request_uri;
    }

    /**
     * Group routes to set common options
     *
     * @param array|callable|string $options
     * @param callable $body
     * @throws PHPRouterError
     */
    public function group($options, $body)
    {
        if (is_array($options)) {
            if (isset($options["middleware"]))
                if (!is_array($options["middleware"]))
                    $options["middleware"] = array($options["middleware"]);
        } else if (is_callable($options) || is_string($options)) {
            $middleware = $options;
            $options = array();
            $options["middleware"] = array($middleware);
        }
        $this->group_middleware = (!isset($options["middleware"]) || !is_array($t = $options["middleware"]))
            ? array() : $t;
        $this->group_prefix = (!isset($options["prefix"]) || !is_string($t = $options["prefix"])) ? "" : $t;
        $this->group_postfix = (!isset($options["postfix"]) || !is_string($t = $options["postfix"])) ? "" : $t;
        if (is_callable($body))
            $body($this);
        $this->group_middleware = array();
        $this->group_prefix = "";
        $this->group_postfix = "";
    }

    /**
     * Dispatch all routes and perform the appropriate controller (in order to run!)
     */
    public function dispatch()
    {
        $this->checkBaseURI();
        // Prepare all routes of the requested method
        if (!isset($this->routes[null]) || !is_array($this->routes[null]))
            $this->routes[null] = array();
        if (!isset($this->routes[$this->request_method]) || !is_array($this->routes[$this->request_method]))
            $this->routes[$this->request_method] = array();
        $routes = array_merge($this->routes[null], $this->routes[$this->request_method]);
        // Flag down! Not found is the default
        $flag = false;
        // Check all routes
        foreach ($routes as $route => $jobs) {
            // Extract all the current route parameter
            $route_pattern = $this->regexRoute($route);
            // Check if the request uri is match with current route
            if (preg_match($route_pattern, $this->local_uri, $arguments)) {
                // Flag up! found, matches...
                $flag = true;
                $arguments["request"] = (isset($parameters["request"])) ? $parameters["request"] :
                    Request::getInstance($this);
                $arguments["response"] = (isset($parameters["response"])) ? $parameters["response"] :
                    Response::getInstance($this);
                $parameters = $arguments;
                // Controller...
                if (is_callable($controller = $jobs["controller"])) {
                    $parameters = $this->arrangeFunctionArguments($controller, $parameters);
                } else {
                    $list = explode('@', $controller = $jobs["controller"]);
                    if (count($list) != 2)
                        throw new PHPRouterError("Neatplex PHPRouter, Error 1021: Invalid controller function");
                    if (method_exists($class = $list[0], $method = $list[1])) {
                        $c = new $class();
                        $parameters = $this->arrangeMethodArguments($c, $method, $parameters);
                        $controller = array($c, $method);
                    } else {
                        throw new PHPRouterError("Neatplex PHPRouter, Error 1022: Invalid controller method");
                    }
                }
                // Run middleware if exists
                if (!empty($jobs["middleware"])) {
                    foreach ($jobs["middleware"] as $middleware) {
                        if (is_callable($middleware) || function_exists($middleware)) {
                            $mid_args = $arguments;
                            $mid_args = $this->arrangeFunctionArguments($middleware, $mid_args);
                            $this->publish(call_user_func_array($middleware, $mid_args));
                        } else if (is_string($middleware)) {
                            $list = explode('@', $middleware);
                            if (count($list) != 2)
                                throw new PHPRouterError("Neatplex PHPRouter, Error 1023: Invalid middleware function");
                            if (method_exists($list[0], $list[1])) {
                                $mid_args = $arguments;
                                $mid_args = $this->arrangeMethodArguments($list[0], $list[1], $mid_args);
                                $this->publish(call_user_func_array($list[0]->$list[1], $mid_args));
                            } else {
                                throw new PHPRouterError("Neatplex PHPRouter, Error 1022: Invalid middleware method");
                            }
                        }
                    }
                }
                // Run controller function or method
                if (is_callable($controller))
                    $this->publish(call_user_func_array($controller, $parameters));
                else if (is_array($controller))
                    $this->publish(call_user_func_array($controller[0]->$controller[1], $parameters));
            }
        }
        if (!$flag)
            throw new HttpError(404);
    }

    /**
     * Check if the current base uri is correct or not
     *
     * @throws HttpError
     */
    private function checkBaseURI()
    {
        if (substr($this->uri, 0, strlen($this->base_uri)) != $this->base_uri)
            throw new HttpError(404);
    }

    /**
     * Convert route to regex pattern and extract the parameters
     *
     * @param string $route Route ro compile
     * @return string Pattern
     */
    private function regexRoute($route)
    {
        return '@^' . preg_replace("@{([^}]+)}@e", '$this->regexParameter("$1")', $route) . '$@';
    }

    /**
     * Arrange arguments for the given function
     *
     * @param callable $function
     * @param array $arguments
     * @return array
     */
    private function arrangeFunctionArguments($function, $arguments)
    {
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
     * @param object $class
     * @param callable $method
     * @param array $arguments
     * @return array
     */
    private function arrangeMethodArguments($class, $method, $arguments)
    {
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
    public function publish($content)
    {
        // Open output stream
        $fp = fopen("php://output", 'r+');
        // Raw content
        if (is_string($content) || is_numeric($content) || is_null($content)) {
            fputs($fp, $content);
        } // Object with __toString method
        else if (is_object($content) && method_exists($content, "__toString")) {
            fputs($fp, $content->__toString());
        } // Else
        else {
            fputs($fp, print_r($content, true));
        }
    }

    /**
     * Match GET request
     *
     * @param string|array $routes
     * @param string|callable $controller
     * @param callable|string|null $middleware
     */
    public function get($routes = null, $controller = null, $middleware = null)
    {
        $this->match("GET", $routes, $controller, $middleware);
    }

    /**
     * Match the the desired route
     *
     * @param string|array $methods
     * @param string|array $routes
     * @param string|callable $controller
     * @param callable|string|null $middleware
     * @throws PHPRouterError
     */
    public function match($methods, $routes, $controller, $middleware = null)
    {
        if (!is_array($methods))
            $methods = array($methods);
        if (!is_array($routes))
            $routes = array($routes);
        if (!empty($this->group_middleware) && is_null($middleware))
            $middleware = $this->group_middleware;
        else
            if (!is_array($middleware) && !is_null($middleware))
                $middleware = array($middleware);
            else if (!is_array($middleware) && is_null($middleware))
                $middleware = array();
        foreach ($methods as $method) {
            if (!isset($this->routes[$method]))
                $this->routes[$method] = array();
            foreach ($routes as $route) {
                $route = $this->safeRegex($this->group_prefix . $route . $this->group_postfix);
                $this->routes[$method][$route] = array();
                $this->routes[$method][$route]["controller"] = $controller;
                $this->routes[$method][$route]["middleware"] = $middleware;
            }
        }
    }

    /**
     * Escape undesired regex from the given content
     *
     * @param string $content
     * @return string
     */
    private function safeRegex($content)
    {
        $f = array('(', ')');
        $r = array('\(', '\)');
        return str_replace($f, $r, $content);
    }

    /**
     * Match any (GET, POST, etc.) request
     *
     * @param string|array $routes
     * @param string|callable $controller
     * @param callable|string|null $middleware
     */
    public function any($routes = null, $controller = null, $middleware = null)
    {
        $this->match(null, $routes, $controller, $middleware);
    }

    /**
     * Match POST request
     *
     * @param string|array $routes
     * @param string|callable $controller
     * @param callable|string|null $middleware
     */
    public function post($routes = null, $controller = null, $middleware = null)
    {
        $this->match("POST", $routes, $controller, $middleware);
    }

    /**
     * Define parameter regex pattern
     *
     * @param string $parameter_name Desired parameter for changing it's regex pattern
     * @param string $regex Desired regex pattern for related parameter
     * @throw \InvalidArgumentException
     */
    public function define($parameter_name, $regex)
    {
        if (!is_string($parameter_name))
            throw new \InvalidArgumentException('Neatplex PHPRouter: $parameter_name must be a string value');
        if (!is_string($regex))
            throw new \InvalidArgumentException('Neatplex PHPRouter: $regex must be a string value');
        $this->parameters[$parameter_name] = $this->safeRegex($regex);
    }

    /**
     * @return string
     */
    public function getBaseURI()
    {
        return $this->base_uri;
    }

    /**
     * @return string
     */
    public function getRequestProtocol()
    {
        return $this->request_protocol;
    }

    /**
     * @return string
     */
    public function getRequestIP()
    {
        return $this->request_ip;
    }

    /**
     * @return int
     */
    public function getRequestPort()
    {
        return $this->request_port;
    }

    /**
     * @return array
     */
    public function getRequestPOST()
    {
        return $this->request_post;
    }

    /**
     * @return array
     */
    public function getRequestGET()
    {
        return $this->request_get;
    }

    /**
     * @return string
     */
    public function getRequestQueryString()
    {
        return $this->request_query_string;
    }

    /**
     * @return string
     */
    public function getURI()
    {
        return $this->uri;
    }

    /**
     * @return string
     */
    public function getLocalURI()
    {
        return $this->local_uri;
    }

    /**
     * Return the regex pattern of given parameter
     *
     * @param string $name Parameter Name
     * @return string Pattern
     */
    private function regexParameter($name)
    {
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