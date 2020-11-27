<?php

namespace MiladRahimi\PhpRouter\Dispatching;

use Closure;
use Laminas\Diactoros\ServerRequest;
use MiladRahimi\PhpContainer\Container;
use MiladRahimi\PhpContainer\Exceptions\ContainerException;
use MiladRahimi\PhpRouter\Exceptions\InvalidCallableException;
use Psr\Http\Message\ServerRequestInterface;

class Caller
{
    /**
     * @var Container
     */
    private $container;

    /**
     * Caller constructor.
     *
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Call the given callable stack
     *
     * @param string[] $callables
     * @param ServerRequestInterface $request
     * @param int $i
     * @return mixed
     * @throws ContainerException
     * @throws InvalidCallableException
     */
    public function stack(array $callables, ServerRequestInterface $request, $i = 0)
    {
        $this->container->singleton(ServerRequest::class, $request);
        $this->container->singleton(ServerRequestInterface::class, $request);

        if (isset($callables[$i + 1])) {
            $next = function (ServerRequestInterface $request) use ($callables, $i) {
                return $this->stack($callables, $request, $i + 1);
            };

            $this->container->closure('$next', $next);
        } else {
            $this->container->delete('$next');
        }

        return $this->call($callables[$i]);
    }

    /**
     * Run the given callable
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

            list($class, $method) = $callable;

            if (class_exists($class) == false) {
                throw new InvalidCallableException("Class `$callable` not found.");
            }

            $object = $this->container->instantiate($class);

            if (method_exists($object, $method) == false) {
                throw new InvalidCallableException("Method `$class::$method` not found.");
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

            if (is_object($callable) && !($callable instanceof Closure)) {
                if (method_exists($callable, 'handle')) {
                    $callable = [$callable, 'handle'];
                } else {
                    throw new InvalidCallableException("Method `$callable::handle` not found.");
                }
            }
        }

        if (is_callable($callable) == false) {
            throw new InvalidCallableException('Invalid callable.');
        }

        return $this->container->call($callable);
    }
}
