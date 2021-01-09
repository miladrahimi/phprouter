<?php

namespace MiladRahimi\PhpRouter\Tests\Common;

use Closure;
use Psr\Http\Message\ServerRequestInterface;
use Laminas\Diactoros\Response\TextResponse;

class StopperMiddleware
{
    /**
     * @var string
     */
    public $content;

    /**
     * @var array
     */
    public static $output = [];

    /**
     * SampleMiddleware constructor.
     * @param string $content
     */
    public function __construct(string $content = null)
    {
        $this->content = $content ?: mt_rand(1, 9999999);
    }

    /**
     * @inheritdoc
     */
    public function handle(ServerRequestInterface $request, Closure $next)
    {
        static::$output[] = $this->content;

        return new TextResponse('Stopped in middleware.');
    }
}
