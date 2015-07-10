<?php namespace MiladRahimi\PHPRouter;

/**
 * Class Response
 *
 * Response Class is used to help developer to response the matched route, it
 * includes methods like redirect(), render(), etc.
 *
 * @package MiladRahimi\PHPRouter
 *
 * @author Milad Rahimi <info@miladrahimi.com>
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
     *
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


    /**
     * Response cookies (write-only)
     *
     * @param string $name
     * @param string $value
     * @param int $expire
     * @param string $path
     * @param string $domain
     * @param bool $secure
     * @param bool $httponly
     *
     * @return array
     */
    public function cookie($name, $value, $expire = 0, $path = null, $domain = null, $secure = false, $httponly = false)
    {
        if (empty($name) || empty($value))
            throw new \InvalidArgumentException("cookie() method catches empty name or value parameter");
        return setcookie($name, $value, $expire, $path, $domain, $secure, $httponly);
    }

    /**
     * Return current output content
     *
     * @return string
     */
    public function contents()
    {
        return ob_get_contents();
    }

    /**
     * Publish output content
     *
     * @param string|mixed $content
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
}