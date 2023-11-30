<?php

namespace MiladRahimi\PhpRouter\Tests\Common;

use Laminas\Diactoros\Response\TextResponse;

class StopperMiddleware
{
    public string $content;

    /**
     * @var array
     */
    public static array $output = [];

    public function __construct(string $content = null)
    {
        $this->content = $content ?: mt_rand(1, 9999999);
    }

    public function handle(): TextResponse
    {
        static::$output[] = $this->content;

        return new TextResponse('Stopped in middleware.');
    }
}
