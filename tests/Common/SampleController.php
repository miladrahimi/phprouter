<?php

namespace MiladRahimi\PhpRouter\Tests\Common;

class SampleController
{
    public function home(): string
    {
        return 'Home';
    }

    public function page(): string
    {
        return 'Page';
    }

    public function ok(): string
    {
        return 'OK';
    }
}
