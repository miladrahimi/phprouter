<?php namespace Neatplex\PHPRouter;

    /*
    --------------------------------------------------------------------------------
    Response Class
    --------------------------------------------------------------------------------
    Response Class is used to help developer to response the matched route, it
    includes methods like redirect(), render(), etc.
    --------------------------------------------------------------------------------
    http://neatplex.com/package/phprouter/master/component#response
    --------------------------------------------------------------------------------
    */

/**
 * Class Response
 *
 * @package Neatplex\PHPRouter
 */
class Response
{

    /**
     * Singleton instance of the class
     *
     * @var Request
     */
    private static $instance = null;

    /**
     * Injected Router object
     *
     * @var Router
     */
    private $router;

    /**
     * Constructor
     *
     * @param Router $rooter
     */
    private function __construct(Router $rooter)
    {
        if (!($rooter instanceof Router))
            throw new \InvalidArgumentException("Neatplex PHPRouter: Invalid object given instead of Router object");
        $this->router = $rooter;
    }

    /**
     * Get singleton instance of the class
     *
     * @param Router $router
     * @return Request
     */
    public static function getInstance(Router $router = null)
    {
        return isset(self::$instance) ? self::$instance : self::$instance = new Response($router);
    }

    /**
     * Redirect to given URL
     *
     * @param string $to
     */
    public function redirect($to = "/")
    {
        $to = $this->router->getBaseURI() . (is_string($to) ? $to : "");
        ob_start();
        ob_clean();
        header("Location: " . $to);
        ob_flush();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return ob_get_contents();
    }

    /**
     * Render (PHP) file
     *
     * @param string $file
     */
    public function render($file = null)
    {
        if (file_exists($file))
            include $file;
    }

}