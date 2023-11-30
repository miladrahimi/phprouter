<?php

namespace MiladRahimi\PhpRouter\Routing;

use Closure;

/**
 * It adds new routes with an existing state (attributes) into a Route repository
 */
class Storekeeper
{
    private Repository $repository;

    private State $state;

    public function __construct(Repository $repository, State $state)
    {
        $this->repository = $repository;
        $this->state = $state;
    }

    /**
     * Add a new route
     *
     * @param string $method
     * @param string $path
     * @param Closure|string|array $controller
     * @param string|null $name
     */
    public function add(string $method, string $path, $controller, ?string $name = null): void
    {
        $this->repository->save(
            $method,
            $this->state->getPrefix() . $path,
            $controller,
            $name,
            $this->state->getMiddleware(),
            $this->state->getDomain()
        );
    }

    public function getState(): State
    {
        return $this->state;
    }

    public function setState(State $state): void
    {
        $this->state = $state;
    }

    public function getRepository(): Repository
    {
        return $this->repository;
    }
}
