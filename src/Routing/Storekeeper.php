<?php

namespace MiladRahimi\PhpRouter\Routing;

class Storekeeper
{
    /**
     * @var Repository
     */
    private $store;

    /**
     * @var State
     */
    private $state;

    /**
     * Storekeeper constructor.
     *
     * @param Repository $store
     * @param State $state
     */
    public function __construct(Repository $store, State $state)
    {
        $this->store = $store;
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
        $this->store->save(
            $method,
            $this->state->getPrefix() . $path,
            $controller,
            $name,
            $this->state->getMiddleware(),
            $this->state->getDomain()
        );
    }

    /**
     * @return Repository
     */
    public function getStore(): Repository
    {
        return $this->store;
    }

    /**
     * @param Repository $store
     */
    public function setStore(Repository $store): void
    {
        $this->store = $store;
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
