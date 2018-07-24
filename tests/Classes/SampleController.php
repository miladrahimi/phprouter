<?php
/**
 * Created by PhpStorm.
 * User: Milad Rahimi <info@miladrahimi.com>
 * Date: 7/9/2018 AD
 * Time: 16:07
 */

namespace MiladRahimi\PhpRouter\Tests\Classes;

use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\TextResponse;

class SampleController
{
    public function getNoParameter()
    {
        return new TextResponse('Here I am!');
    }

    public function postOneParameter(int $id)
    {
        return new TextResponse('The id is ' . $id);
    }

    public function putOnlyRequest(ServerRequestInterface $request)
    {
        return new TextResponse('The uri is ' . $request->getUri());
    }

    public function patchOneParameterWithRequest(int $id, ServerRequestInterface $request)
    {
        return new TextResponse('The id is ' . $id . ', the uri is ' . $request->getUri());
    }

    public function deleteTwoParameter(string $two, string $one)
    {
        return new TextResponse('The one is ' . $one . ', the two is ' . $two);
    }

    public function optionsOptionalParameter(int $id, string $more = null)
    {
        return new TextResponse('The id is ' . $id . ', the more is ' . $more ?: 'NULL');
    }
}
