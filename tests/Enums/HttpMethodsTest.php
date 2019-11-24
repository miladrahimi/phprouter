<?php

namespace MiladRahimi\PhpRouter\Tests\Enums;

use MiladRahimi\PhpRouter\Enums\HttpMethods;
use MiladRahimi\PhpRouter\Tests\TestCase;

class HttpMethodsTest extends TestCase
{
    public function test_custom_method_it_should_standardize_the_method()
    {
        $actual = HttpMethods::custom('append');

        $this->assertEquals('APPEND', $actual);
    }
}
