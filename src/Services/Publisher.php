<?php

namespace MiladRahimi\PhpRouter\Services;

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
