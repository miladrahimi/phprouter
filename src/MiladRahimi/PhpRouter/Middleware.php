<?php
/**
 * Created by PhpStorm.
 * User: Milad Rahimi <info@miladrahimi.com>
 * Date: 6/23/2018 AD
 * Time: 01:31
 */

namespace MiladRahimi\PhpRouter;

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
