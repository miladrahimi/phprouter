<?php

namespace MiladRahimi\PhpRouter\Dispatching;

use Closure;
use Laminas\Diactoros\ServerRequest;
use MiladRahimi\PhpContainer\Container;
use MiladRahimi\PhpContainer\Exceptions\ContainerException;
use MiladRahimi\PhpRouter\Exceptions\InvalidCallableException;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class Caller
 * It calls (runs) middleware and controllers
 *
 * @package MiladRahimi\PhpRouter\Dispatching
 */
class Caller
{
    /**
     * @var Container
     */
    private $container;

    /**
     * Constructor
     *
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Call a callable stack
     *
     * @param string[] $callables
     * @param ServerRequestInterface $request
     * @param int $index
     * @return mixed
     * @throws ContainerException
     * @throws InvalidCallableException
     */
    public function stack(array $callables, ServerRequestInterface $request, int $index = 0)
    {
        $this->container->singleton(ServerRequest::class, $request);
        $this->container->singleton(ServerRequestInterface::class, $request);

        if (isset($callables[$index + 1])) {
            $this->container->closure('$next', function (ServerRequestInterface $request) use ($callables, $index) {
                return $this->stack($callables, $request, $index + 1);
            });
        } else {
            $this->container->delete('$next');
        }

        return $this->call($callables[$index]);
    }

    /**
     * Run a callable
     *
     * @param Closure|callable|string $callable
     * @return mixed
     * @throws InvalidCallableException
     * @throws ContainerException
     */
    public function call($callable)
    {
        if (is_array($callable)) {
            if (count($callable) != 2) {
                throw new InvalidCallableException('Invalid callable: ' . implode(',', $callable));
            }

            [$class, $method] = $callable;

            if (class_exists($class) == false) {
                throw new InvalidCallableException("Class `$class` not found.");
            }

            $object = $this->container->instantiate($class);

            if (method_exists($object, $method) == false) {
                throw new InvalidCallableException("Method `$class::$method` is not declared.");
            }

            $callable = [$object, $method];
        } else {
            if (is_string($callable)) {
                if (class_exists($callable)) {
                    $callable = new $callable();
                } else {
                    throw new InvalidCallableException("Class `$callable` not found.");
                }
            }

            if (is_object($callable) && !$callable instanceof Closure) {
                if (method_exists($callable, 'handle')) {
                    $callable = [$callable, 'handle'];
                } else {
                    throw new InvalidCallableException("Method `handle` is not declared.");
                }
            }
        }

        if (is_callable($callable) == false) {
            throw new InvalidCallableException('Invalid callable.');
        }

        return $this->container->call($callable);
    }
}
