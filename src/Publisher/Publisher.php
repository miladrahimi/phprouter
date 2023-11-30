<?php

namespace MiladRahimi\PhpRouter\Publisher;

/**
 * It publishes responses provided by controllers and middleware
 */
interface Publisher
{
    /**
     * Publish the response
     *
     * @param mixed $response
     */
    public function publish($response): void;
}
