<?php

namespace MiladRahimi\PhpRouter\Tests;

use MiladRahimi\PhpRouter\Enums\HttpMethods;

/**
 * Class HttpMethodsTest
 *
 * @package MiladRahimi\PhpRouter\Tests
 */
class HttpMethodsTest extends TestCase
{
    public function test_custom_method_it_should_return_standardized_name()
    {
        $actual = HttpMethods::custom('append');

        $this->assertEquals('APPEND', $actual);
    }
}
