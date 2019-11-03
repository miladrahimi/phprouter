<?php

namespace MiladRahimi\PhpRouter;

use Closure;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Interface Middleware
 *
 * @package MiladRahimi\PhpRouter
 */
interface Middleware
{
    /**
     * Handle request and response
     *
     * @param ServerRequestInterface $request
     * @param Closure $next
     * @return ResponseInterface|mixed|null
     */
    public function handle(ServerRequestInterface $request, Closure $next);
}
