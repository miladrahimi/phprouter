<?php

namespace MiladRahimi\PhpRouter\Values;

use Closure;
use MiladRahimi\PhpRouter\Middleware;

/**
 * Class GroupState
 *
 * @package MiladRahimi\PhpRouter\Values
 */
class GroupState
{
    /**
     * URI prefix
     *
     * @var string
     */
    public $prefix = '';

    /**
     * Controller namespace (prefix)
     *
     * @var string
     */
    public $namespace = '';

    /**
     * Middleware list
     *
     * @var string[]|callable[]|Closure[]|Middleware[]
     */
    public $middleware = [];

    /**
     * Domain
     *
     * @var string|null
     */
    public $domain = null;
}
