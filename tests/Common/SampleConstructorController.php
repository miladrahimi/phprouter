<?php

namespace MiladRahimi\PhpRouter\Tests\Common;

class SampleConstructorController
{
    public SampleInterface $sample;

    public function __construct(SampleInterface $sample)
    {
        $this->sample = $sample;
    }

    public function getSampleClassName(): string
    {
        return get_class($this->sample);
    }
}
