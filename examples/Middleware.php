<?php

use Psr\Http\Message\ServerRequestInterface;

class Middleware implements \MiladRahimi\PhpRouter\Middleware
{
    /**
     * @inheritdoc
     */
    public function handle(ServerRequestInterface $request, Closure $next)
    {
        $request = $request->withAttribute('color', 'blue');

        return $next($request);
    }
}
