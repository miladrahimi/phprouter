<?php

namespace MiladRahimi\PhpRouter\View;

/**
 * It makes views from PHP and HTML/PHP files
 */
class PhpView implements View
{
    /**
     * The root directory of view files
     */
    private string $directory;

    public function __construct(string $directory)
    {
        $this->directory = $directory;
    }

    /**
     * @inheritDoc
     */
    public function make(string $name, array $data = [], int $httpStatus = 200, array $httpHeaders = [])
    {
        $file = str_replace('.', DIRECTORY_SEPARATOR, $name) . '.phtml';
        $path = join('/', [$this->directory, $file]);

        http_response_code($httpStatus);

        foreach ($httpHeaders as $name => $values) {
            @header($name . ': ' . $values);
        }

        extract($data);

        require $path;

        return null;
    }
}