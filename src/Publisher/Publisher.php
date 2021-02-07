<?php

namespace MiladRahimi\PhpRouter\Publisher;

/**
 * Interface Publisher
 * It publishes responses provided by controllers and middleware
 *
 * @package MiladRahimi\PhpRouter\Services
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
