<?php
/**
 * Created by PhpStorm.
 * User: Milad Rahimi <info@miladrahimi.com>
 * Date: 7/13/2018 AD
 * Time: 16:22
 */

namespace MiladRahimi\PhpRouter\Services;

interface PublisherInterface
{
    /**
     * Publish the output
     *
     * @param mixed $content
     */
    public function publish($content);
}
