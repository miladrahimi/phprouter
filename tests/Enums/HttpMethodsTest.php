<?php

namespace MiladRahimi\PhpRouter\Tests\Enums;

use MiladRahimi\PhpRouter\Enums\HttpMethods;
use MiladRahimi\PhpRouter\Tests\TestCase;

class HttpMethodsTest extends TestCase
{
    public function test_custom_method_with_lowercase_method_it_should_standardize_it()
    {
        $actual = HttpMethods::custom('append');

        $this->assertEquals('APPEND', $actual);
    }

    public function test_custom_method_with_uppercase_method_it_should_standardize_it()
    {
        $actual = HttpMethods::custom('APPEND');

        $this->assertEquals('APPEND', $actual);
    }

    public function test_custom_method_with_mixed_case_method_it_should_standardize_it()
    {
        $actual = HttpMethods::custom('AppEnd');

        $this->assertEquals('APPEND', $actual);
    }
}
