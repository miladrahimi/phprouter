<?php
/**
 * Created by PhpStorm.
 * User: Milad Rahimi <info@miladrahimi.com>
 * Date: 6/23/2018 AD
 * Time: 02:46
 */

namespace MiladRahimi\PhpRouter\Tests\Classes;

use Closure;
use MiladRahimi\PhpRouter\Middleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\TextResponse;

class StopperMiddleware implements Middleware
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var array
     */
    public static $output = [];

    /**
     * SampleMiddleware constructor.
     * @param int $id
     */
    public function __construct(int $id = 0)
    {
        $this->id = $id;
    }

    /**
     * Handle user request
     *
     * @param ServerRequestInterface $request
     * @param Closure $next
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request, Closure $next)
    {
        static::$output[] = $this->id;

        return new TextResponse('Stopped in middleware.');
    }
}
