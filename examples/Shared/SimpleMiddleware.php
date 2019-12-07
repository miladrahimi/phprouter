<?php

namespace MiladRahimi\PhpRouter\Examples\Shared;

use Closure;
use MiladRahimi\PhpRouter\Middleware;
use Psr\Http\Message\ServerRequestInterface;

class SimpleMiddleware implements Middleware
{
    /**
     * @inheritDoc
     */
    public function handle(ServerRequestInterface $request, Closure $next)
    {
        return $next($request);
    }
}
