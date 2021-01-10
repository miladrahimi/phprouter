<?php

namespace MiladRahimi\PhpRouter\Routing;

class Storekeeper
{
    /**
     * @var Repository
     */
    private $repository;

    /**
     * @var State
     */
    private $state;

    /**
     * Storekeeper constructor.
     *
     * @param Repository $repository
     * @param State $state
     */
    public function __construct(Repository $repository, State $state)
    {
        $this->repository = $repository;
        $this->state = $state;
    }

    /**
     * Add a route to the collection
     *
     * @param string $method
     * @param string $path
     * @param $controller
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

    /**
     * @return State
     */
    public function getState(): State
    {
        return $this->state;
    }

    /**
     * @param State $state
     */
    public function setState(State $state): void
    {
        $this->state = $state;
    }
}
